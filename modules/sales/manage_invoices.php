<?php
$page_title="Invoices";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
$role=$_SESSION['role_name'];
$search=sanitize($_GET['search']??'');
$date_from=sanitize($_GET['date_from']??'');
$date_to=sanitize($_GET['date_to']??'');
$w="1=1";
if($search) $w.=" AND (s.invoice_number LIKE '%$search%' OR s.customer_name LIKE '%$search%')";
if($date_from) $w.=" AND DATE(s.created_at)>='$date_from'";
if($date_to) $w.=" AND DATE(s.created_at)<='$date_to'";
$page=max(1,intval($_GET['page']??1)); $limit=15; $offset=($page-1)*$limit;
$total=$conn->query("SELECT COUNT(*) as c FROM sales s WHERE $w")->fetch_assoc()['c'];
$pages=max(1,ceil($total/$limit));
$sales=getAllRows("SELECT s.*,u.full_name FROM sales s JOIN users u ON s.user_id=u.user_id WHERE $w ORDER BY s.created_at DESC LIMIT $offset,$limit");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<div class="page-header">
    <div><h2><i class="fas fa-receipt"></i> Invoices</h2><p>Total: <?php echo $total;?></p></div>
    <?php if($role==='Salesman'):?><a href="create_invoice.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Invoice</a><?php endif;?>
</div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="row g-2">
<div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Invoice# or customer..." value="<?php echo htmlspecialchars($search);?>"></div>
<div class="col-md-2"><input type="date" name="date_from" class="form-control" value="<?php echo $date_from;?>"></div>
<div class="col-md-2"><input type="date" name="date_to" class="form-control" value="<?php echo $date_to;?>"></div>
<div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button></div>
<div class="col-md-2"><a href="manage_invoices.php" class="btn btn-secondary w-100">Reset</a></div>
</form></div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0"><thead class="table-light">
<tr><th>Invoice#</th><th>Customer</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th><th>By</th><th>Actions</th></tr></thead>
<tbody>
<?php if(empty($sales)):?><tr><td colspan="8" class="text-center py-4 text-muted">No invoices found</td></tr>
<?php else: foreach($sales as $s):?>
<tr>
<td><strong><?php echo htmlspecialchars($s['invoice_number']);?></strong></td>
<td><?php echo htmlspecialchars($s['customer_name']??'Walk-in');?></td>
<td><strong>PKR <?php echo number_format($s['total_amount'],0);?></strong></td>
<td><span class="badge bg-info"><?php echo $s['payment_method'];?></span></td>
<td><span class="badge bg-<?php echo $s['payment_status']==='Paid'?'success':($s['payment_status']==='Pending'?'warning':'info');?>"><?php echo $s['payment_status'];?></span></td>
<td><?php echo date('d M Y',strtotime($s['created_at']));?></td>
<td><?php echo htmlspecialchars($s['full_name']);?></td>
<td>
<a href="view_invoice.php?id=<?php echo $s['sale_id'];?>" class="btn btn-sm btn-info me-1" title="View"><i class="fas fa-eye"></i></a>
<a href="print_invoice.php?id=<?php echo $s['sale_id'];?>" class="btn btn-sm btn-secondary" target="_blank" title="Print"><i class="fas fa-print"></i></a>
</td>
</tr>
<?php endforeach; endif;?></tbody></table></div>
<?php if($pages>1):?><div class="p-3"><nav><ul class="pagination justify-content-center mb-0">
<li class="page-item <?php echo $page<=1?'disabled':'';?>"><a class="page-link" href="?page=<?php echo $page-1;?>&search=<?php echo urlencode($search);?>">Prev</a></li>
<?php for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++):?><li class="page-item <?php echo $i==$page?'active':'';?>"><a class="page-link" href="?page=<?php echo $i;?>&search=<?php echo urlencode($search);?>"><?php echo $i;?></a></li><?php endfor;?>
<li class="page-item <?php echo $page>=$pages?'disabled':'';?>"><a class="page-link" href="?page=<?php echo $page+1;?>&search=<?php echo urlencode($search);?>">Next</a></li>
</ul></nav></div><?php endif;?>
</div></div>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
