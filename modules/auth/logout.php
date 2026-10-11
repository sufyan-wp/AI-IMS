<?php
require_once('../../config/database.php');
require_once('../../config/functions.php');

if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'Logout', 'User logged out');
}

session_unset();
session_destroy();
header("Location: /inventory_system/modules/auth/login.php");
exit();
?>
