<?php
$page_title="Create Invoice";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Salesman'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$success=$error='';

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_invoice'])){
    $cid    = intval($_POST['customer_id']);
    $cname  = sanitize($_POST['customer_name']);
    $tax    = floatval($_POST['tax_rate']);
    $disc   = floatval($_POST['discount']);
    $method = sanitize($_POST['payment_method']);
    $notes  = sanitize($_POST['notes']);
    $items  = json_decode($_POST['items_json'],true);

    if(empty($items)){$error='Add at least one product.';}
    elseif(empty($cname)){$error='Customer name required.';}
    else {
        $sub=0;
        foreach($items as $it) $sub += $it['qty']*$it['price'];
        $tax_amt = $sub*($tax/100);
        $total   = $sub + $tax_amt - $disc;
        $inv_no  = 'INV-'.str_pad($conn->query("SELECT COUNT(*) as c FROM sales")->fetch_assoc()['c']+1,5,'0',STR_PAD_LEFT);

        if(executeQuery("INSERT INTO sales (invoice_number,customer_id,customer_name,user_id,subtotal,tax_rate,tax_amount,discount,total_amount,payment_method,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            "sisidddddss",[$inv_no,$cid?:null,$cname,$_SESSION['user_id'],$sub,$tax,$tax_amt,$disc,$total,$method,$notes])){
            $sid=$conn->insert_id;
            foreach($items as $it){
                executeQuery("INSERT INTO sale_items (sale_id,product_id,product_name,quantity,unit_price,subtotal) VALUES (?,?,?,?,?,?)","iisids",[$sid,$it['product_id'],$it['name'],$it['qty'],$it['price'],$it['qty']*$it['price']]);
                executeQuery("UPDATE products SET quantity=quantity-? WHERE product_id=?","ii",[$it['qty'],$it['product_id']]);
                executeQuery("INSERT INTO stock_logs (product_id,user_id,type,quantity,reference) VALUES (?,?,'sale',?,?)","iiis",[$it['product_id'],$_SESSION['user_id'],$it['qty'],$inv_no]);
            }
            logActivity($_SESSION['user_id'],'Create Invoice',"Invoice: $inv_no Total: $total");
            header("Location: /inventory_system/modules/sales/view_invoice.php?id=$sid");
            exit();
        } else { $error='Failed to create invoice: '.$conn->error; }
    }
}
$customers = getAllRows("SELECT customer_id,customer_name,phone FROM customers WHERE status='active' ORDER BY customer_name");
$categories = getAllRows("SELECT category_id,category_name FROM categories WHERE status='active' ORDER BY category_name");
$products  = getAllRows("SELECT product_id,product_name,category_id,barcode,selling_price,quantity FROM products WHERE status='active' AND quantity>0 ORDER BY product_name");
$tax_setting = getSingleRow("SELECT setting_value FROM settings WHERE setting_key='tax_rate'");
$default_tax = $tax_setting['setting_value'] ?? 0;
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($error):?><div class="alert alert-danger"><?php echo $error;?></div><?php endif;?>
<div class="page-header"><div><h2><i class="fas fa-file-invoice-dollar"></i> Create Invoice</h2></div><a href="manage_invoices.php" class="btn btn-secondary"><i class="fas fa-list"></i> All Invoices</a></div>
<form method="POST" id="invoiceForm">
<input type="hidden" name="items_json" id="items_json">
<div class="row g-3">
<div class="col-md-8">
  <div class="card mb-3"><div class="card-header">Customer</div><div class="card-body">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Select Customer</label>
        <select name="customer_id" id="cust_sel" class="form-select"><option value="0">Walk-in Customer</option>
        <?php foreach($customers as $c):?><option value="<?php echo $c['customer_id'];?>" data-name="<?php echo htmlspecialchars($c['customer_name']);?>"><?php echo htmlspecialchars($c['customer_name']);?> - <?php echo $c['phone'];?></option><?php endforeach;?>
        </select></div>
      <div class="col-md-6"><label class="form-label">Customer Name *</label><input type="text" name="customer_name" id="cust_name" class="form-control" placeholder="Enter name" required></div>
    </div>
  </div></div>
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="fas fa-cart-plus me-1"></i> Add Products</span>
      <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#cameraScannerModal" onclick="startCameraScanner()">
        <i class="fas fa-camera me-1"></i> Scan with Phone / Camera
      </button>
    </div>
    <div class="card-body">
    <div class="row g-2 mb-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Category</label>
        <select id="cat_filter" class="form-select" onchange="filterProductsByCategory()">
          <option value="">All Categories</option>
          <?php foreach($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4"><label class="form-label">Product</label>
        <select id="prod_sel" class="form-select"><option value="">-- Select Product --</option>
        <?php foreach($products as $p):?><option value="<?php echo $p['product_id'];?>" data-category="<?php echo $p['category_id'];?>" data-barcode="<?php echo htmlspecialchars($p['barcode'] ?? ''); ?>" data-price="<?php echo $p['selling_price'];?>" data-name="<?php echo htmlspecialchars($p['product_name']);?>" data-qty="<?php echo $p['quantity'];?>"><?php echo htmlspecialchars($p['product_name']);?> <?php if(!empty($p['barcode'])) echo '['.$p['barcode'].']'; ?> - PKR <?php echo number_format($p['selling_price'],0);?> (<?php echo $p['quantity'];?> in stock)</option><?php endforeach;?></select></div>
      <div class="col-md-2"><label class="form-label">Price</label><input type="number" id="prod_price" class="form-control" step="0.01" min="0"></div>
      <div class="col-md-1"><label class="form-label">Qty</label><input type="number" id="prod_qty" class="form-control" min="1" value="1"></div>
      <div class="col-md-2"><button type="button" class="btn btn-success w-100" onclick="addItem()"><i class="fas fa-plus"></i> Add</button></div>
    </div>
    <table class="table table-sm" id="cart_table"><thead class="table-light"><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
    <tbody id="cart_body"><tr><td colspan="5" class="text-center text-muted" id="empty_row">No items added yet</td></tr></tbody></table>
  </div></div>
</div>
<div class="col-md-4">
  <div class="card mb-3"><div class="card-header bg-primary text-white">Invoice Summary</div><div class="card-body">
    <div class="mb-2 d-flex justify-content-between"><span>Subtotal:</span><strong id="disp_sub">PKR 0.00</strong></div>
    <div class="mb-2"><label class="form-label">Tax Rate (%)</label><input type="number" name="tax_rate" id="tax_rate" class="form-control" min="0" max="100" step="0.01" value="<?php echo $default_tax;?>" onchange="calcTotal()"></div>
    <div class="mb-2 d-flex justify-content-between"><span>Tax Amount:</span><strong id="disp_tax">PKR 0.00</strong></div>
    <div class="mb-2"><label class="form-label">Discount (PKR)</label><input type="number" name="discount" id="discount" class="form-control" min="0" step="0.01" value="0" onchange="calcTotal()"></div>
    <hr>
    <div class="d-flex justify-content-between fs-5"><span><strong>Total:</strong></span><strong class="text-primary" id="disp_total">PKR 0.00</strong></div>
  </div></div>
  <div class="card mb-3"><div class="card-header">Payment</div><div class="card-body">
    <div class="mb-3"><label class="form-label">Method</label><select name="payment_method" class="form-select"><option>Cash</option><option>Card</option><option>Bank Transfer</option><option>Cheque</option></select></div>
    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
  </div></div>
  <button type="submit" name="submit_invoice" class="btn btn-primary w-100 btn-lg" onclick="return prepareSubmit()"><i class="fas fa-save"></i> Save Invoice</button>
</div></div></form>
<script>
var cart=[],cartId=0;
document.getElementById('cust_sel').onchange=function(){
    var opt=this.options[this.selectedIndex];
    if(this.value!='0') document.getElementById('cust_name').value=opt.dataset.name;
    else document.getElementById('cust_name').value='';
};
document.getElementById('prod_sel').onchange=function(){
    var opt=this.options[this.selectedIndex];
    document.getElementById('prod_price').value=opt.dataset.price||'';
    document.getElementById('prod_qty').max=opt.dataset.qty||999;
};
function filterProductsByCategory(){
    var catId = document.getElementById('cat_filter').value;
    var prodSel = document.getElementById('prod_sel');
    var opts = prodSel.querySelectorAll('option');
    for(var i=1; i<opts.length; i++){
        var opt = opts[i];
        if(!catId || opt.dataset.category == catId){
            opt.style.display = '';
            opt.disabled = false;
        } else {
            opt.style.display = 'none';
            opt.disabled = true;
        }
    }
    prodSel.value = '';
    document.getElementById('prod_price').value = '';
}
function addItem(){
    var sel=document.getElementById('prod_sel');
    var opt=sel.options[sel.selectedIndex];
    var price=parseFloat(document.getElementById('prod_price').value);
    var qty=parseInt(document.getElementById('prod_qty').value);
    if(!sel.value){alert('Select a product');return;}
    if(!price||price<=0){alert('Enter valid price');return;}
    if(!qty||qty<=0){alert('Enter valid qty');return;}
    if(qty>parseInt(opt.dataset.qty)){alert('Max available: '+opt.dataset.qty);return;}
    var existing=cart.find(i=>i.product_id==sel.value);
    if(existing){existing.qty+=qty;existing.subtotal=existing.qty*existing.price;}
    else cart.push({id:cartId++,product_id:sel.value,name:opt.dataset.name,price:price,qty:qty,subtotal:price*qty});
    renderCart();
}
function removeItem(id){cart=cart.filter(i=>i.id!==id);renderCart();}
function renderCart(){
    var tb=document.getElementById('cart_body');
    if(cart.length===0){tb.innerHTML='<tr><td colspan="5" class="text-center text-muted" id="empty_row">No items added</td></tr>';calcTotal();return;}
    tb.innerHTML=cart.map(i=>'<tr><td>'+i.name+'</td><td>PKR '+i.price.toFixed(2)+'</td><td>'+i.qty+'</td><td>PKR '+(i.price*i.qty).toFixed(2)+'</td><td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem('+i.id+')"><i class="fas fa-times"></i></button></td></tr>').join('');
    calcTotal();
}
function calcTotal(){
    var sub=cart.reduce((a,i)=>a+i.price*i.qty,0);
    var tax=sub*(parseFloat(document.getElementById('tax_rate').value)||0)/100;
    var disc=parseFloat(document.getElementById('discount').value)||0;
    var total=sub+tax-disc;
    document.getElementById('disp_sub').textContent='PKR '+sub.toFixed(2);
    document.getElementById('disp_tax').textContent='PKR '+tax.toFixed(2);
    document.getElementById('disp_total').textContent='PKR '+total.toFixed(2);
}
function prepareSubmit(){
    if(cart.length===0){alert('Add at least one product');return false;}
    document.getElementById('items_json').value=JSON.stringify(cart);
    return true;
}

// Mobile / Camera Barcode Scanner Controller
var html5QrCode = null;

function playScanBeep() {
    try {
        var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        var osc = audioCtx.createOscillator();
        var gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch(e) {}
}

function startCameraScanner() {
    var feedback = document.getElementById('scan-feedback');
    feedback.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Starting camera...</span>';

    if (typeof Html5Qrcode === 'undefined') {
        feedback.innerHTML = '<span class="text-danger">Scanner library loading. Please wait a moment.</span>';
        return;
    }

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("qr-reader");
    }

    const config = { fps: 10, qrbox: { width: 260, height: 180 } };

    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
        .then(() => {
            feedback.innerHTML = '<span class="text-success fw-bold"><i class="fas fa-video me-1"></i> Camera Active. Point at product barcode.</span>';
        })
        .catch(err => {
            console.warn("Environment camera failed, trying fallback:", err);
            html5QrCode.start({ facingMode: "user" }, config, onScanSuccess)
                .then(() => {
                    feedback.innerHTML = '<span class="text-success fw-bold">Camera Active. Point at product barcode.</span>';
                })
                .catch(e => {
                    feedback.innerHTML = '<span class="text-danger">Unable to access camera: ' + e + '. Please allow camera permission in browser.</span>';
                });
        });
}

function onScanSuccess(decodedText, decodedResult) {
    playScanBeep();
    var feedback = document.getElementById('scan-feedback');
    var rawText = decodedText.trim();
    feedback.innerHTML = '<span class="text-primary fw-bold"><i class="fas fa-barcode me-1"></i> Scanned: ' + rawText + '</span>';

    // Find product matching barcode or ID
    var prodSel = document.getElementById('prod_sel');
    var foundIndex = -1;
    var options = prodSel.options;

    for (var i = 1; i < options.length; i++) {
        var opt = options[i];
        var bc = (opt.dataset.barcode || '').trim().toLowerCase();
        var pid = opt.value.trim().toLowerCase();
        var pname = (opt.dataset.name || '').trim().toLowerCase();
        var target = rawText.toLowerCase();

        if (bc === target || pid === target || pname === target) {
            foundIndex = i;
            break;
        }
    }

    if (foundIndex !== -1) {
        prodSel.selectedIndex = foundIndex;
        var selectedOpt = options[foundIndex];
        document.getElementById('prod_price').value = selectedOpt.dataset.price || '';
        document.getElementById('prod_qty').value = 1;
        document.getElementById('prod_qty').max = selectedOpt.dataset.qty || 999;
        
        // Auto add to cart
        addItem();

        feedback.innerHTML = '<div class="alert alert-success py-1 px-2 mb-0"><strong>✅ Added:</strong> ' + selectedOpt.dataset.name + ' (1 unit)</div>';
    } else {
        feedback.innerHTML = '<div class="alert alert-warning py-1 px-2 mb-0">⚠️ Barcode "<strong>' + rawText + '</strong>" not found in active catalog.</div>';
    }
}

function stopCameraScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
        }).catch(err => console.log(err));
    }
}

document.getElementById('cameraScannerModal').addEventListener('hidden.bs.modal', function () {
    stopCameraScanner();
});
</script>

<!-- Mobile Camera Barcode Scanner Modal -->
<div class="modal fade" id="cameraScannerModal" tabindex="-1" aria-labelledby="cameraScannerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="cameraScannerModalLabel">
          <i class="fas fa-barcode me-2"></i> Mobile / Camera Barcode Scanner
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="text-muted small mb-2">Align the product barcode within the viewfinder box below:</p>
        <div id="qr-reader" style="width: 100%; border-radius: 8px; overflow: hidden; background: #000; min-height: 240px;"></div>
        <div id="scan-feedback" class="mt-3 small"></div>
      </div>
      <div class="modal-footer justify-content-between">
        <small class="text-muted"><i class="fas fa-mobile-screen me-1"></i> Works on phone browser (Chrome/Safari)</small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done / Close</button>
      </div>
    </div>
  </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
