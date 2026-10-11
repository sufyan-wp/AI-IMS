<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SESSION_TIMEOUT', 1800);

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function checkSession() {
    if (!isLoggedIn()) {
        header("Location: /inventory_system/modules/auth/login.php");
        exit();
    }
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header("Location: /inventory_system/modules/auth/login.php?timeout=1");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

function hasRole($roles) {
    if (!isLoggedIn()) return false;
    $user_role = $_SESSION['role_name'] ?? '';
    if (is_array($roles)) return in_array($user_role, $roles);
    return $user_role === $roles;
}

function redirectByRole() {
    if (!isLoggedIn()) return;
    $role = $_SESSION['role_name'];
    if ($role === 'Salesman') {
        header("Location: /inventory_system/modules/sales/create_invoice.php");
    } else {
        header("Location: /inventory_system/modules/dashboard/dashboard.php");
    }
    exit();
}

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function executeQuery($query, $types = "", $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error . " | Query: " . $query);
        return false;
    }
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $result = $stmt->execute();
    if (!$result) {
        error_log("Execute failed: " . $stmt->error);
    }
    $stmt->close();
    return $result;
}

function getSingleRow($query, $types = "", $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) return null;
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row;
}

function getAllRows($query, $types = "", $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) return [];
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getCount($table, $where = "") {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM `$table`";
    if (!empty($where)) $query .= " WHERE $where";
    $result = $conn->query($query);
    if (!$result) return 0;
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

function getLowStockProducts($limit = 10) {
    global $conn;
    $query = "SELECT product_name, quantity, min_quantity FROM products WHERE status='active' AND quantity <= COALESCE(min_quantity, 0) ORDER BY quantity ASC LIMIT " . (int)$limit;
    $result = $conn->query($query);
    if (!$result) return [];
    return $result->fetch_all(MYSQLI_ASSOC);
}

function uploadImage($file) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/inventory_system/assets/uploads/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    $max_size = 5 * 1024 * 1024;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed.'];
    }
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large. Max 5MB.'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    return ['success' => false, 'message' => 'Upload failed. Check folder permissions.'];
}

function deleteImage($filename) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/inventory_system/assets/uploads/';
    $filepath = $upload_dir . $filename;
    if (file_exists($filepath)) unlink($filepath);
}

function logActivity($user_id, $action, $description) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $query = "INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)";
    return executeQuery($query, "isss", [$user_id, $action, $description, $ip]);
}

function formatCurrency($amount) {
    return number_format($amount, 2);
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d M Y h:i A', strtotime($datetime));
}
?>
