<?php
$page_title="Customers";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
$role=$_SESSION['role_name'];
$success=$error='';
$can_edit=($role==='Salesman');

if($can_edit && $_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['add'])){
        $n=sanitize($_POST['customer_name']); $e=sanitize($_POST['email']); $p=sanitize($_POST['phone']); $a=sanitize($_POST['address']);
        if(empty($n)){$error='Name required.';}
        elseif(executeQuery("INSERT INTO customers (customer_name,email,phone,address) VALUES (?,?,?,?)","ssss",[$n,$e,$p,$a])){logActivity($_SESSION['user_id'],'Add Customer',"Added: $n");$success='Customer added!';}
        else $error='Failed.';
    } elseif(isset($_POST['edit'])){
        $id=intval($_POST['cid']); $n=sanitize($_POST['customer_name']); $e=sanitize($_POST['email']); $p=sanitize($_POST['phone']); $a=sanitize($_POST['address']);
        if(executeQuery("UPDATE customers SET customer_name=?,email=?,phone=?,address=? WHERE customer_id=?","ssssi",[$n,$e,$p,$a,$id])){logActivity($_SESSION['user_id'],'Edit Customer',"ID: $id");$success='Updated!';}
        else $error='Failed.';
    }
}
if($can_edit && isset($_GET['delete'])){
    $id=intval($_GET['delete']);
    if(executeQuery("DELETE FROM customers WHERE customer_id=?","i",[$id])){$success='Deleted!';}
    else $error='Cannot delete.';
}
$search=sanitize($_GET['search']??'');
$w="1=1"; if($search) $w.=" AND (customer_name LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%')";
$customers=getAllRows("SELECT * FROM customers WHERE $w ORDER BY customer_name");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success):?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?php echo $error;?></div><?php endif;?>
<div class="page-header"><div><h2><i class="fas fa-users"></i> Customers</h2><p><?php echo count($customers);?> customers</p></div>
<?php if($can_edit):?><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Customer</button><?php endif;?></div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="row g-2">
<div class="col-md-8"><input type="text" name="search" class="form-control" placeholder="Search name, phone, email..." value="<?php echo htmlspecialchars($search);?>"></div>
<div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Search</button></div>
<div class="col-md-2"><a href="manage_customers.php" class="btn btn-secondary w-100">Reset</a></div>
</form></div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><?php if($can_edit):?><th>Actions</th><?php endif;?></tr></thead><tbody>
<?php if(empty($customers)):?><tr><td colspan="6" class="text-center py-4 text-muted">No customers</td></tr>
<?php else: foreach($customers as $i=>$c):?>
<tr><td><?php echo $i+1;?></td><td><strong><?php echo htmlspecialchars($c['customer_name']);?></strong></td>
<td><?php echo htmlspecialchars($c['email']??'--');?></td><td><?php echo htmlspecialchars($c['phone']??'--');?></td>
<td><?php echo htmlspecialchars($c['address']??'--');?></td>
<?php if($can_edit):?><td>
<button class="btn btn-sm btn-info me-1" onclick='editC(<?php echo json_encode($c);?>)'><i class="fas fa-edit"></i></button>
<a href="?delete=<?php echo $c['customer_id'];?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
</td><?php endif;?></tr>
<?php endforeach; endif;?></tbody></table></div></div></div>

<?php if($can_edit):?>
<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5>Add Customer</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body">
<div class="mb-3"><label class="form-label">Name *</label><input type="text" name="customer_name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add" class="btn btn-primary">Save</button></div></form></div></div></div>

<div class="modal fade" id="editModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5>Edit Customer</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body">
<input type="hidden" name="cid" id="ec_id">
<div class="mb-3"><label class="form-label">Name *</label><input type="text" name="customer_name" id="ec_n" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="ec_e" class="form-control"></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" id="ec_p" class="form-control"></div>
<div class="mb-3"><label class="form-label">Address</label><textarea name="address" id="ec_a" class="form-control" rows="2"></textarea></div>
</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="edit" class="btn btn-primary">Update</button></div></form></div></div></div>
<script>function editC(c){document.getElementById('ec_id').value=c.customer_id;document.getElementById('ec_n').value=c.customer_name;document.getElementById('ec_e').value=c.email||'';document.getElementById('ec_p').value=c.phone||'';document.getElementById('ec_a').value=c.address||'';new bootstrap.Modal(document.getElementById('editModal')).show();}</script>
<?php endif;?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
