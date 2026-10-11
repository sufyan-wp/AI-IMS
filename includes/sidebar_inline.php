<?php
$cp = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role_name'] ?? '';
?>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header"><i class="fas fa-boxes me-2"></i><span>IMS</span></div>
    <ul class="sidebar-menu">
        <li><a href="/inventory_system/modules/dashboard/dashboard.php" class="<?php echo $cp==='dashboard.php'?'active':''; ?>"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <?php if($role==='Admin'||$role==='Manager'): ?>
        <li>
            <a href="/inventory_system/modules/ai_analytics/index.php" class="<?php echo strpos($_SERVER['REQUEST_URI'],'ai_analytics')!==false?'active':''; ?>">
                <i class="fas fa-brain text-info"></i><span>AI Analytics</span>
                <span class="badge ms-auto" style="font-size:9px; background:linear-gradient(135deg, #10b981, #06b6d4); color:#fff; font-weight:700;">AI PRO</span>
            </a>
        </li>
        <?php endif; ?>
        <li><a href="/inventory_system/modules/dashboard/profile.php" class="<?php echo $cp==='profile.php'?'active':''; ?>"><i class="fas fa-user-circle"></i><span>My Profile</span></a></li>
        <?php if($role==='Admin'||$role==='Manager'): ?>
        <li><a href="/inventory_system/modules/inventory/manage_products.php" class="<?php echo strpos($cp,'product')!==false?'active':''; ?>"><i class="fas fa-box"></i><span>Products</span></a></li>
        <li><a href="/inventory_system/modules/inventory/categories.php" class="<?php echo $cp==='categories.php'?'active':''; ?>"><i class="fas fa-list"></i><span>Categories</span></a></li>
        <?php else: ?>
        <li><a href="/inventory_system/modules/inventory/manage_products.php" class="<?php echo strpos($cp,'product')!==false?'active':''; ?>"><i class="fas fa-search"></i><span>Search Products</span></a></li>
        <?php endif; ?>
        <?php if($role==='Admin'): ?>
        <li><a href="/inventory_system/modules/stock/stock_in.php" class="<?php echo $cp==='stock_in.php'?'active':''; ?>"><i class="fas fa-arrow-circle-down"></i><span>Stock In</span></a></li>
        <li><a href="/inventory_system/modules/stock/stock_out.php" class="<?php echo $cp==='stock_out.php'?'active':''; ?>"><i class="fas fa-arrow-circle-up"></i><span>Stock Out</span></a></li>
        <?php endif; ?>
        <?php if($role==='Admin'||$role==='Manager'): ?>
        <li><a href="/inventory_system/modules/stock/stock_history.php" class="<?php echo $cp==='stock_history.php'?'active':''; ?>"><i class="fas fa-history"></i><span>Stock History</span></a></li>
        <?php endif; ?>
        <?php if($role==='Salesman'): ?>
        <li><a href="/inventory_system/modules/sales/create_invoice.php" class="<?php echo $cp==='create_invoice.php'?'active':''; ?>"><i class="fas fa-file-invoice-dollar"></i><span>Create Invoice</span></a></li>
        <?php endif; ?>
        <li><a href="/inventory_system/modules/sales/manage_invoices.php" class="<?php echo $cp==='manage_invoices.php'?'active':''; ?>"><i class="fas fa-receipt"></i><span>Invoices</span></a></li>
        <?php if($role==='Salesman'): ?>
        <li><a href="/inventory_system/modules/returns/manage_returns.php" class="<?php echo $cp==='manage_returns.php'?'active':''; ?>"><i class="fas fa-undo"></i><span>Returns</span></a></li>
        <?php endif; ?>
        <li><a href="/inventory_system/modules/customers/manage_customers.php" class="<?php echo strpos($cp,'customer')!==false?'active':''; ?>"><i class="fas fa-users"></i><span>Customers</span></a></li>
        <?php if($role==='Admin'||$role==='Manager'): ?>
        <li><a href="/inventory_system/modules/suppliers/manage_suppliers.php" class="<?php echo strpos($cp,'supplier')!==false?'active':''; ?>"><i class="fas fa-truck"></i><span>Suppliers</span></a></li>
        <?php endif; ?>
        <li><a href="/inventory_system/modules/reports/sales_report.php" class="<?php echo strpos($cp,'report')!==false?'active':''; ?>"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
        <?php if($role==='Admin'||$role==='Manager'): ?>
        <li><a href="/inventory_system/modules/dashboard/users.php" class="<?php echo $cp==='users.php'?'active':''; ?>"><i class="fas fa-user-cog"></i><span>Users</span></a></li>
        <?php endif; ?>
        <?php if($role==='Admin'): ?>
        <li><a href="/inventory_system/modules/backup/backup.php" class="<?php echo $cp==='backup.php'?'active':''; ?>"><i class="fas fa-database"></i><span>Backup</span></a></li>
        <li><a href="/inventory_system/modules/dashboard/activity_logs.php" class="<?php echo $cp==='activity_logs.php'?'active':''; ?>"><i class="fas fa-clipboard-list"></i><span>Activity Logs</span></a></li>
        <li><a href="/inventory_system/modules/dashboard/settings.php" class="<?php echo $cp==='settings.php'?'active':''; ?>"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        <?php endif; ?>
    </ul>
</nav>
