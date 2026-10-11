<?php
$page_title = "Categories";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if (!hasRole(['Admin','Manager'])) { header("Location: /inventory_system/modules/dashboard/dashboard.php"); exit(); }
$success=$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (isset($_POST['add'])) {
        $name=sanitize($_POST['category_name']); $desc=sanitize($_POST['description']);
        if (empty($name)) $error='Category name required.';
        elseif(executeQuery("INSERT INTO categories (category_name,description) VALUES (?,?)","ss",[$name,$desc])) { logActivity($_SESSION['user_id'],'Add Category',"Added: $name"); $success='Category added!'; }
        else $error='Failed to add.';
    } elseif(isset($_POST['edit'])) {
        $id=intval($_POST['cat_id']); $name=sanitize($_POST['category_name']); $desc=sanitize($_POST['description']);
        if(executeQuery("UPDATE categories SET category_name=?,description=? WHERE category_id=?","ssi",[$name,$desc,$id])) { logActivity($_SESSION['user_id'],'Edit Category',"Updated ID: $id"); $success='Updated!'; }
        else $error='Failed.';
    }
}
if(isset($_GET['delete'])) {
    $id=intval($_GET['delete']);
    if(executeQuery("DELETE FROM categories WHERE category_id=?","i",[$id])) { $success='Deleted!'; }
    else $error='Cannot delete - has products linked.';
}
$categories=getAllRows("SELECT c.*,(SELECT COUNT(*) FROM products WHERE category_id=c.category_id) as pcount FROM categories c ORDER BY c.category_name");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
<div class="page-header"><div><h2><i class="fas fa-list"></i> Categories</h2></div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Category</button></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light"><tr><th>#</th><th>Category Name</th><th>Description</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($categories as $i=>$c): ?>
<tr>
<td><?php echo $i+1; ?></td>
<td><strong><?php echo htmlspecialchars($c['category_name']); ?></strong></td>
<td><?php echo htmlspecialchars($c['description']??'--'); ?></td>
<td><span class="badge bg-primary"><?php echo $c['pcount']; ?></span></td>
<td><span class="badge bg-<?php echo $c['status']==='active'?'success':'secondary'; ?>"><?php echo ucfirst($c['status']); ?></span></td>
<td>
<button class="btn btn-sm btn-info me-1" onclick='editCat(<?php echo json_encode($c); ?>)'><i class="fas fa-edit"></i></button>
<a href="?delete=<?php echo $c['category_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
</td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div></div>

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Add Category</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body">
<div class="mb-3"><label class="form-label">Name *</label><input type="text" name="category_name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add" class="btn btn-primary">Save</button></div>
</form></div></div></div>

<div class="modal fade" id="editModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Edit Category</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body">
<input type="hidden" name="cat_id" id="edit_cat_id">
<div class="mb-3"><label class="form-label">Name *</label><input type="text" name="category_name" id="edit_cat_name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="edit_cat_desc" class="form-control" rows="3"></textarea></div>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="edit" class="btn btn-primary">Update</button></div>
</form></div></div></div>
<script>function editCat(c){document.getElementById('edit_cat_id').value=c.category_id;document.getElementById('edit_cat_name').value=c.category_name;document.getElementById('edit_cat_desc').value=c.description||'';new bootstrap.Modal(document.getElementById('editModal')).show();}</script>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php'; ?>
