<?php
$page_title="Settings";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Admin'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $keys=['company_name','company_email','company_phone','company_address','tax_rate','currency','low_stock_alert'];
    foreach($keys as $k){
        $v=sanitize($_POST[$k]??'');
        executeQuery("UPDATE settings SET setting_value=? WHERE setting_key=?","ss",[$v,$k]);
    }
    // Logo upload
    if(!empty($_FILES['company_logo']['name'])){
        $up=uploadImage($_FILES['company_logo']);
        if($up['success']){
            $old=getSingleRow("SELECT setting_value FROM settings WHERE setting_key='company_logo'");
            if($old && $old['setting_value']) deleteImage($old['setting_value']);
            executeQuery("UPDATE settings SET setting_value=? WHERE setting_key='company_logo'","s",[$up['filename']]);
            $success='Settings and logo saved!';
        } else { $error='Logo upload failed: '.$up['message']; }
    } else { $success='Settings saved!'; }
    logActivity($_SESSION['user_id'],'Update Settings','Settings updated');
}
$rows=getAllRows("SELECT * FROM settings");
$cfg=[]; foreach($rows as $r) $cfg[$r['setting_key']]=$r['setting_value'];
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success):?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?php echo $error;?></div><?php endif;?>
<div class="page-header"><div><h2><i class="fas fa-cog"></i> Settings</h2></div></div>
<form method="POST" enctype="multipart/form-data">
<div class="row g-3">
<div class="col-md-8">
<div class="card mb-3"><div class="card-header">Company Information</div><div class="card-body">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($cfg['company_name']??'');?>"></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="company_email" class="form-control" value="<?php echo htmlspecialchars($cfg['company_email']??'');?>"></div>
<div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="company_phone" class="form-control" value="<?php echo htmlspecialchars($cfg['company_phone']??'');?>"></div>
<div class="col-md-6"><label class="form-label">Currency</label><select name="currency" class="form-select"><option value="PKR" <?php echo ($cfg['currency']??'PKR')==='PKR'?'selected':'';?>>PKR - Pakistani Rupee</option><option value="USD" <?php echo ($cfg['currency']??'')==='USD'?'selected':'';?>>USD - US Dollar</option><option value="EUR" <?php echo ($cfg['currency']??'')==='EUR'?'selected':'';?>>EUR - Euro</option></select></div>
<div class="col-12"><label class="form-label">Address</label><textarea name="company_address" class="form-control" rows="2"><?php echo htmlspecialchars($cfg['company_address']??'');?></textarea></div>
</div></div></div>
<div class="card"><div class="card-header">System Configuration</div><div class="card-body"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Default Tax Rate (%)</label><input type="number" name="tax_rate" class="form-control" step="0.01" min="0" max="100" value="<?php echo $cfg['tax_rate']??0;?>"></div>
<div class="col-md-6"><label class="form-label">Low Stock Alert (qty)</label><input type="number" name="low_stock_alert" class="form-control" min="1" value="<?php echo $cfg['low_stock_alert']??10;?>"></div>
</div></div></div>
</div>
<div class="col-md-4">
<div class="card"><div class="card-header">Company Logo</div><div class="card-body">
<?php if(!empty($cfg['company_logo'])):?>
<div class="mb-3"><img src="/inventory_system/assets/uploads/<?php echo htmlspecialchars($cfg['company_logo']);?>" style="max-width:100%;max-height:120px;border-radius:8px;border:1px solid #e2e8f0"><p class="text-muted mt-1" style="font-size:12px">Current logo</p></div>
<?php endif;?>
<label class="form-label">Upload New Logo</label>
<input type="file" name="company_logo" class="form-control" accept="image/*">
<small class="text-muted">JPG, PNG, GIF. Max 5MB.</small>
</div></div>
<div class="card mt-3"><div class="card-body"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Save Settings</button></div></div>
</div></div></form>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
