<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once('../../config/database.php');
require_once('../../config/functions.php');

// If already logged in redirect
if (isLoggedIn()) {
    redirectByRole();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $user = getSingleRow(
            "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.username = ? AND u.status = 'active'",
            "s", [$username]
        );

        if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_id']   = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['profile_image'] = $user['profile_image'];
            $_SESSION['last_activity'] = time();
            $_SESSION['low_stock_alerts'] = getLowStockProducts(20);

            // Update last login
            executeQuery("UPDATE users SET last_login = NOW() WHERE user_id = ?", "i", [$user['user_id']]);
            logActivity($user['user_id'], 'Login', 'User logged in');

            redirectByRole();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #3b82f6 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }
            /* Login Card Color */
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo .icon-wrap {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, #3b82f6, #3b82f6);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        .login-logo .icon-wrap i { color: #fff; font-size: 30px; }
        .login-logo h4 { font-weight: 700; color: #1e3a8a; margin: 0; }
        .login-logo p { color: #6b7280; font-size: 13px; }
        .form-label { font-weight: 600; color: #374151; font-size: 14px; }
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            transition: border-color .2s;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        .input-group-text {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-right: none;
            color: #6b7280;
        }
        .input-group .form-control { border-left: none; }
        .btn-login {
            background: linear-gradient(135deg, #3b82f6, #3b82f6);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            font-size: 15px;
            width: 100%;
            color: white;
            cursor: pointer;
            transition: opacity .2s;
        }
        .btn-login:hover { opacity: .9; }
        .demo-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 20px;
            font-size: 13px;
        }
        .demo-box h6 { color: #0369a1; font-weight: 700; margin-bottom: 6px; }
        .demo-box p { margin: 2px 0; color: #374151; }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <div class="icon-wrap"><i class="fas fa-boxes"></i></div>
            <h4>ISMS</h4>
            <p>Inventory Stock Management System</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['timeout'])): ?>
            <div class="alert-error"><i class="fas fa-clock"></i> Session expired. Please login again.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

       <div class="demo-box">
            <h6><i class="fas fa-info-circle"></i> Demo Credentials</h6>
            <p><strong>Admin:</strong> admin / admin123</p>
            <p><strong>Manager:</strong> manager / password</p>
            <p><strong>Salesman:</strong> salesman / password</p>
        </div> 
    </div>
</div>
</body>
</html>
