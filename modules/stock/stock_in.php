<?php
$page_title="Stock In";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Admin'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $pid=intval($_POST['product_id']); $qty=intval($_POST['quantity']); $ref=sanitize($_POST['reference']); $notes=sanitize($_POST['notes']);
    if($qty<=0){$error='Quantity must be greater than 0.';}
    else{
        executeQuery("UPDATE products SET quantity=quantity+? WHERE product_id=?","ii",[$qty,$pid]);
        executeQuery("INSERT INTO stock_logs (product_id,user_id,type,quantity,reference,notes) VALUES (?,?,'in',?,?,?)","iiiss",[$pid,$_SESSION['user_id'],$qty,$ref,$notes]);
        logActivity($_SESSION['user_id'],'Stock In',"Product ID $pid Qty: $qty");
        $success="Stock added successfully!";
    }
}
$products=getAllRows("SELECT product_id,product_name,quantity FROM products WHERE status='active' ORDER BY product_name");
$recent=getAllRows("SELECT s.*,p.product_name FROM stock_logs s JOIN products p ON s.product_id=p.product_id WHERE s.type='in' ORDER BY s.created_at DESC LIMIT 10");
$pre_pid = intval($_GET['product_id'] ?? 0);
$pre_qty = intval($_GET['quantity'] ?? 0);
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success): ?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<?php if($error): ?><div class="alert alert-danger"><?php echo $error;?></div><?php endif;?>
<?php if($pre_pid > 0): ?>
    <div class="alert alert-info py-2 d-flex align-items-center gap-2 mb-3">
        <i class="fas fa-brain fs-5"></i>
        <div><strong>AI Restock Assistant:</strong> Product and recommended reorder quantity have been pre-filled from your AI stockout prediction.</div>
    </div>
<?php endif; ?>
<div class="page-header"><div><h2><i class="fas fa-arrow-circle-down text-success"></i> Stock In</h2><p>Add inventory</p></div><a href="stock_history.php" class="btn btn-secondary"><i class="fas fa-history"></i> History</a></div>
<div class="row g-3">
<div class="col-md-6"><div class="card"><div class="card-header">Add Stock</div><div class="card-body">
<form method="POST">
<div class="mb-3"><label class="form-label">Product *</label><select name="product_id" class="form-select" required><option value="">-- Select Product --</option><?php foreach($products as $p):?><option value="<?php echo $p['product_id'];?>" <?php echo ($pre_pid === (int)$p['product_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['product_name']);?> (Qty: <?php echo $p['quantity'];?>)</option><?php endforeach;?></select></div>
<div class="mb-3"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-control" min="1" value="<?php echo $pre_qty > 0 ? $pre_qty : ''; ?>" required></div>
<div class="mb-3"><label class="form-label">Reference No.</label><input type="text" name="reference" class="form-control" value="<?php echo $pre_pid > 0 ? 'AI-RESTOCK-'.date('md') : ''; ?>" placeholder="e.g. PO-001"></div>
<div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?php echo $pre_pid > 0 ? 'Replenishment based on AI Stockout Run-out Date prediction.' : ''; ?></textarea></div>
<button type="submit" class="btn btn-success w-100"><i class="fas fa-plus-circle"></i> Add Stock</button>
</form></div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">Recent Stock In</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Date</th></tr></thead><tbody>
<?php foreach($recent as $l):?><tr><td><?php echo htmlspecialchars($l['product_name']);?></td><td><span class="badge bg-success">+<?php echo $l['quantity'];?></span></td><td><?php echo date('d M Y',strtotime($l['created_at']));?></td></tr><?php endforeach;?>
</tbody></table></div></div></div></div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
