<?php
$page_title = "Dashboard";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
$role = $_SESSION['role_name'] ?? '';

$is_admin_or_manager = ($role === 'Admin' || $role === 'Manager');
if ($is_admin_or_manager) {
    require_once('../../config/ai_engine.php');
    $ai = new AIEngine($conn);
    $ai_forecast = $ai->generateSalesForecast(30);
    $ai_restock = $ai->getRestockAndStockoutPredictions();
    $ai_dead = $ai->getDeadStockAnalysis(30);
    $ai_pl = $ai->getProfitLossAnalysis(30);
}

// Safe stats
$total_products  = getCount('products');
$total_customers = getCount('customers');
$total_suppliers = getCount('suppliers');
$total_sales     = getCount('sales');

// Safe queries - check if column exists first
$col_check = $conn->query("SHOW COLUMNS FROM sales LIKE 'total_amount'");
$has_total = $col_check && $col_check->num_rows > 0;

$ret_col_check = $conn->query("SHOW COLUMNS FROM returns LIKE 'price'");
$has_ret_price = $ret_col_check && $ret_col_check->num_rows > 0;

if ($has_total) {
    $r = $conn->query("SELECT COALESCE(SUM(total_amount),0) as t FROM sales WHERE DATE(created_at)=CURDATE()");
    $today_gross = $r ? floatval($r->fetch_assoc()['t']) : 0;
    $today_returns = 0;
    if ($has_ret_price) {
        $ret_r = $conn->query("SELECT COALESCE(SUM(quantity * price),0) as t FROM returns WHERE DATE(created_at)=CURDATE()");
        $today_returns = $ret_r ? floatval($ret_r->fetch_assoc()['t']) : 0;
    }
    $today_sales = $today_gross - $today_returns;

    $r2 = $conn->query("SELECT COALESCE(SUM(total_amount),0) as t FROM sales WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
    $month_gross = $r2 ? floatval($r2->fetch_assoc()['t']) : 0;
    $month_returns = 0;
    if ($has_ret_price) {
        $ret_r2 = $conn->query("SELECT COALESCE(SUM(quantity * price),0) as t FROM returns WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
        $month_returns = $ret_r2 ? floatval($ret_r2->fetch_assoc()['t']) : 0;
    }
    $month_sales = $month_gross - $month_returns;

    $sales_by_month = getAllRows("SELECT MONTH(created_at) as m_num, MONTHNAME(created_at) as month, SUM(total_amount) as total FROM sales WHERE YEAR(created_at)=YEAR(CURDATE()) GROUP BY MONTH(created_at)");
    $returns_by_month = $has_ret_price ? getAllRows("SELECT MONTH(created_at) as m_num, SUM(quantity * price) as total FROM returns WHERE YEAR(created_at)=YEAR(CURDATE()) GROUP BY MONTH(created_at)") : [];
    
    $net_monthly = [];
    foreach ($sales_by_month as $sm) {
        $net_monthly[$sm['m_num']] = [
            'month' => $sm['month'],
            'total' => floatval($sm['total'])
        ];
    }
    foreach ($returns_by_month as $rm) {
        if (isset($net_monthly[$rm['m_num']])) {
            $net_monthly[$rm['m_num']]['total'] -= floatval($rm['total']);
        } else {
            $net_monthly[$rm['m_num']] = [
                'month' => date('F', mktime(0, 0, 0, $rm['m_num'], 10)),
                'total' => -floatval($rm['total'])
            ];
        }
    }
    ksort($net_monthly);
    $monthly_data = array_values($net_monthly);

    $recent_sales = getAllRows("SELECT * FROM sales ORDER BY created_at DESC LIMIT 8");
} else {
    $today_sales = 0; $month_sales = 0; $monthly_data = []; $recent_sales = [];
}

$low_stock = getAllRows("SELECT product_name, quantity, min_quantity FROM products WHERE quantity <= min_quantity AND status='active' ORDER BY quantity ASC LIMIT 8");
$page_title="Dashboard";
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
    <?php if(!$has_total): ?>
    <div class="alert alert-warning">
        <strong>⚠️ Database needs updating!</strong>
        <a href="/inventory_system/fix_database.php" class="btn btn-warning btn-sm ms-2">Click here to fix database</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($low_stock)): ?>
    <div class="modal fade" id="lowStockPopup" tabindex="-1" aria-labelledby="lowStockPopupLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning-subtle border-warning">
                    <h5 class="modal-title text-warning" id="lowStockPopupLabel"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php foreach ($low_stock as $item): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <small class="text-muted">Minimum: <?php echo (int)$item['min_quantity']; ?></small>
                            </div>
                            <span class="badge bg-danger rounded-pill"><?php echo (int)$item['quantity']; ?> left</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Dismiss</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alertModal = document.getElementById('lowStockPopup');
            if (alertModal && typeof bootstrap !== 'undefined') {
                new bootstrap.Modal(alertModal).show();
            }
        });
    </script>
    <?php endif; ?>

    <div class="page-header">
        <div><h2><i class="fas fa-home"></i> Dashboard</h2><p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']??''); ?>!</p></div>
        <span class="badge bg-primary"><?php echo date('d M Y'); ?></span>
    </div>

    <?php if ($is_admin_or_manager): ?>
    <!-- AI Predictive Intelligence Spotlight -->
    <div class="card mb-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #4f46e5 100%); color: white; border-radius: 12px;">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="background: rgba(255,255,255,0.15); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="fas fa-brain text-warning"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background: rgba(255,255,255,0.2); font-size: 10px; font-weight: 700;">AI INTELLIGENCE ENGINE</span>
                            <span class="badge bg-success" style="font-size: 10px;">LIVE PREDICTIVE INFERENCE</span>
                        </div>
                        <h5 class="fw-bold mb-0 mt-1">Predictive Inventory Forecast & Risk Analytics</h5>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/inventory_system/modules/ai_analytics/index.php" class="btn btn-warning btn-sm fw-bold">
                        <i class="fas fa-chart-line me-1"></i> Open AI Analytics Hub
                    </a>
                </div>
            </div>
            <div class="row g-2 mt-2 pt-2 border-top border-white border-opacity-10 text-center">
                <div class="col-6 col-md-3">
                    <div class="text-white-50 small">30-Day Sales Forecast</div>
                    <div class="fw-bold fs-6">PKR <?php echo number_format($ai_forecast['projected_total'], 0); ?></div>
                    <small class="text-warning"><?php echo ($ai_forecast['growth_rate_pct']>=0?'+':'').$ai_forecast['growth_rate_pct']; ?>% projected</small>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-white-50 small">Expected Profit Margin</div>
                    <div class="fw-bold fs-6"><?php echo $ai_pl['net_margin_pct']; ?>% Margin</div>
                    <small class="text-info"><?php echo $ai_pl['health_grade']; ?></small>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-white-50 small">Imminent Stockout Risk</div>
                    <div class="fw-bold fs-6 <?php echo $ai_restock['critical_count']>0?'text-danger':'text-white'; ?>">
                        <?php echo $ai_restock['critical_count']; ?> SKUs Critical
                    </div>
                    <a href="/inventory_system/modules/ai_analytics/index.php?tab=restock" class="text-white text-decoration-underline small">View Stockout Dates</a>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-white-50 small">Dead Stock Capital</div>
                    <div class="fw-bold fs-6">PKR <?php echo number_format($ai_dead['total_locked_capital'], 0); ?></div>
                    <a href="/inventory_system/modules/ai_analytics/index.php?tab=dead_stock" class="text-warning text-decoration-underline small">Halt Purchases</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats Row 1 -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon blue"><i class="fas fa-box"></i></div><div class="stat-info"><div class="stat-value"><?php echo $total_products; ?></div><div class="stat-label">Products</div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon green"><i class="fas fa-receipt"></i></div><div class="stat-info"><div class="stat-value"><?php echo $total_sales; ?></div><div class="stat-label">Sales</div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon orange"><i class="fas fa-users"></i></div><div class="stat-info"><div class="stat-value"><?php echo $total_customers; ?></div><div class="stat-label">Customers</div></div></div></div>
        <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-icon purple"><i class="fas fa-truck"></i></div><div class="stat-info"><div class="stat-value"><?php echo $total_suppliers; ?></div><div class="stat-label">Suppliers</div></div></div></div>
    </div>

    <!-- Stats Row 2 -->
    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="stat-card"><div class="stat-icon teal"><i class="fas fa-calendar-day"></i></div><div class="stat-info"><div class="stat-value">PKR <?php echo number_format($today_sales,0); ?></div><div class="stat-label">Today's Sales</div></div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-icon red"><i class="fas fa-chart-line"></i></div><div class="stat-info"><div class="stat-value">PKR <?php echo number_format($month_sales,0); ?></div><div class="stat-label">This Month</div></div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-icon orange"><i class="fas fa-exclamation-triangle"></i></div><div class="stat-info"><div class="stat-value"><?php echo count($low_stock); ?></div><div class="stat-label">Low Stock Alerts</div></div></div></div>
    </div>

    <!-- Chart + Low Stock -->
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-chart-bar"></i> Monthly Sales <?php echo date('Y'); ?></div>
                <div class="card-body"><canvas id="salesChart" height="110"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header text-danger"><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Product</th><th>Qty</th><th>Min</th></tr></thead>
                        <tbody>
                        <?php if(empty($low_stock)): ?>
                            <tr><td colspan="3" class="text-center py-3 text-muted"><i class="fas fa-check text-success"></i> All stock OK</td></tr>
                        <?php else: foreach($low_stock as $item): ?>
                            <tr>
                                <td style="font-size:12px"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><span class="badge bg-danger"><?php echo $item['quantity']; ?></span></td>
                                <td><?php echo $item['min_quantity']; ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header"><i class="fas fa-clock"></i> Recent Sales</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Invoice</th><th>Customer</th><th>Amount</th><th>Method</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php if(empty($recent_sales)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No sales yet</td></tr>
                    <?php else: foreach($recent_sales as $s): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($s['invoice_number']??'--'); ?></strong></td>
                            <td><?php echo htmlspecialchars($s['customer_name']??'Walk-in'); ?></td>
                            <td>PKR <?php echo number_format($s['total_amount']??0, 0); ?></td>
                            <td><span class="badge bg-success"><?php echo $s['payment_method']??'Cash'; ?></span></td>
                            <td><?php echo date('d M Y', strtotime($s['created_at'])); ?></td>
                            <td><a href="/inventory_system/modules/sales/view_invoice.php?id=<?php echo $s['sale_id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


<script>
var months = <?php echo json_encode(array_column($monthly_data,'month')); ?>;
var totals  = <?php echo json_encode(array_map('floatval', array_column($monthly_data,'total'))); ?>;
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: months.length ? months : ['No Data'],
        datasets: [{
            label: 'Sales PKR',
            data: totals.length ? totals : [0],
            backgroundColor: 'rgba(59,130,246,0.7)',
            borderColor: '#1e3a8a',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
