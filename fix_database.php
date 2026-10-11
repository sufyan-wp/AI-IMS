<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('config/database.php');

echo "<h2>🔧 Database Fix Script</h2>";
echo "<p>Checking and fixing database structure...</p>";

$fixes = 0;
$errors = 0;

// Check what columns exist in sales table
$result = $conn->query("DESCRIBE sales");
if (!$result) {
    echo "<p style='color:red'>❌ Sales table does not exist! Please import the SQL file.</p>";
    exit;
}

$cols = [];
while ($row = $result->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo "<p>Current sales columns: <code>" . implode(', ', $cols) . "</code></p>";

// Fix missing columns in sales table
$needed = [
    'total_amount'   => "ALTER TABLE sales ADD COLUMN `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00",
    'subtotal'       => "ALTER TABLE sales ADD COLUMN `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00",
    'tax_rate'       => "ALTER TABLE sales ADD COLUMN `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00",
    'tax_amount'     => "ALTER TABLE sales ADD COLUMN `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00",
    'discount'       => "ALTER TABLE sales ADD COLUMN `discount` decimal(10,2) NOT NULL DEFAULT 0.00",
    'payment_method' => "ALTER TABLE sales ADD COLUMN `payment_method` enum('Cash','Card','Bank Transfer','Cheque') DEFAULT 'Cash'",
    'payment_status' => "ALTER TABLE sales ADD COLUMN `payment_status` enum('Paid','Pending','Partial') DEFAULT 'Paid'",
    'customer_name'  => "ALTER TABLE sales ADD COLUMN `customer_name` varchar(100) DEFAULT NULL",
    'user_id'        => "ALTER TABLE sales ADD COLUMN `user_id` int(11) NOT NULL DEFAULT 1",
    'notes'          => "ALTER TABLE sales ADD COLUMN `notes` text DEFAULT NULL",
    'invoice_number' => "ALTER TABLE sales ADD COLUMN `invoice_number` varchar(50) DEFAULT NULL",
    'customer_id'    => "ALTER TABLE sales ADD COLUMN `customer_id` int(11) DEFAULT NULL",
];

foreach ($needed as $col => $sql) {
    if (!in_array($col, $cols)) {
        if ($conn->query($sql)) {
            echo "<p style='color:green'>✅ Added column: <strong>$col</strong></p>";
            $fixes++;
        } else {
            echo "<p style='color:red'>❌ Failed to add $col: " . $conn->error . "</p>";
            $errors++;
        }
    } else {
        echo "<p style='color:blue'>ℹ️ Column exists: $col</p>";
    }
}

// Check sale_items table
$result2 = $conn->query("SHOW TABLES LIKE 'sale_items'");
if ($result2->num_rows == 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS `sale_items` (
        `item_id` int(11) NOT NULL AUTO_INCREMENT,
        `sale_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `product_name` varchar(200) NOT NULL,
        `quantity` int(11) NOT NULL,
        `unit_price` decimal(10,2) NOT NULL,
        `subtotal` decimal(10,2) NOT NULL,
        PRIMARY KEY (`item_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color:green'>✅ Created sale_items table</p>";
    $fixes++;
}

// Check stock_logs table
$result3 = $conn->query("SHOW TABLES LIKE 'stock_logs'");
if ($result3->num_rows == 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS `stock_logs` (
        `log_id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL DEFAULT 1,
        `type` enum('in','out','sale','return') NOT NULL,
        `quantity` int(11) NOT NULL,
        `reference` varchar(100) DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color:green'>✅ Created stock_logs table</p>";
    $fixes++;
}

// Check returns table
$result4 = $conn->query("SHOW TABLES LIKE 'returns'");
if ($result4->num_rows == 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS `returns` (
        `return_id` int(11) NOT NULL AUTO_INCREMENT,
        `sale_id` int(11) DEFAULT NULL,
        `product_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL DEFAULT 1,
        `quantity` int(11) NOT NULL,
        `reason` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`return_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color:green'>✅ Created returns table</p>";
    $fixes++;
}

// Check activity_logs table
$result5 = $conn->query("SHOW TABLES LIKE 'activity_logs'");
if ($result5->num_rows == 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
        `log_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `action` varchar(100) NOT NULL,
        `description` text DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color:green'>✅ Created activity_logs table</p>";
    $fixes++;
}

// Check settings table
$result6 = $conn->query("SHOW TABLES LIKE 'settings'");
if ($result6->num_rows == 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS `settings` (
        `setting_id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text DEFAULT NULL,
        PRIMARY KEY (`setting_id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $conn->query("INSERT INTO settings (setting_key,setting_value) VALUES
        ('company_name','ABC Trading Company'),
        ('company_email','info@company.com'),
        ('company_phone','051-1234567'),
        ('company_address','Islamabad, Pakistan'),
        ('tax_rate','8'),
        ('currency','PKR'),
        ('low_stock_alert','10'),
        ('company_logo','')");
    echo "<p style='color:green'>✅ Created settings table with defaults</p>";
    $fixes++;
}

// Check products table has needed columns
$rp = $conn->query("DESCRIBE products");
$pcols = [];
while ($row = $rp->fetch_assoc()) $pcols[] = $row['Field'];

if (!in_array('status', $pcols)) {
    $conn->query("ALTER TABLE products ADD COLUMN `status` enum('active','inactive') DEFAULT 'active'");
    echo "<p style='color:green'>✅ Added status column to products</p>";
    $fixes++;
}
if (!in_array('product_image', $pcols)) {
    $conn->query("ALTER TABLE products ADD COLUMN `product_image` varchar(255) DEFAULT NULL");
    echo "<p style='color:green'>✅ Added product_image column to products</p>";
    $fixes++;
}
if (!in_array('min_quantity', $pcols)) {
    $conn->query("ALTER TABLE products ADD COLUMN `min_quantity` int(11) NOT NULL DEFAULT 5");
    echo "<p style='color:green'>✅ Added min_quantity column to products</p>";
    $fixes++;
}

// Check customers table status column
$rc = $conn->query("DESCRIBE customers");
$ccols = [];
while ($row = $rc->fetch_assoc()) $ccols[] = $row['Field'];
if (!in_array('status', $ccols)) {
    $conn->query("ALTER TABLE customers ADD COLUMN `status` enum('active','inactive') DEFAULT 'active'");
    echo "<p style='color:green'>✅ Added status column to customers</p>";
    $fixes++;
}

// Check categories table status column
$rcat = $conn->query("DESCRIBE categories");
$catcols = [];
while ($row = $rcat->fetch_assoc()) $catcols[] = $row['Field'];
if (!in_array('status', $catcols)) {
    $conn->query("ALTER TABLE categories ADD COLUMN `status` enum('active','inactive') DEFAULT 'active'");
    echo "<p style='color:green'>✅ Added status column to categories</p>";
    $fixes++;
}

// Check suppliers table status column
$rsup = $conn->query("DESCRIBE suppliers");
$supcols = [];
while ($row = $rsup->fetch_assoc()) $supcols[] = $row['Field'];
if (!in_array('status', $supcols)) {
    $conn->query("ALTER TABLE suppliers ADD COLUMN `status` enum('active','inactive') DEFAULT 'active'");
    echo "<p style='color:green'>✅ Added status column to suppliers</p>";
    $fixes++;
}

// Check sale_items total_price/subtotal column
$rsi = $conn->query("DESCRIBE sale_items");
$sicols = [];
while ($row = $rsi->fetch_assoc()) $sicols[] = $row['Field'];
if (in_array('total_price', $sicols) && !in_array('subtotal', $sicols)) {
    $conn->query("ALTER TABLE sale_items CHANGE COLUMN total_price subtotal decimal(10,2) NOT NULL");
    echo "<p style='color:green'>✅ Renamed total_price to subtotal in sale_items</p>";
    $fixes++;
}

// Check stock_logs action_type/type and reference_number/reference columns
$rsl = $conn->query("DESCRIBE stock_logs");
$slcols = [];
while ($row = $rsl->fetch_assoc()) $slcols[] = $row['Field'];
if (in_array('action_type', $slcols)) {
    $conn->query("UPDATE stock_logs SET action_type = 'in' WHERE action_type = 'stock_in'");
    $conn->query("UPDATE stock_logs SET action_type = 'out' WHERE action_type = 'stock_out'");
    $conn->query("ALTER TABLE stock_logs CHANGE COLUMN action_type type enum('in','out','sale','return') NOT NULL");
    echo "<p style='color:green'>✅ Renamed action_type to type in stock_logs</p>";
    $fixes++;
}
if (in_array('reference_number', $slcols)) {
    $conn->query("ALTER TABLE stock_logs CHANGE COLUMN reference_number reference varchar(100) DEFAULT NULL");
    echo "<p style='color:green'>✅ Renamed reference_number to reference in stock_logs</p>";
    $fixes++;
}

// Check returns return_reason/reason column
$rret = $conn->query("DESCRIBE returns");
$retcols = [];
while ($row = $rret->fetch_assoc()) $retcols[] = $row['Field'];
if (in_array('return_reason', $retcols) && !in_array('reason', $retcols)) {
    $conn->query("ALTER TABLE returns CHANGE COLUMN return_reason reason text");
    echo "<p style='color:green'>✅ Renamed return_reason to reason in returns</p>";
    $fixes++;
}
$conn->query("ALTER TABLE returns MODIFY COLUMN sale_id int(11) DEFAULT NULL");
if (!in_array('price', $retcols)) {
    $conn->query("ALTER TABLE returns ADD COLUMN `price` decimal(10,2) NOT NULL DEFAULT 0.00");
    echo "<p style='color:green'>✅ Added price column to returns</p>";
    $fixes++;
}

// Check users table profile_image column
$rusers = $conn->query("DESCRIBE users");
$ucols = [];
while ($row = $rusers->fetch_assoc()) $ucols[] = $row['Field'];
if (!in_array('profile_image', $ucols)) {
    $conn->query("ALTER TABLE users ADD COLUMN `profile_image` varchar(255) DEFAULT NULL");
    echo "<p style='color:green'>✅ Added profile_image column to users</p>";
    $fixes++;
}

// Modify sales enums
$conn->query("ALTER TABLE sales MODIFY COLUMN payment_method enum('Cash','Card','Bank Transfer','Cheque') DEFAULT 'Cash'");
$conn->query("ALTER TABLE sales MODIFY COLUMN payment_status enum('Paid','Pending','Partial') DEFAULT 'Paid'");

// Repair product_sales_stats view
$conn->query("CREATE OR REPLACE VIEW `product_sales_stats` AS 
select `p`.`product_id` AS `product_id`,
       `p`.`product_name` AS `product_name`,
       `c`.`category_name` AS `category_name`,
       sum(`si`.`quantity`) AS `total_sold`,
       sum(`si`.`subtotal`) AS `total_revenue` 
from ((`products` `p` 
join `categories` `c` on(`p`.`category_id` = `c`.`category_id`)) 
left join `sale_items` `si` on(`p`.`product_id` = `si`.`product_id`)) 
group by `p`.`product_id`,`p`.`product_name`,`c`.`category_name` 
order by sum(`si`.`quantity`) desc");

// Check invoice_number unique constraint
$index_check = $conn->query("SHOW INDEX FROM sales WHERE Key_name = 'invoice_number'");
if ($index_check->num_rows == 0) {
    $conn->query("ALTER TABLE sales ADD UNIQUE KEY `invoice_number` (`invoice_number`)");
}

echo "<hr>";
if ($errors == 0) {
    echo "<h3 style='color:green'>✅ Database fixed! $fixes changes made.</h3>";
    echo "<p><strong><a href='/inventory_system/modules/dashboard/dashboard.php' style='color:green;font-size:16px'>→ Click here to go to Dashboard</a></strong></p>";
} else {
    echo "<h3 style='color:orange'>⚠️ Done with $errors errors. Try again or re-import the SQL file.</h3>";
}
echo "<p style='color:gray;font-size:12px'>You can delete this file after fixing: fix_database.php</p>";
?>
