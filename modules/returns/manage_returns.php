<?php
$page_title="Returns";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Salesman'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $pid=intval($_POST['product_id']); $qty=intval($_POST['quantity']); $reason=sanitize($_POST['reason']); $sid=intval($_POST['sale_id']);
    $prod=getSingleRow("SELECT product_name, selling_price FROM products WHERE product_id=?","i",[$pid]);
    if($qty<=0){$error='Quantity must be > 0.';}
    elseif(!$prod){$error='Product not found.';}
    else{
        $price=floatval($prod['selling_price']);
        executeQuery("INSERT INTO returns (sale_id,product_id,user_id,quantity,reason,price) VALUES (?,?,?,?,?,?)","iiissd",[$sid?:null,$pid,$_SESSION['user_id'],$qty,$reason,$price]);
        executeQuery("UPDATE products SET quantity=quantity+? WHERE product_id=?","ii",[$qty,$pid]);
        executeQuery("INSERT INTO stock_logs (product_id,user_id,type,quantity,notes) VALUES (?,?,'return',?,?)","iiis",[$pid,$_SESSION['user_id'],$qty,'Return: '.$reason]);
        logActivity($_SESSION['user_id'],'Return','Returned '.$qty.' x '.$prod['product_name']);
        $success='Return processed successfully!';
    }
}
$products=getAllRows("SELECT product_id,product_name FROM products WHERE status='active' ORDER BY product_name");
$returns=getAllRows("SELECT r.*,p.product_name,u.full_name FROM returns r JOIN products p ON r.product_id=p.product_id JOIN users u ON r.user_id=u.user_id ORDER BY r.created_at DESC LIMIT 50");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success):?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?php echo $error;?></div><?php endif;?>
<div class="page-header"><div><h2><i class="fas fa-undo"></i> Returns</h2></div></div>
<div class="row g-3">
<div class="col-md-5"><div class="card"><div class="card-header">Process Return</div><div class="card-body">
<form method="POST">
<div class="mb-3"><label class="form-label">Product *</label><select name="product_id" class="form-select" required><option value="">-- Select Product --</option><?php foreach($products as $p):?><option value="<?php echo $p['product_id'];?>"><?php echo htmlspecialchars($p['product_name']);?></option><?php endforeach;?></select></div>
<div class="mb-3"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-control" min="1" required></div>
<div class="mb-3"><label class="form-label">Invoice No. (optional)</label><input type="text" name="sale_id" class="form-control" placeholder="Sale ID if known"></div>
<div class="mb-3"><label class="form-label">Reason *</label><textarea name="reason" class="form-control" rows="3" required placeholder="Reason for return..."></textarea></div>
<button type="submit" class="btn btn-warning w-100"><i class="fas fa-undo"></i> Process Return</button>
</form></div></div></div>
<div class="col-md-7"><div class="card"><div class="card-header">Return History</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Price (Unit)</th><th>Total Refund</th><th>Reason</th><th>By</th><th>Date/Time</th></tr></thead><tbody>
<?php if(empty($returns)):?><tr><td colspan="7" class="text-center py-3 text-muted">No returns yet</td></tr>
<?php else: foreach($returns as $r):?>
<tr><td><?php echo htmlspecialchars($r['product_name']);?></td><td><span class="badge bg-warning text-dark"><?php echo $r['quantity'];?></span></td><td>PKR <?php echo number_format($r['price'],2);?></td><td><strong>PKR <?php echo number_format($r['price']*$r['quantity'],2);?></strong></td><td><?php echo htmlspecialchars($r['reason']??'--');?></td><td><?php echo htmlspecialchars($r['full_name']);?></td><td><?php echo date('d M Y h:i A',strtotime($r['created_at']));?></td></tr>
<?php endforeach; endif;?></tbody></table>
</div></div></div></div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
