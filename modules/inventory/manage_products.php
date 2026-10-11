<?php
$page_title = "Products";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
$role = $_SESSION['role_name'];
$success = $error = '';

if (isset($_GET['delete']) && $role === 'Manager') {
    $id = intval($_GET['delete']);
    $p = getSingleRow("SELECT product_image FROM products WHERE product_id=?","i",[$id]);
    if ($p && $p['product_image']) deleteImage($p['product_image']);
    if (executeQuery("DELETE FROM products WHERE product_id=?","i",[$id])) {
        logActivity($_SESSION['user_id'],'Delete Product',"ID: $id");
        $success = 'Product deleted successfully.';
    } else { $error = 'Cannot delete - product may be in use.'; }
}

$search = sanitize($_GET['search'] ?? '');
$category_id = intval($_GET['category_id'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15; $offset = ($page-1)*$limit;

$w = "p.status='active'";
if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $w .= " AND (p.product_name LIKE '%$search_safe%' OR p.brand LIKE '%$search_safe%' OR p.barcode LIKE '%$search_safe%' OR c.category_name LIKE '%$search_safe%')";
}
if ($category_id > 0) {
    $w .= " AND p.category_id = $category_id";
}

$count_query = "SELECT COUNT(*) as c FROM products p LEFT JOIN categories c ON p.category_id=c.category_id WHERE $w";
$total = $conn->query($count_query)->fetch_assoc()['c'];
$pages = max(1, ceil($total/$limit));

$products = getAllRows("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id=c.category_id WHERE $w ORDER BY p.created_at DESC LIMIT $offset,$limit");
$categories = getAllRows("SELECT category_id, category_name FROM categories WHERE status='active' ORDER BY category_name ASC");

// Preserve query parameters for pagination
$pagination_params = [];
if ($search) $pagination_params['search'] = $search;
if ($category_id > 0) $pagination_params['category_id'] = $category_id;
$pagination_query_str = !empty($pagination_params) ? '&' . http_build_query($pagination_params) : '';

include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success): ?><div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="page-header">
    <div>
        <h2><i class="fas fa-box"></i> Products</h2>
        <p>Total: <?php echo $total; ?> products <?php if($category_id > 0): ?><span class="badge bg-primary ms-1">Filtered by category</span><?php endif; ?></p>
    </div>
    <?php if($role==='Manager'): ?><a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a><?php endif; ?>
</div>
<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" id="product_search_input" class="form-control" placeholder="Search by name, brand, barcode, or category..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#searchScannerModal" onclick="startSearchScanner()" title="Scan with Phone Camera">
                    <i class="fas fa-camera me-1"></i> Scan
                </button>
            </div>
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select" onchange="this.form.submit()">
                <option value="0">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $category_id === (int)$cat['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button></div>
        <div class="col-md-2"><a href="manage_products.php" class="btn btn-secondary w-100">Reset</a></div>
    </form>
</div></div>
<div class="card"><div class="card-header"><i class="fas fa-list"></i> Product List</div>
<div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light"><tr><th>#</th><th>Image</th><th>Product</th><th>Category</th><th>Cost</th><th>Price</th><th>Stock</th><?php if($role!=='Salesman'): ?><th>Actions</th><?php endif; ?></tr></thead>
<tbody>
<?php if(empty($products)): ?><tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-box-open fa-2x mb-2 d-block"></i>No products found</td></tr>
<?php else: foreach($products as $i=>$p): ?>
<tr>
<td><?php echo $offset+$i+1; ?></td>
<td><?php if($p['product_image']): ?><img src="/inventory_system/assets/uploads/<?php echo htmlspecialchars($p['product_image']); ?>" width="42" height="42" style="object-fit:cover;border-radius:8px;border:1px solid #e2e8f0"><?php else: ?><div style="width:42px;height:42px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94a3b8"><i class="fas fa-image"></i></div><?php endif; ?></td>
<td><strong><?php echo htmlspecialchars($p['product_name']); ?></strong><?php if($p['brand']): ?><br><small class="text-muted"><?php echo htmlspecialchars($p['brand']); ?></small><?php endif; ?></td>
<td><?php echo htmlspecialchars($p['category_name']??'--'); ?></td>
<td>PKR <?php echo number_format($p['cost_price'],0); ?></td>
<td><strong>PKR <?php echo number_format($p['selling_price'],0); ?></strong></td>
<td><?php $q=$p['quantity']; echo '<span class="badge bg-'.($q<=$p['min_quantity']?'danger':($q<=($p['min_quantity']*2)?'warning':'success')).'">'.$q.'</span>'; ?></td>
<?php if($role!=='Salesman'): ?>
<td>
    <a href="edit_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-info me-1" title="Edit"><i class="fas fa-edit"></i></a>
    <?php if($role==='Manager'): ?><a href="?delete=<?php echo $p['product_id']; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')" title="Delete"><i class="fas fa-trash"></i></a><?php endif; ?>
</td>
<?php endif; ?>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div>
<?php if($pages>1): ?><div class="p-3"><nav><ul class="pagination justify-content-center mb-0">
<li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="?page=<?php echo $page-1 . $pagination_query_str; ?>">Prev</a></li>
<?php for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++): ?><li class="page-item <?php echo $i==$page?'active':''; ?>"><a class="page-link" href="?page=<?php echo $i . $pagination_query_str; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
<li class="page-item <?php echo $page>=$pages?'disabled':''; ?>"><a class="page-link" href="?page=<?php echo $page+1 . $pagination_query_str; ?>">Next</a></li>
</ul></nav></div><?php endif; ?>
</div></div>
<!-- Mobile Camera Barcode Search Modal -->
<div class="modal fade" id="searchScannerModal" tabindex="-1" aria-labelledby="searchScannerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="searchScannerModalLabel">
          <i class="fas fa-barcode me-2"></i> Scan Product Barcode
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="text-muted small mb-2">Align the barcode in front of your camera to search:</p>
        <div id="search-qr-reader" style="width: 100%; border-radius: 8px; overflow: hidden; background: #000; min-height: 240px;"></div>
        <div id="search-scan-feedback" class="mt-3 small"></div>
      </div>
      <div class="modal-footer justify-content-between">
        <small class="text-muted"><i class="fas fa-mobile-screen me-1"></i> Point mobile camera directly at barcode</small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
var searchQrScanner = null;

function playSearchBeep() {
    try {
        var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        var osc = audioCtx.createOscillator();
        var gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch(e) {}
}

function startSearchScanner() {
    var feedback = document.getElementById('search-scan-feedback');
    feedback.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Starting camera...</span>';

    if (typeof Html5Qrcode === 'undefined') {
        feedback.innerHTML = '<span class="text-danger">Scanner library loading. Please wait.</span>';
        return;
    }

    if (!searchQrScanner) {
        searchQrScanner = new Html5Qrcode("search-qr-reader");
    }

    const config = { fps: 10, qrbox: { width: 260, height: 180 } };

    searchQrScanner.start({ facingMode: "environment" }, config, onSearchScanSuccess)
        .then(() => {
            feedback.innerHTML = '<span class="text-success fw-bold"><i class="fas fa-video me-1"></i> Camera Active. Point at product barcode.</span>';
        })
        .catch(err => {
            searchQrScanner.start({ facingMode: "user" }, config, onSearchScanSuccess)
                .then(() => {
                    feedback.innerHTML = '<span class="text-success fw-bold">Camera Active. Point at product barcode.</span>';
                })
                .catch(e => {
                    feedback.innerHTML = '<span class="text-danger">Unable to access camera: ' + e + '. Please allow camera permission in browser.</span>';
                });
        });
}

function onSearchScanSuccess(decodedText) {
    playSearchBeep();
    var raw = decodedText.trim();
    document.getElementById('product_search_input').value = raw;
    document.getElementById('search-scan-feedback').innerHTML = '<div class="alert alert-success py-1 px-2 mb-0"><strong>Scanned:</strong> ' + raw + ' (Searching...)</div>';
    
    // Stop camera and submit form
    setTimeout(function() {
        var modalEl = document.getElementById('searchScannerModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
        document.getElementById('product_search_input').closest('form').submit();
    }, 500);
}

document.getElementById('searchScannerModal').addEventListener('hidden.bs.modal', function () {
    if (searchQrScanner) {
        searchQrScanner.stop().then(() => searchQrScanner.clear()).catch(e => console.log(e));
    }
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php'; ?>
