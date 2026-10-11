<?php
$page_title="Suppliers";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Admin','Manager'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$role=$_SESSION['role_name'];
$success=$error='';
$can_edit=($role==='Manager');

if($can_edit && $_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['add'])){
        $n=sanitize($_POST['supplier_name']); $c=sanitize($_POST['company_name']); $e=sanitize($_POST['email']); $p=sanitize($_POST['phone']); $a=sanitize($_POST['address']);
        if(empty($n)){$error='Name required.';}
        elseif(executeQuery("INSERT INTO suppliers (supplier_name,company_name,email,phone,address) VALUES (?,?,?,?,?)","sssss",[$n,$c,$e,$p,$a])){logActivity($_SESSION['user_id'],'Add Supplier',"Added: $n");$success='Supplier added!';}
        else $error='Failed.';
    } elseif(isset($_POST['edit'])){
        $id=intval($_POST['sid']); $n=sanitize($_POST['supplier_name']); $c=sanitize($_POST['company_name']); $e=sanitize($_POST['email']); $p=sanitize($_POST['phone']); $a=sanitize($_POST['address']);
        if(executeQuery("UPDATE suppliers SET supplier_name=?,company_name=?,email=?,phone=?,address=? WHERE supplier_id=?","sssssi",[$n,$c,$e,$p,$a,$id])){$success='Updated!';}
        else $error='Failed.';
    }
}
if($can_edit && isset($_GET['delete'])){
    $id=intval($_GET['delete']);
    if(executeQuery("DELETE FROM suppliers WHERE supplier_id=?","i",[$id])){$success='Deleted!';}
    else $error='Cannot delete.';
}
$suppliers=getAllRows("SELECT * FROM suppliers ORDER BY supplier_name");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success):?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?php echo $error;?></div><?php endif;?>
<div class="page-header"><div><h2><i class="fas fa-truck"></i> Suppliers</h2></div>
<?php if($can_edit):?><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Supplier</button><?php endif;?></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>Name</th><th>Company</th><th>Email</th><th>Phone</th><?php if($can_edit):?><th>Actions</th><?php endif;?></tr></thead><tbody>
<?php foreach($suppliers as $i=>$s):?>
<tr><td><?php echo $i+1;?></td><td><strong><?php echo htmlspecialchars($s['supplier_name']);?></strong></td>
<td><?php echo htmlspecialchars($s['company_name']??'--');?></td><td><?php echo htmlspecialchars($s['email']??'--');?></td><td><?php echo htmlspecialchars($s['phone']??'--');?></td>
<?php if($can_edit):?><td>
<button class="btn btn-sm btn-info me-1" onclick='editS(<?php echo json_encode($s);?>)'><i class="fas fa-edit"></i></button>
<a href="?delete=<?php echo $s['supplier_id'];?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
</td><?php endif;?></tr>
<?php endforeach;?></tbody></table></div></div></div>
<?php if($can_edit):?>
<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5>Add Supplier</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body">
<div class="mb-3"><label class="form-label">Name *</label><input type="text" name="supplier_name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Company</label><input type="text" name="company_name" class="form-control"></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add" class="btn btn-primary">Save</button></div></form></div></div></div>
<div class="modal fade" id="editModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5>Edit Supplier</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body">
<input type="hidden" name="sid" id="es_id">
<div class="mb-3"><label class="form-label">Name *</label><input type="text" name="supplier_name" id="es_n" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Company</label><input type="text" name="company_name" id="es_c" class="form-control"></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="es_e" class="form-control"></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" id="es_p" class="form-control"></div>
<div class="mb-3"><label class="form-label">Address</label><textarea name="address" id="es_a" class="form-control" rows="2"></textarea></div>
</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="edit" class="btn btn-primary">Update</button></div></form></div></div></div>
<script>function editS(s){document.getElementById('es_id').value=s.supplier_id;document.getElementById('es_n').value=s.supplier_name;document.getElementById('es_c').value=s.company_name||'';document.getElementById('es_e').value=s.email||'';document.getElementById('es_p').value=s.phone||'';document.getElementById('es_a').value=s.address||'';new bootstrap.Modal(document.getElementById('editModal')).show();}</script>
<?php endif;?>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
