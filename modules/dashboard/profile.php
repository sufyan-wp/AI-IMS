<?php
$page_title="My Profile";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();

$user_id = $_SESSION['user_id'];
$success = $error = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $fn = sanitize($_POST['full_name'] ?? '');
    $em = sanitize($_POST['email'] ?? '');
    $ph = sanitize($_POST['phone'] ?? '');
    $pw = $_POST['password'] ?? '';
    
    if (empty($fn) || empty($em)) {
        $error = 'Name and email are required.';
    } else {
        $pw_sql = "";
        $params = [$fn, $em, $ph];
        $types = "sss";
        
        if (!empty($pw)) {
            if (strlen($pw) < 6) {
                $error = 'Password must be at least 6 characters.';
            } else {
                $pw_sql = ", password=?";
                $params[] = $pw;
                $types .= "s";
            }
        }
        
        if (empty($error)) {
            $img_sql = "";
            if (!empty($_FILES['profile_image']['name'])) {
                $up = uploadImage($_FILES['profile_image']);
                if ($up['success']) {
                    $old = getSingleRow("SELECT profile_image FROM users WHERE user_id=?", "i", [$user_id]);
                    if ($old && $old['profile_image']) {
                        deleteImage($old['profile_image']);
                    }
                    $img_sql = ", profile_image=?";
                    $params[] = $up['filename'];
                    $types .= "s";
                    $_SESSION['profile_image'] = $up['filename'];
                } else {
                    $error = 'Profile image upload failed: ' . $up['message'];
                }
            }
            
            if (empty($error)) {
                $params[] = $user_id;
                $types .= "i";
                $query = "UPDATE users SET full_name=?, email=?, phone=? {$pw_sql} {$img_sql} WHERE user_id=?";
                
                if (executeQuery($query, $types, $params)) {
                    $_SESSION['full_name'] = $fn;
                    $success = 'Profile updated successfully!';
                    logActivity($user_id, 'Update Profile', 'Updated profile details');
                } else {
                    $error = 'Failed to update profile in database.';
                }
            }
        }
    }
}

$user = getSingleRow("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id=r.role_id WHERE u.user_id=?", "i", [$user_id]);
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<?php if($success):?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo $success;?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error;?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif;?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-user-circle"></i> My Profile</h2>
        <p>Manage your account settings, password, and profile picture</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Avatar Display & Upload -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="profile-avatar-container mb-4 position-relative mx-auto" style="width: 150px; height: 150px;">
                    <div class="rounded-circle overflow-hidden border border-4 border-primary-light shadow-sm w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                        <?php if(!empty($user['profile_image'])): ?>
                            <img src="/inventory_system/assets/uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span class="display-3 text-primary fw-bold"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted small mb-3"><?php echo htmlspecialchars($user['role_name']); ?></p>
                <span class="badge bg-success-light text-success px-3 py-2 rounded-pill"><i class="fas fa-shield-alt me-1"></i> Account Active</span>
            </div>
        </div>
    </div>

    <!-- Right Column: Personal Information Form -->
    <div class="col-md-8">
        <form method="POST" enctype="multipart/form-data">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-id-card me-2"></i> Personal Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['username']); ?>" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Role</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($user['role_name']); ?>" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Image</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
                            <small class="text-muted">Accepts JPG, PNG, GIF. Max 5MB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-warning fw-bold"><i class="fas fa-lock me-2"></i> Change Password</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password" minlength="6">
                            <small class="text-muted">Minimum 6 characters.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 bg-transparent">
                <div class="card-body p-0 text-end">
                    <button type="submit" class="btn btn-primary px-4 py-2"><i class="fas fa-save me-2"></i> Save Profile Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
