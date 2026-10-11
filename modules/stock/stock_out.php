<?php
$page_title="Stock Out";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Admin'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $pid=intval($_POST['product_id']); $qty=intval($_POST['quantity']); $ref=sanitize($_POST['reference']); $notes=sanitize($_POST['notes']);
    $prod=getSingleRow("SELECT quantity FROM products WHERE product_id=?","i",[$pid]);
    if($qty<=0){$error='Quantity must be > 0.';}
    elseif(!$prod||$prod['quantity']<$qty){$error='Insufficient stock. Available: '.($prod['quantity']??0);}
    else{
        executeQuery("UPDATE products SET quantity=quantity-? WHERE product_id=?","ii",[$qty,$pid]);
        executeQuery("INSERT INTO stock_logs (product_id,user_id,type,quantity,reference,notes) VALUES (?,?,'out',?,?,?)","iiiss",[$pid,$_SESSION['user_id'],$qty,$ref,$notes]);
        logActivity($_SESSION['user_id'],'Stock Out',"Product ID $pid Qty: $qty");
        $success="Stock removed successfully!";
    }
}
$products=getAllRows("SELECT product_id,product_name,quantity FROM products WHERE status='active' ORDER BY product_name");
$recent=getAllRows("SELECT s.*,p.product_name FROM stock_logs s JOIN products p ON s.product_id=p.product_id WHERE s.type='out' ORDER BY s.created_at DESC LIMIT 10");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success):?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?php echo $error;?></div><?php endif;?>
<div class="page-header"><div><h2><i class="fas fa-arrow-circle-up text-danger"></i> Stock Out</h2><p>Remove inventory</p></div><a href="stock_history.php" class="btn btn-secondary"><i class="fas fa-history"></i> History</a></div>
<div class="row g-3">
<div class="col-md-6"><div class="card"><div class="card-header">Remove Stock</div><div class="card-body">
<form method="POST">
<div class="mb-3"><label class="form-label">Product *</label><select name="product_id" class="form-select" required><option value="">-- Select Product --</option><?php foreach($products as $p):?><option value="<?php echo $p['product_id'];?>"><?php echo htmlspecialchars($p['product_name']);?> (Qty: <?php echo $p['quantity'];?>)</option><?php endforeach;?></select></div>
<div class="mb-3"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-control" min="1" required></div>
<div class="mb-3"><label class="form-label">Reference</label><input type="text" name="reference" class="form-control"></div>
<div class="mb-3"><label class="form-label">Reason</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
<button type="submit" class="btn btn-danger w-100"><i class="fas fa-minus-circle"></i> Remove Stock</button>
</form></div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">Recent Stock Out</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Date</th></tr></thead><tbody>
<?php foreach($recent as $l):?><tr><td><?php echo htmlspecialchars($l['product_name']);?></td><td><span class="badge bg-danger">-<?php echo $l['quantity'];?></span></td><td><?php echo date('d M Y',strtotime($l['created_at']));?></td></tr><?php endforeach;?>
</tbody></table></div></div></div></div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
