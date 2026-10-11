<?php
$page_title="Sales Report";
require_once('../../config/database.php');
require_once('../../config/functions.php');
checkSession();
$date_from=sanitize($_GET['date_from']??date('Y-m-01'));
$date_to=sanitize($_GET['date_to']??date('Y-m-d'));
$w="DATE(created_at) BETWEEN '$date_from' AND '$date_to'";
$summary=$conn->query("SELECT COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as total, COALESCE(SUM(tax_amount),0) as tax, COALESCE(SUM(discount),0) as disc FROM sales WHERE $w")->fetch_assoc();
$ret_summary=$conn->query("SELECT COALESCE(SUM(quantity * price),0) as total FROM returns WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'")->fetch_assoc();
$net_revenue = floatval($summary['total']) - floatval($ret_summary['total']);

$by_method=getAllRows("SELECT payment_method, COUNT(*) as cnt, SUM(total_amount) as total FROM sales WHERE $w GROUP BY payment_method");
$top_products=getAllRows("SELECT p.product_name, SUM(si.quantity) as qty, SUM(si.subtotal) as total FROM sale_items si JOIN products p ON si.product_id=p.product_id JOIN sales s ON si.sale_id=s.sale_id WHERE DATE(s.created_at) BETWEEN '$date_from' AND '$date_to' GROUP BY si.product_id ORDER BY total DESC LIMIT 10");

$daily_sales=getAllRows("SELECT DATE(created_at) as day, COUNT(*) as cnt, SUM(total_amount) as total FROM sales WHERE $w GROUP BY DATE(created_at)");
$daily_returns=getAllRows("SELECT DATE(created_at) as day, SUM(quantity * price) as total FROM returns WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to' GROUP BY DATE(created_at)");

$daily_net = [];
foreach ($daily_sales as $ds) {
    $daily_net[$ds['day']] = [
        'day' => $ds['day'],
        'cnt' => intval($ds['cnt']),
        'total' => floatval($ds['total'])
    ];
}
foreach ($daily_returns as $dr) {
    if (isset($daily_net[$dr['day']])) {
        $daily_net[$dr['day']]['total'] -= floatval($dr['total']);
    } else {
        $daily_net[$dr['day']] = [
            'day' => $dr['day'],
            'cnt' => 0,
            'total' => -floatval($dr['total'])
        ];
    }
}
krsort($daily_net);
$daily = array_values($daily_net);

$returned_items=getAllRows("SELECT r.*, p.product_name, u.full_name FROM returns r JOIN products p ON r.product_id=p.product_id JOIN users u ON r.user_id=u.user_id WHERE DATE(r.created_at) BETWEEN '$date_from' AND '$date_to' ORDER BY r.created_at DESC");
include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>
<div class="page-header"><div><h2><i class="fas fa-chart-bar"></i> Sales Report</h2></div>
<button onclick="window.print()" class="btn btn-secondary no-print"><i class="fas fa-print"></i> Print</button></div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="row g-2 align-items-end">
<div class="col-md-3"><label class="form-label mb-1">From</label><input type="date" name="date_from" class="form-control" value="<?php echo $date_from;?>"></div>
<div class="col-md-3"><label class="form-label mb-1">To</label><input type="date" name="date_to" class="form-control" value="<?php echo $date_to;?>"></div>
<div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button></div>
<div class="col-md-2"><a href="sales_report.php" class="btn btn-secondary w-100">Reset</a></div>
</form></div></div>
<div class="row g-3 mb-3">
<div class="col-6 col-md-4 col-lg"><div class="stat-card"><div class="stat-icon blue"><i class="fas fa-receipt"></i></div><div class="stat-info"><div class="stat-value"><?php echo $summary['cnt'];?></div><div class="stat-label">Total Invoices</div></div></div></div>
<div class="col-6 col-md-4 col-lg"><div class="stat-card"><div class="stat-icon green"><i class="fas fa-money-bill"></i></div><div class="stat-info"><div class="stat-value">PKR <?php echo number_format($net_revenue,0);?></div><div class="stat-label">Total Revenue</div></div></div></div>
<div class="col-6 col-md-4 col-lg"><div class="stat-card"><div class="stat-icon red"><i class="fas fa-undo"></i></div><div class="stat-info"><div class="stat-value">PKR <?php echo number_format($ret_summary['total'],0);?></div><div class="stat-label">Returned Amount</div></div></div></div>
<div class="col-6 col-md-4 col-lg"><div class="stat-card"><div class="stat-icon orange"><i class="fas fa-percent"></i></div><div class="stat-info"><div class="stat-value">PKR <?php echo number_format($summary['tax'],0);?></div><div class="stat-label">Tax Collected</div></div></div></div>
<div class="col-6 col-md-4 col-lg"><div class="stat-card"><div class="stat-icon red"><i class="fas fa-tag"></i></div><div class="stat-info"><div class="stat-value">PKR <?php echo number_format($summary['disc'],0);?></div><div class="stat-label">Discounts Given</div></div></div></div>
</div>
<div class="row g-3 mb-3">
<div class="col-md-5"><div class="card"><div class="card-header">Payment Methods</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead class="table-light"><tr><th>Method</th><th>Count</th><th>Total</th></tr></thead><tbody>
<?php foreach($by_method as $m):?><tr><td><span class="badge bg-success"><?php echo $m['payment_method'];?></span></td><td><?php echo $m['cnt'];?></td><td>PKR <?php echo number_format($m['total'],0);?></td></tr><?php endforeach;?>
</tbody></table></div></div></div>
<div class="col-md-7"><div class="card"><div class="card-header">Top Products</div><div class="card-body p-0">
<table class="table table-sm mb-0"><thead class="table-light"><tr><th>#</th><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead><tbody>
<?php foreach($top_products as $i=>$p):?><tr><td><?php echo $i+1;?></td><td><?php echo htmlspecialchars($p['product_name']);?></td><td><?php echo $p['qty'];?></td><td>PKR <?php echo number_format($p['total'],0);?></td></tr><?php endforeach;?>
</tbody></table></div></div></div>
</div>
<div class="card"><div class="card-header">Daily Breakdown</div><div class="card-body p-0">
<table class="table table-hover mb-0"><thead class="table-light"><tr><th>Date</th><th>Invoices</th><th>Revenue</th></tr></thead><tbody>
<?php if(empty($daily)):?><tr><td colspan="3" class="text-center py-4 text-muted">No data for this period</td></tr>
<?php else: foreach($daily as $d):?><tr><td><?php echo date('d M Y',strtotime($d['day']));?></td><td><span class="badge bg-primary"><?php echo $d['cnt'];?></span></td><td>PKR <?php echo number_format($d['total'],0);?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div>
<div class="card mt-3">
    <div class="card-header"><i class="fas fa-undo"></i> Returned Invoices / Items</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date/Time</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price (Unit)</th>
                        <th>Total Refund</th>
                        <th>Reason</th>
                        <th>Processed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($returned_items)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No returns for this period</td></tr>
                    <?php else: foreach($returned_items as $ret): ?>
                        <tr>
                            <td><?php echo date('d M Y h:i A', strtotime($ret['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($ret['product_name']); ?></td>
                            <td><span class="badge bg-danger"><?php echo $ret['quantity']; ?></span></td>
                            <td>PKR <?php echo number_format($ret['price'], 2); ?></td>
                            <td><strong>PKR <?php echo number_format($ret['price'] * $ret['quantity'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($ret['reason'] ?? '--'); ?></td>
                            <td><?php echo htmlspecialchars($ret['full_name']); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>@media print{.sidebar,.top-navbar,.no-print{display:none!important;} .main-content{margin-left:0!important;}}</style>
<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php';?>
