<?php
$role = $_SESSION['role_name'] ?? '';
// Fetch company name from settings
$comp_setting = getSingleRow("SELECT setting_value FROM settings WHERE setting_key='company_name'");
$company_name = $comp_setting['setting_value'] ?? 'Inventory Management System';
$header_low_stock_alerts = $_SESSION['low_stock_alerts'] ?? getLowStockProducts(10);
if (!is_array($header_low_stock_alerts)) {
    $header_low_stock_alerts = [];
}
$header_low_stock_count = count($header_low_stock_alerts);
$_SESSION['low_stock_alerts'] = $header_low_stock_alerts;
?>
<style>
.company-marquee {
    flex: 1;
    margin: 0 30px;
    overflow: hidden;
    white-space: nowrap;
    position: relative;
    background: #f8fafc;
    border: 1.5px solid var(--border);
    border-radius: 20px;
    padding: 6px 20px;
}
.marquee-content {
    display: inline-block;
    padding-left: 100%;
    animation: marquee 15s linear infinite;
    font-weight: 700;
    color: var(--primary);
    font-size: 14px;
}
@keyframes marquee {
    0%   { transform: translate(0, 0); }
    100% { transform: translate(-100%, 0); }
}
</style>
<nav class="top-navbar">
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    
    <!-- Sliding Company Name Marquee -->
    <div class="company-marquee">
        <div class="marquee-content"><?php echo htmlspecialchars($company_name); ?></div>
    </div>
    
    <div class="navbar-right">
        <div class="dropdown header-alert-wrapper">
            <button type="button" class="header-alert-btn <?php echo $header_low_stock_count ? 'has-alert' : ''; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <?php if ($header_low_stock_count): ?>
                    <span class="header-alert-badge"><?php echo $header_low_stock_count; ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end low-stock-dropdown">
                <?php if ($header_low_stock_count): ?>
                    <div class="dropdown-header low-stock-header"><i class="fas fa-exclamation-triangle me-2"></i>Low stock alert</div>
                    <?php foreach ($header_low_stock_alerts as $alert): ?>
                        <div class="dropdown-item-text">
                            <div class="low-stock-name"><?php echo htmlspecialchars($alert['product_name']); ?></div>
                            <small>Qty <?php echo (int)$alert['quantity']; ?> / Min <?php echo (int)$alert['min_quantity']; ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="dropdown-item-text text-muted">No low stock items.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="user-info">
            <div class="user-avatar" style="overflow:hidden; display:flex; align-items:center; justify-content:center;">
                <?php if(!empty($_SESSION['profile_image'])): ?>
                    <img src="/inventory_system/assets/uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <?php echo strtoupper(substr($_SESSION['full_name']??'U',0,1)); ?>
                <?php endif; ?>
            </div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']??''); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($role); ?></div>
            </div>
        </div>
        <a href="/inventory_system/modules/auth/logout.php" class="btn btn-danger btn-sm ms-3" style="background-color:#dc0c0c; color:white"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>
