<?php
$page_title = "Add Product";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if (!hasRole(['Manager'])) { header("Location: /inventory_system/modules/inventory/manage_products.php"); exit(); }
$success = $error = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name     = sanitize($_POST['product_name']);
    $cat_id   = intval($_POST['category_id']);
    $brand    = sanitize($_POST['brand']);
    $barcode  = sanitize($_POST['barcode']);
    $cost     = floatval($_POST['cost_price']);
    $sell     = floatval($_POST['selling_price']);
    $qty      = intval($_POST['quantity']);
    $min_qty  = intval($_POST['min_quantity']);
    $desc     = sanitize($_POST['description']);
    $status   = sanitize($_POST['status']);
    $image    = '';

    if (empty($name)) { $error = 'Product name is required.'; }
    elseif ($sell <= 0) { $error = 'Selling price must be greater than 0.'; }
    else {
        if (!empty($_FILES['product_image']['name'])) {
            $up = uploadImage($_FILES['product_image']);
            if ($up['success']) $image = $up['filename'];
            else { $error = $up['message']; }
        }
        if (empty($error)) {
            $q = "INSERT INTO products (product_name,category_id,brand,barcode,cost_price,selling_price,quantity,min_quantity,description,product_image,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            if (executeQuery($q,"sissddiisss",[$name,$cat_id,$brand,$barcode,$cost,$sell,$qty,$min_qty,$desc,$image,$status])) {
                if ($qty > 0) {
                    $pid = $conn->insert_id;
                    executeQuery("INSERT INTO stock_logs (product_id,user_id,type,quantity,reference,notes) VALUES (?,?,'in',?,?,?)","iiiss",[$pid,$_SESSION['user_id'],$qty,'INITIAL','Initial stock']);
                }
                logActivity($_SESSION['user_id'],'Add Product',"Added: $name");
                $success = 'Product added successfully!';
            } else { $error = 'Failed to save product.'; }
        }
    }
}
$categories = getAllRows("SELECT * FROM categories WHERE status='active' ORDER BY category_name");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success): ?><div class="alert alert-success"><?php echo $success; ?> <a href="manage_products.php">View Products</a></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
<div class="page-header">
    <div><h2><i class="fas fa-plus"></i> Add Product</h2></div>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<form method="POST" enctype="multipart/form-data">
<div class="row g-3">
<div class="col-md-8">
<div class="card"><div class="card-header">Product Information</div><div class="card-body">
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Product Name *</label><input type="text" name="product_name" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
<div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="0">-- Select --</option><?php foreach($categories as $c): ?><option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control" placeholder="Brand name"></div>
<div class="col-md-6"><label class="form-label">Barcode</label><input type="text" name="barcode" class="form-control" placeholder="Barcode/SKU"></div>
<div class="col-md-6"><label class="form-label">Cost Price *</label><input type="number" name="cost_price" class="form-control" step="0.01" min="0" required></div>
<div class="col-md-6"><label class="form-label">Selling Price *</label><input type="number" name="selling_price" class="form-control" step="0.01" min="0" required></div>
<div class="col-md-3"><label class="form-label">Initial Qty</label><input type="number" name="quantity" class="form-control" min="0" value="0"></div>
<div class="col-md-3"><label class="form-label">Min Qty Alert</label><input type="number" name="min_quantity" class="form-control" min="0" value="5"></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
</div></div></div>
</div>
<div class="col-md-4">
<div class="card"><div class="card-header">Product Image</div><div class="card-body">
<input type="file" name="product_image" class="form-control" accept="image/*">
<small class="text-muted">Max 5MB. JPG, PNG, GIF only.</small>
<div id="preview" class="mt-2"></div>
</div></div>
<div class="card mt-3"><div class="card-body">
<button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Save Product</button>
</div></div>
</div>
</div>
</form>
<script>document.querySelector('input[type=file]').addEventListener('change',function(){const f=this.files[0];if(f){const r=new FileReader();r.onload=e=>document.getElementById('preview').innerHTML='<img src="'+e.target.result+'" style="max-width:100%;border-radius:8px;margin-top:8px">';r.readAsDataURL(f);}});</script>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php'; ?>
