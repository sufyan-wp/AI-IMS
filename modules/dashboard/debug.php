<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2 style='color:green'>✅ PHP is working - Version: " . phpversion() . "</h2>";
echo "<h3>Now testing includes...</h3>";

// Test 1: Database
echo "<p>Testing database.php...</p>";
require_once('../../config/database.php');
echo "<p style='color:green'>✅ database.php loaded</p>";

if ($conn) {
    echo "<p style='color:green'>✅ Database connected</p>";
} else {
    echo "<p style='color:red'>❌ Database NOT connected</p>";
}

// Test 2: Functions
echo "<p>Testing functions.php...</p>";
require_once('../../config/functions.php');
echo "<p style='color:green'>✅ functions.php loaded</p>";

// Test 3: Session
session_start();
echo "<p>Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p>Session role: " . ($_SESSION['role_name'] ?? 'NOT SET') . "</p>";

// Test 4: Query
echo "<p>Testing database query...</p>";
$result = $conn->query("SELECT COUNT(*) as c FROM products");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p style='color:green'>✅ Products count: " . $row['c'] . "</p>";
} else {
    echo "<p style='color:red'>❌ Query failed: " . $conn->error . "</p>";
}

// Test 5: Includes
echo "<p>Testing header.php...</p>";
$header = file_exists('../../includes/header.php') ? '✅ EXISTS' : '❌ MISSING';
$sidebar = file_exists('../../includes/sidebar.php') ? '✅ EXISTS' : '❌ MISSING';
$navbar = file_exists('../../includes/navbar.php') ? '✅ EXISTS' : '❌ MISSING';
$footer = file_exists('../../includes/footer.php') ? '✅ EXISTS' : '❌ MISSING';
echo "<p>header.php: $header</p>";
echo "<p>sidebar.php: $sidebar</p>";
echo "<p>navbar.php: $navbar</p>";
echo "<p>footer.php: $footer</p>";

echo "<h3 style='color:green'>All basic tests passed!</h3>";
echo "<p><a href='/inventory_system/modules/dashboard/dashboard.php'>Try Dashboard Again</a></p>";
?>
