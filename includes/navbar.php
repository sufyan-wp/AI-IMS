<nav class="top-navbar">
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="navbar-right">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)); ?></div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($_SESSION['role_name'] ?? ''); ?></div>
            </div>
        </div>
        <a href="/inventory_system/modules/auth/logout.php" class="btn btn-danger btn-sm ms-3">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>
