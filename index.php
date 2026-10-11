<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /inventory_system/modules/dashboard/dashboard.php");
} else {
    header("Location: /inventory_system/modules/auth/login.php");
}
exit();
?>
