<?php
$page_title = "Edit Product";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if (!hasRole(['Manager','Admin'])) { header("Location: /inventory_system/modules/inventory/manage_products.php"); exit(); }

$id = intval($_GET['id'] ?? 0);
$product = getSingleRow("SELECT * FROM products WHERE product_id=?","i",[$id]);
if (!$product) { header("Location: manage_products.php"); exit(); }

$success = $error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name    = sanitize($_POST['product_name']);
    $cat_id  = intval($_POST['category_id']);
    $brand   = sanitize($_POST['brand']);
    $barcode = sanitize($_POST['barcode']);
    $cost    = floatval($_POST['cost_price']);
    $sell    = floatval($_POST['selling_price']);
    $min_qty = intval($_POST['min_quantity']);
    $desc    = sanitize($_POST['description']);
    $status  = sanitize($_POST['status']);
    $image   = $product['product_image'];

    if (empty($name)) { $error = 'Product name required.'; }
    elseif ($sell <= 0) { $error = 'Selling price must be > 0.'; }
    else {
        if (!empty($_FILES['product_image']['name'])) {
            $up = uploadImage($_FILES['product_image']);
            if ($up['success']) {
                if ($image) deleteImage($image);
                $image = $up['filename'];
            } else { $error = $up['message']; }
        }
        if (empty($error)) {
            $q = "UPDATE products SET product_name=?,category_id=?,brand=?,barcode=?,cost_price=?,selling_price=?,min_quantity=?,description=?,product_image=?,status=? WHERE product_id=?";
            if (executeQuery($q,"sissddiissi",[$name,$cat_id,$brand,$barcode,$cost,$sell,$min_qty,$desc,$image,$status,$id])) {
                logActivity($_SESSION['user_id'],'Edit Product',"Updated: $name");
                $success = 'Product updated successfully!';
                $product = getSingleRow("SELECT * FROM products WHERE product_id=?","i",[$id]);
            } else { $error = 'Failed to update. Error: '.$conn->error; }
        }
    }
}
$categories = getAllRows("SELECT * FROM categories WHERE status='active' ORDER BY category_name");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success): ?><div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="page-header">
    <div><h2><i class="fas fa-edit"></i> Edit Product</h2><p><?php echo htmlspecialchars($product['product_name']); ?></p></div>
    <a href="manage_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<form method="POST" enctype="multipart/form-data">
<div class="row g-3">
<div class="col-md-8">
<div class="card"><div class="card-header">Product Information</div><div class="card-body">
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Product Name *</label><input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($product['product_name']); ?>" required></div>
<div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?php echo $product['status']==='active'?'selected':''; ?>>Active</option><option value="inactive" <?php echo $product['status']==='inactive'?'selected':''; ?>>Inactive</option></select></div>
<div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="0">-- Select --</option><?php foreach($categories as $c): ?><option value="<?php echo $c['category_id']; ?>" <?php echo $c['category_id']==$product['category_id']?'selected':''; ?>><?php echo htmlspecialchars($c['category_name']); ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($product['brand']??''); ?>"></div>
<div class="col-md-6"><label class="form-label">Barcode</label><input type="text" name="barcode" class="form-control" value="<?php echo htmlspecialchars($product['barcode']??''); ?>"></div>
<div class="col-md-6"><label class="form-label">Cost Price *</label><input type="number" name="cost_price" class="form-control" step="0.01" value="<?php echo $product['cost_price']; ?>" required></div>
<div class="col-md-6"><label class="form-label">Selling Price *</label><input type="number" name="selling_price" class="form-control" step="0.01" value="<?php echo $product['selling_price']; ?>" required></div>
<div class="col-md-4"><label class="form-label">Current Stock</label><input type="text" class="form-control" value="<?php echo $product['quantity']; ?>" readonly style="background:#f8fafc"></div>
<div class="col-md-4"><label class="form-label">Min Qty Alert</label><input type="number" name="min_quantity" class="form-control" min="0" value="<?php echo $product['min_quantity']; ?>"></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description']??''); ?></textarea></div>
</div></div></div>
</div>
<div class="col-md-4">
<div class="card"><div class="card-header">Product Image</div><div class="card-body">
<?php if($product['product_image']): ?>
<div class="mb-2"><img src="/inventory_system/assets/uploads/<?php echo htmlspecialchars($product['product_image']); ?>" style="max-width:100%;border-radius:8px;border:1px solid #e2e8f0" alt="Current"><p class="mt-1 text-muted" style="font-size:12px">Current image</p></div>
<?php endif; ?>
<label class="form-label">Upload New Image</label>
<input type="file" name="product_image" class="form-control" accept="image/*">
<small class="text-muted">Max 5MB. JPG, PNG, GIF. Leave empty to keep current.</small>
<div id="preview" class="mt-2"></div>
</div></div>
<div class="card mt-3"><div class="card-body">
<button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Save Changes</button>
</div></div>
</div></div>
</form>
<script>document.querySelector('input[type=file]').addEventListener('change',function(){const f=this.files[0];if(f){const r=new FileReader();r.onload=e=>document.getElementById('preview').innerHTML='<img src="'+e.target.result+'" style="max-width:100%;border-radius:8px;margin-top:8px">';r.readAsDataURL(f);}});</script>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php'; ?>
