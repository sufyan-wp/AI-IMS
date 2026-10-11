<?php
$page_title="Stock History";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Admin','Manager'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$search=sanitize($_GET['search']??''); $type=sanitize($_GET['type']??'');
$w="1=1"; if($search) $w.=" AND p.product_name LIKE '%$search%'"; if($type) $w.=" AND s.type='$type'";
$logs=getAllRows("SELECT s.*,p.product_name,u.full_name FROM stock_logs s JOIN products p ON s.product_id=p.product_id JOIN users u ON s.user_id=u.user_id WHERE $w ORDER BY s.created_at DESC LIMIT 200");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<div class="page-header"><div><h2><i class="fas fa-history"></i> Stock History</h2></div></div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="row g-2">
<div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Search product..." value="<?php echo htmlspecialchars($search);?>"></div>
<div class="col-md-3"><select name="type" class="form-select"><option value="">All Types</option><option value="in" <?php echo $type==='in'?'selected':'';?>>In</option><option value="out" <?php echo $type==='out'?'selected':'';?>>Out</option><option value="sale">Sale</option><option value="return">Return</option></select></div>
<div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
<div class="col-md-2"><a href="stock_history.php" class="btn btn-secondary w-100">Reset</a></div>
</form></div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0"><thead class="table-light"><tr><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>Reference</th><th>By</th></tr></thead><tbody>
<?php if(empty($logs)):?><tr><td colspan="6" class="text-center py-4 text-muted">No records</td></tr>
<?php else: $c=['in'=>'success','out'=>'danger','sale'=>'warning','return'=>'info']; foreach($logs as $l):?>
<tr><td><?php echo date('d M Y H:i',strtotime($l['created_at']));?></td><td><?php echo htmlspecialchars($l['product_name']);?></td>
<td><span class="badge bg-<?php echo $c[$l['type']]??'secondary';?>"><?php echo ucfirst($l['type']);?></span></td>
<td><?php echo ($l['type']==='out'?'-':'+'). $l['quantity'];?></td>
<td><?php echo htmlspecialchars($l['reference']??'--');?></td><td><?php echo htmlspecialchars($l['full_name']);?></td></tr>
<?php endforeach; endif;?></tbody></table></div></div></div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
