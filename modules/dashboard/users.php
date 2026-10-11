<?php
$page_title="Users";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
if(!hasRole(['Admin', 'Manager'])){header("Location: /inventory_system/modules/dashboard/dashboard.php");exit();}
$success=$error='';
$current_role=$_SESSION['role_name']??'';

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['add'])){
        $un=sanitize($_POST['username']); $fn=sanitize($_POST['full_name']); $em=sanitize($_POST['email']); $ph=sanitize($_POST['phone']); $pw=$_POST['password']; $rid=intval($_POST['role_id']);
        if($current_role==='Admin'){
            $rid=2;
        } elseif($current_role==='Manager'){
            $rid=3;
        } else {
            $error='Unauthorized action.';
        }
        if(empty($error)){
            if(empty($un)||empty($pw)||empty($fn)){$error='Name, username and password required.';}
            elseif(strlen($pw)<6){$error='Password min 6 characters.';}
            elseif(getSingleRow("SELECT user_id FROM users WHERE username=?","s",[$un])){$error='Username already exists.';}
            else{
                if(executeQuery("INSERT INTO users (username,email,password,full_name,phone,role_id) VALUES (?,?,?,?,?,?)","sssssi",[$un,$em,$pw,$fn,$ph,$rid])){
                    logActivity($_SESSION['user_id'],'Add User',"Added: $un");$success='User added!';
                } else $error='Failed.';
            }
        }
    } elseif(isset($_POST['reset_pw'])){
        $uid=intval($_POST['uid']); $pw=$_POST['new_password'];
        $target_user=getSingleRow("SELECT r.role_name FROM users u JOIN roles r ON u.role_id=r.role_id WHERE u.user_id=?","i",[$uid]);
        if($current_role==='Manager' && ($target_user['role_name'] === 'Admin' || $target_user['role_name'] === 'Manager')){
            $error='Managers cannot modify Admin or Manager accounts.';
        } elseif(strlen($pw)<6){$error='Min 6 characters.';}
        else{
            if(executeQuery("UPDATE users SET password=? WHERE user_id=?","si",[$pw,$uid])) $success='Password reset!';
            else $error='Failed.';
        }
    }
}
if(isset($_GET['toggle'])){
    $uid=intval($_GET['toggle']);
    if($uid!=$_SESSION['user_id']){
        $target_user=getSingleRow("SELECT r.role_name, u.status FROM users u JOIN roles r ON u.role_id=r.role_id WHERE u.user_id=?","i",[$uid]);
        if($current_role==='Manager' && ($target_user['role_name'] === 'Admin' || $target_user['role_name'] === 'Manager')){
            $error='Managers cannot toggle Admin or Manager accounts.';
        } else {
            $ns=$target_user['status']==='active'?'inactive':'active';
            executeQuery("UPDATE users SET status=? WHERE user_id=?","si",[$ns,$uid]);
            $success='Status updated!';
        }
    }
}
$users=getAllRows("SELECT u.*,r.role_name FROM users u JOIN roles r ON u.role_id=r.role_id ORDER BY u.created_at DESC");

if ($current_role === 'Admin') {
    $roles = getAllRows("SELECT * FROM roles WHERE role_name='Manager'");
    $sub_title = "Admin can add Manager accounts only";
    $btn_text = "Add Manager";
} elseif ($current_role === 'Manager') {
    $roles = getAllRows("SELECT * FROM roles WHERE role_name='Salesman'");
    $sub_title = "Manager can add Salesman accounts only";
    $btn_text = "Add Salesman";
} else {
    $roles = [];
    $sub_title = "";
    $btn_text = "Add User";
}

include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success):?><div class="alert alert-success"><?php echo $success;?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?php echo $error;?></div><?php endif;?>
<div class="page-header"><div><h2><i class="fas fa-user-cog"></i> Users</h2><p><?php echo $sub_title;?></p></div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> <?php echo $btn_text;?></button></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0"><thead class="table-light"><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead><tbody>
<?php foreach($users as $u): $rc=['Admin'=>'danger','Manager'=>'warning','Salesman'=>'info']; ?>
<tr>
<td><strong><?php echo htmlspecialchars($u['full_name']);?></strong><br><small class="text-muted"><?php echo htmlspecialchars($u['email']??'');?></small></td>
<td><code><?php echo htmlspecialchars($u['username']);?></code></td>
<td><span class="badge bg-<?php echo $rc[$u['role_name']]??'secondary';?>"><?php echo $u['role_name'];?></span></td>
<td><span class="badge bg-<?php echo $u['status']==='active'?'success':'secondary';?>"><?php echo ucfirst($u['status']);?></span></td>
<td><?php echo $u['last_login']?date('d M Y',strtotime($u['last_login'])):'Never';?></td>
<td>
<?php 
$is_protected = ($current_role === 'Manager' && ($u['role_name'] === 'Admin' || $u['role_name'] === 'Manager'));
if(!$is_protected):
?>
<button class="btn btn-sm btn-warning me-1" onclick='showReset(<?php echo $u['user_id'];?>,"<?php echo htmlspecialchars($u['username']);?>")'><i class="fas fa-key"></i></button>
<?php if($u['user_id']!=$_SESSION['user_id']):?>
<a href="?toggle=<?php echo $u['user_id'];?>" class="btn btn-sm btn-<?php echo $u['status']==='active'?'danger':'success';?>"><i class="fas fa-<?php echo $u['status']==='active'?'ban':'check';?>"></i></a>
<?php endif; endif;?>
</td></tr>
<?php endforeach;?></tbody></table></div></div></div>

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5><?php echo $btn_text;?> Account</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body">
<div class="alert alert-info"><i class="fas fa-info-circle"></i> Admins can only create Manager accounts. Managers can only create Salesman accounts.</div>
<input type="hidden" name="role_id" value="<?php echo ($roles[0]['role_id'] ?? 2);?>">
<div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="full_name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="mb-3"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" minlength="6" required></div>
</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add" class="btn btn-primary"><?php echo $btn_text;?></button></div></form></div></div></div>

<div class="modal fade" id="resetModal"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header"><h5>Reset Password</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body">
<input type="hidden" name="uid" id="ruid">
<p>Reset password for: <strong id="runame"></strong></p>
<div class="mb-3"><label class="form-label">New Password *</label><input type="password" name="new_password" class="form-control" minlength="6" required></div>
</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="reset_pw" class="btn btn-warning">Reset</button></div></form></div></div></div>
<script>function showReset(id,un){document.getElementById('ruid').value=id;document.getElementById('runame').textContent=un;new bootstrap.Modal(document.getElementById('resetModal')).show();}</script>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
