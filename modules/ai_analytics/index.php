<?php
$page_title = "AI Intelligence & Predictive Analytics";
require_once('../../config/database.php');
require_once('../../config/functions.php');
require_once('../../config/ai_engine.php');
checkSession();
if (!hasRole(['Admin', 'Manager'])) {
    header("Location: /inventory_system/modules/dashboard/dashboard.php");
    exit();
}

$role = $_SESSION['role_name'] ?? '';
$ai = new AIEngine($conn);

// Handle AJAX actions (Seeding / Resetting / AI Assistant Queries)
if (isset($_GET['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax_action'];

    if ($action === 'seed_demo') {
        if (!hasRole(['Admin', 'Manager'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        $res = $ai->seedFYPDemoDataset();
        echo json_encode($res);
        exit();
    }

    if ($action === 'reset_demo') {
        if (!hasRole(['Admin', 'Manager'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        $res = $ai->resetFYPDemoData();
        echo json_encode($res);
        exit();
    }

    if ($action === 'ai_ask') {
        $q = strtolower(trim($_GET['q'] ?? ''));
        $restock = $ai->getRestockAndStockoutPredictions();
        $dead = $ai->getDeadStockAnalysis();
        $forecast = $ai->generateSalesForecast(30);
        $pl = $ai->getProfitLossAnalysis(30);
        $velocity = $ai->getProductVelocityAndABC(60);

        $answer = "I analyzed your real-time inventory and sales database:";

        if (strpos($q, 'stockout') !== false || strpos($q, 'restock') !== false || strpos($q, 'run out') !== false) {
            $crit = array_filter($restock['predictions'], fn($x) => in_array($x['urgency'], ['CRITICAL', 'OUT_OF_STOCK']));
            if (!empty($crit)) {
                $topCrit = array_slice($crit, 0, 3);
                $lines = [];
                foreach ($topCrit as $c) {
                    $lines[] = "• <strong>{$c['product_name']}</strong>: {$c['current_stock']} units left (Burn rate: {$c['daily_burn_rate']} /day) → Expected stockout: <strong>{$c['stockout_date']}</strong>. Recommended Reorder: <strong>{$c['recommended_reorder_qty']} units</strong>.";
                }
                $answer = "🚨 <strong>Imminent Stockout Threat Detected:</strong><br>" . implode('<br>', $lines);
            } else {
                $answer = "🟢 Stock levels are currently healthy! No urgent stockouts detected within the next 7-14 days.";
            }
        } elseif (strpos($q, 'profit') !== false || strpos($q, 'loss') !== false || strpos($q, 'revenue') !== false) {
            $answer = "💰 <strong>AI 30-Day Financial Projections:</strong><br>" .
                      "• Projected Gross Revenue: <strong>PKR " . number_format($pl['projected_revenue'], 0) . "</strong><br>" .
                      "• Estimated Cost of Goods (COGS): <strong>PKR " . number_format($pl['projected_cogs'], 0) . "</strong><br>" .
                      "• Expected Net Profit: <strong>PKR " . number_format($pl['projected_net_profit'], 0) . "</strong> (" . $pl['net_margin_pct'] . "% net margin)<br>" .
                      "• Portfolio Financial Grade: <strong>" . $pl['health_grade'] . "</strong>";
        } elseif (strpos($q, 'dead') !== false || strpos($q, 'slow') !== false || strpos($q, 'not sell') !== false || strpos($q, 'dont buy') !== false) {
            if ($dead['dead_items_count'] > 0) {
                $topDead = array_slice($dead['dead_items'], 0, 3);
                $itemsList = [];
                foreach ($topDead as $d) {
                    $itemsList[] = "• <strong>{$d['product_name']}</strong> ({$d['stock_qty']} units, PKR " . number_format($d['locked_capital'], 0) . " tied up)";
                }
                $answer = "🛑 <strong>Dead / Stagnant Stock Advisory:</strong><br>" .
                          "Found <strong>{$dead['dead_items_count']} products</strong> with zero recent sales, locking up <strong>PKR " . number_format($dead['total_locked_capital'], 0) . "</strong> in idle capital.<br>" .
                          "Top idle products:<br>" . implode('<br>', $itemsList) . "<br><br>" .
                          "<strong>AI Recommendation:</strong> Freeze purchase orders for these items immediately and consider a 15-20% bundle or liquidation promotion.";
            } else {
                $answer = "✨ Excellent inventory velocity! There is currently no severe dead stock in your active catalog.";
            }
        } elseif (strpos($q, 'best') !== false || strpos($q, 'top') !== false || strpos($q, 'sale more') !== false || strpos($q, 'velocity') !== false) {
            if ($velocity['top_seller']) {
                $top3 = array_slice($velocity['products'], 0, 3);
                $itemsList = [];
                foreach ($top3 as $t) {
                    $itemsList[] = "• <strong>{$t['product_name']}</strong>: {$t['total_qty_sold']} units sold (PKR " . number_format($t['total_revenue'], 0) . ", {$t['revenue_share_pct']}% share) — Class {$t['abc_class']}";
                }
                $answer = "🚀 <strong>Top Selling / High Velocity Products:</strong><br>" . implode('<br>', $itemsList) . "<br><br>" .
                          "These Class A products drive the bulk of your business revenue. Always prioritize supplier lead time and reorder buffer for them.";
            } else {
                $answer = "No sales history found yet. Record sales or generate FYP demo data to view ranking.";
            }
        } else {
            $answer = "🤖 <strong>AI Assistant Quick Overview:</strong><br>" .
                      "• 30-Day Projected Revenue: PKR " . number_format($forecast['projected_total'], 0) . " ({$forecast['growth_rate_pct']}% trajectory)<br>" .
                      "• Expected Net Profit: PKR " . number_format($pl['projected_net_profit'], 0) . " (" . $pl['net_margin_pct'] . "% margin)<br>" .
                      "• Stockout Critical Items: " . $restock['critical_count'] . " SKUs<br>" .
                      "• Dead Stock Tied Capital: PKR " . number_format($dead['total_locked_capital'], 0) . "<br><br>" .
                      "<em>Tip: You can ask about 'stockout', 'profit', 'dead stock', or 'top selling items'!</em>";
        }

        echo json_encode(['answer' => $answer]);
        exit();
    }
}

// Active Tab
$tab = $_GET['tab'] ?? 'forecast';

// Fetch AI calculations for page rendering
$forecastHorizon = intval($_GET['horizon'] ?? 30);
if (!in_array($forecastHorizon, [7, 14, 30, 90])) $forecastHorizon = 30;

$forecast = $ai->generateSalesForecast($forecastHorizon, 60);
$pl = $ai->getProfitLossAnalysis($forecastHorizon);
$velocity = $ai->getProductVelocityAndABC(60);
$restock = $ai->getRestockAndStockoutPredictions();
$dead = $ai->getDeadStockAnalysis(30);
$insights = $ai->generateExecutiveSummary();

include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_start.php';
?>

<style>
/* AI Modern Theme Enhancements */
.ai-header-card {
    background: linear-gradient(135deg, #3b82f6 0%, #3b82f6 50%, #3b82f6 100%);
    border-radius: 14px;
    color: white;
    padding: 24px 28px;
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.35);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.ai-header-card::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 180px; height: 180px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    pointer-events: none;
}
.ai-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #fff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.ai-nav-pills .nav-link {
    color: #475569;
    font-weight: 600;
    border-radius: 10px;
    padding: 10px 18px;
    margin-right: 8px;
    margin-bottom: 8px;
    background: #fff;
    border: 1px solid #e2e8f0;
    transition: all 0.25s ease;
}
.ai-nav-pills .nav-link:hover {
    color: #2563eb;
    border-color: #93c5fd;
    background: #f8fafc;
    transform: translateY(-1px);
}
.ai-nav-pills .nav-link.active {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}
.ai-stat-widget {
    background: #ffffff;
    border-radius: 12px;
    padding: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    transition: transform 0.2s, box-shadow 0.2s;
}
.ai-stat-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}
.stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.pulse-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #10b981;
    box-shadow: 0 0 0 rgba(16, 185, 129, 0.6);
    animation: pulse 1.8s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.table-ai th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 12.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}
.interactive-slider-card {
    background: #f8fafc;
    border: 1.5px dashed #cbd5e1;
    border-radius: 12px;
    padding: 20px;
}
.chip-btn {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    color: #334155;
    font-weight: 500;
    margin: 4px;
    display: inline-block;
}
.chip-btn:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
}
.executive-alert {
    border-left: 4px solid;
    border-radius: 8px;
    padding: 14px 18px;
    background: #ffffff;
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
</style>

<!-- Top AI Banner -->
<div class="ai-header-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="ai-badge"><i class="fas fa-microchip me-1"></i> AI Neural Engine v3.0</span>
                <span class="ai-badge bg-warning text-dark"><i class="fas fa-graduation-cap me-1"></i> FYP Showcase Edition</span>
                <span class="d-inline-flex align-items-center gap-1 text-white-50" style="font-size: 12px;">
                    <span class="pulse-indicator"></span> Real-time Live Inference
                </span>
            </div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-brain me-2"></i> Inventory Predictive Intelligence System</h1>
            <p class="mb-0 text-white-50" style="max-width: 720px; font-size: 14px;">
                Advanced machine learning & statistical forecasting algorithms (Holt's Exponential Smoothing, Linear Regression, ABC Pareto Analysis, Dynamic EOQ Reorder Point, and Capital Lockup Detector).
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-light btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#aiAssistantModal">
                <i class="fas fa-robot text-primary me-1"></i> Ask AI Copilot
            </button>
            <?php if (hasRole(['Admin', 'Manager'])): ?>
            <button class="btn btn-outline-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#fypDataModal">
                <i class="fas fa-database me-1"></i> FYP Demo Data Toolkit
            </button>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-outline-light btn-sm">
                <i class="fas fa-print me-1"></i> Print AI Report
            </button>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav ai-nav-pills mb-4" id="aiTabList">
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'forecast' ? 'active' : ''; ?>" href="?tab=forecast">
            <i class="fas fa-chart-line me-1"></i> Sales Demand Forecasting
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'profit_loss' ? 'active' : ''; ?>" href="?tab=profit_loss">
            <i class="fas fa-sack-dollar me-1"></i> Profit & Loss Simulator
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'velocity' ? 'active' : ''; ?>" href="?tab=velocity">
            <i class="fas fa-fire me-1"></i> Top Sellers & ABC Analysis
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'restock' ? 'active' : ''; ?>" href="?tab=restock">
            <i class="fas fa-boxes-packing me-1"></i> Restock & Stockout Predictor
            <?php if ($restock['critical_count'] > 0): ?>
                <span class="badge bg-danger ms-1"><?php echo $restock['critical_count']; ?></span>
            <?php endif; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'dead_stock' ? 'active' : ''; ?>" href="?tab=dead_stock">
            <i class="fas fa-hand-holding-dollar me-1"></i> Dead Stock & Purchasing Halt
            <?php if ($dead['dead_items_count'] > 0): ?>
                <span class="badge bg-warning text-dark ms-1"><?php echo $dead['dead_items_count']; ?></span>
            <?php endif; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'executive' ? 'active' : ''; ?>" href="?tab=executive">
            <i class="fas fa-file-waveform me-1"></i> AI Executive Briefing
        </a>
    </li>
</ul>

<!-- ========================================== -->
<!-- TAB 1: SALES & DEMAND FORECASTING           -->
<!-- ========================================== -->
<?php if ($tab === 'forecast'): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Forecast Horizon</div>
            <div class="fs-4 fw-bold text-dark mt-1">Next <?php echo $forecastHorizon; ?> Days</div>
            <span class="stat-pill bg-primary-subtle text-primary mt-1">
                <i class="fas fa-calendar"></i> Multi-horizon model
            </span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Projected Revenue</div>
            <div class="fs-4 fw-bold text-success mt-1">PKR <?php echo number_format($forecast['projected_total'], 0); ?></div>
            <span class="stat-pill <?php echo $forecast['growth_rate_pct'] >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> mt-1">
                <i class="fas <?php echo $forecast['growth_rate_pct'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'; ?>"></i> 
                <?php echo ($forecast['growth_rate_pct'] >= 0 ? '+' : '') . $forecast['growth_rate_pct']; ?>% Trajectory
            </span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Expected Daily Average</div>
            <div class="fs-4 fw-bold text-primary mt-1">PKR <?php echo number_format($forecast['avg_daily_projected'], 0); ?></div>
            <span class="stat-pill bg-light text-muted mt-1">
                <i class="fas fa-chart-simple"></i> Smoothed daily run
            </span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Algorithm Confidence (R²)</div>
            <div class="fs-4 fw-bold text-indigo mt-1"><?php echo $forecast['r_squared']; ?></div>
            <span class="stat-pill bg-info-subtle text-info mt-1">
                <i class="fas fa-circle-check"></i> <?php echo $forecast['model_accuracy_rating']; ?>
            </span>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-area text-primary me-2"></i> Sales Demand Trajectory & 95% Confidence Interval</h5>
            <small class="text-muted">Displays historical sales actuals blended with Holt's linear exponential smoothing forecast & upper/lower prediction bands.</small>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            <span class="text-muted small fw-semibold">Horizon:</span>
            <div class="btn-group btn-group-sm">
                <a href="?tab=forecast&horizon=7" class="btn btn-outline-primary <?php echo $forecastHorizon==7?'active':''; ?>">7D</a>
                <a href="?tab=forecast&horizon=14" class="btn btn-outline-primary <?php echo $forecastHorizon==14?'active':''; ?>">14D</a>
                <a href="?tab=forecast&horizon=30" class="btn btn-outline-primary <?php echo $forecastHorizon==30?'active':''; ?>">30D</a>
                <a href="?tab=forecast&horizon=90" class="btn btn-outline-primary <?php echo $forecastHorizon==90?'active':''; ?>">90D</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div style="height: 380px;">
            <canvas id="aiForecastChart"></canvas>
        </div>
    </div>
    <div class="card-footer bg-light py-2 px-3 small text-muted d-flex justify-content-between align-items-center">
        <span><i class="fas fa-info-circle text-info me-1"></i> The shaded envelope represents the <strong>95% Prediction Interval</strong>. The center dashed line represents AI expected revenue.</span>
        <span class="badge bg-secondary">Holt-Winters + OLS Regression</span>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold"><i class="fas fa-calculator text-primary me-2"></i> Algorithmic Forecasting Methodology</div>
            <div class="card-body">
                <p class="text-muted small">This system uses a hybrid machine-learning ensemble designed specifically for retail inventory demand:</p>
                <ul class="small mb-0 text-secondary">
                    <li class="mb-2"><strong>Level & Trend Tracking:</strong> Holt's double exponential smoothing calculates responsive demand levels ($\alpha = 0.35$) and momentum trajectory ($\beta = 0.15$).</li>
                    <li class="mb-2"><strong>Ordinary Least Squares (OLS) Regression:</strong> Fits a linear projection trend line ($y = mx + b$) over recent daily sales series to detect overarching growth/contraction.</li>
                    <li class="mb-2"><strong>Day-of-Week Seasonality Adjuster:</strong> Applies empirical weekend peak weighting (+15% weekend uplift) to prevent flatline predictions.</li>
                    <li><strong>Confidence Interval Envelope:</strong> Multiplies standard forecast error by $1.96 \cdot S_e \sqrt{1 + \frac{1}{N}}$ for 95% certainty boundaries.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold"><i class="fas fa-lightbulb text-warning me-2"></i> Practical Strategic Recommendations</div>
            <div class="card-body">
                <div class="p-3 bg-light rounded mb-3">
                    <div class="fw-semibold text-dark mb-1">
                        <?php if ($forecast['growth_rate_pct'] >= 0): ?>
                            🚀 Positive Demand Momentum (+<?php echo $forecast['growth_rate_pct']; ?>%)
                        <?php else: ?>
                            ⚠️ Demand Deceleration (<?php echo $forecast['growth_rate_pct']; ?>%)
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted">
                        <?php if ($forecast['growth_rate_pct'] >= 0): ?>
                            Sales run rates indicate surging customer activity. Ensure procurement buffers are expanded on top-velocity items to avoid supply deficits.
                        <?php else: ?>
                            Demand trajectory is moderating. Focus marketing on Class B items and refrain from speculative large batch replenishment.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold text-secondary">Expected Next 30D Cash Inflow:</span>
                    <span class="fw-bold text-success fs-6">PKR <?php echo number_format($forecast['projected_total'], 0); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- TAB 2: PROFIT & LOSS SCENARIO SIMULATOR     -->
<!-- ========================================== -->
<?php if ($tab === 'profit_loss'): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Projected Net Revenue</div>
            <div class="fs-4 fw-bold text-dark mt-1" id="dispRevenue">PKR <?php echo number_format($pl['projected_revenue'], 0); ?></div>
            <span class="stat-pill bg-light text-muted mt-1">Based on <?php echo $pl['forecast_days']; ?>-day model</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Estimated COGS (Cost)</div>
            <div class="fs-4 fw-bold text-secondary mt-1" id="dispCogs">PKR <?php echo number_format($pl['projected_cogs'], 0); ?></div>
            <span class="stat-pill bg-secondary-subtle text-secondary mt-1"><?php echo (100 - $pl['gross_margin_pct']); ?>% Cost Ratio</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Expected Net Profit</div>
            <div class="fs-4 fw-bold text-success mt-1" id="dispNetProfit">PKR <?php echo number_format($pl['projected_net_profit'], 0); ?></div>
            <span class="stat-pill bg-success-subtle text-success mt-1" id="dispMargin"><?php echo $pl['net_margin_pct']; ?>% Net Margin</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Financial Health Grade</div>
            <div class="fs-5 fw-bold text-primary mt-1"><?php echo $pl['health_grade']; ?></div>
            <span class="stat-pill bg-primary-subtle text-primary mt-1">Optimized profitability</span>
        </div>
    </div>
</div>

<!-- Interactive "What-If" AI Scenario Simulator -->
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0"><i class="fas fa-sliders me-2"></i> Interactive AI "What-If" Sensitivity Simulator</h5>
            <small class="text-white-50">Drag the control sliders below to test how price changes, customer demand surges, and supplier cost fluctuations affect expected profit in real time.</small>
        </div>
        <button type="button" class="btn btn-outline-light btn-sm" id="btnResetSim">
            <i class="fas fa-rotate-left me-1"></i> Reset Baseline
        </button>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="interactive-slider-card">
                    <label class="form-label fw-bold d-flex justify-content-between">
                        <span><i class="fas fa-people-group text-primary me-1"></i> Demand Volume Shift</span>
                        <span class="text-primary fw-bold" id="valDemand">0%</span>
                    </label>
                    <input type="range" class="form-range" id="sliderDemand" min="-40" max="60" step="5" value="0">
                    <div class="d-flex justify-content-between text-muted small">
                        <span>-40% (Recession)</span>
                        <span>0% Baseline</span>
                        <span>+60% (Surge)</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="interactive-slider-card">
                    <label class="form-label fw-bold d-flex justify-content-between">
                        <span><i class="fas fa-tags text-warning me-1"></i> Promotional Discount</span>
                        <span class="text-warning fw-bold" id="valDiscount">0%</span>
                    </label>
                    <input type="range" class="form-range" id="sliderDiscount" min="0" max="25" step="1" value="0">
                    <div class="d-flex justify-content-between text-muted small">
                        <span>0% (No Sale)</span>
                        <span>10% Promo</span>
                        <span>25% Deep Sale</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="interactive-slider-card">
                    <label class="form-label fw-bold d-flex justify-content-between">
                        <span><i class="fas fa-arrow-trend-up text-danger me-1"></i> Supplier Cost Inflation</span>
                        <span class="text-danger fw-bold" id="valCost">0%</span>
                    </label>
                    <input type="range" class="form-range" id="sliderCost" min="-10" max="25" step="2" value="0">
                    <div class="d-flex justify-content-between text-muted small">
                        <span>-10% (Discount)</span>
                        <span>0% Current</span>
                        <span>+25% Inflation</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 p-3 bg-light rounded d-flex flex-wrap justify-content-around align-items-center text-center">
            <div class="p-2">
                <div class="text-muted small">Simulated Gross Revenue</div>
                <div class="fs-4 fw-bold text-dark" id="simRev">PKR <?php echo number_format($pl['projected_revenue'], 0); ?></div>
            </div>
            <div class="p-2 border-start">
                <div class="text-muted small">Simulated Total Cost (COGS)</div>
                <div class="fs-4 fw-bold text-secondary" id="simCost">PKR <?php echo number_format($pl['projected_cogs'], 0); ?></div>
            </div>
            <div class="p-2 border-start">
                <div class="text-muted small">Simulated Net Profit</div>
                <div class="fs-4 fw-bold text-success" id="simProfit">PKR <?php echo number_format($pl['projected_net_profit'], 0); ?></div>
            </div>
            <div class="p-2 border-start">
                <div class="text-muted small">Simulated Net Margin</div>
                <div class="fs-4 fw-bold text-primary" id="simMargin"><?php echo $pl['net_margin_pct']; ?>%</div>
            </div>
        </div>
    </div>
</div>

<!-- Margin Risk Products -->
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-danger"><i class="fas fa-triangle-exclamation me-2"></i> Low-Margin / Loss-Exposure Watchlist</h5>
        <small class="text-muted">Products where selling price is too close to cost price or could cause negative margins during discount campaigns.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-ai align-middle mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Unit Margin</th>
                        <th>Margin %</th>
                        <th>Risk Assessment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pl['loss_risks'])): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-check-circle text-success me-1"></i> No high-risk loss products detected! All catalog items have healthy markup.</td></tr>
                    <?php else: foreach ($pl['loss_risks'] as $risk): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($risk['product_name']); ?></td>
                            <td>PKR <?php echo number_format($risk['cost_price'], 2); ?></td>
                            <td>PKR <?php echo number_format($risk['selling_price'], 2); ?></td>
                            <td class="<?php echo $risk['unit_margin'] <= 0 ? 'text-danger fw-bold' : 'text-dark'; ?>">
                                PKR <?php echo number_format($risk['unit_margin'], 2); ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $risk['margin_pct'] <= 5 ? 'bg-danger' : 'bg-warning text-dark'; ?>">
                                    <?php echo $risk['margin_pct']; ?>%
                                </span>
                            </td>
                            <td>
                                <span class="text-danger small fw-semibold">
                                    <i class="fas fa-triangle-exclamation me-1"></i> Recommend raising price by at least 10%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- TAB 3: PRODUCT VELOCITY & TOP SELLERS       -->
<!-- ========================================== -->
<?php if ($tab === 'velocity'): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Class A Products (80/20 Rule)</div>
            <div class="fs-4 fw-bold text-success mt-1"><?php echo $velocity['class_a_count']; ?> SKUs</div>
            <span class="stat-pill bg-success-subtle text-success mt-1">Generate ~80% of revenue</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Class B Products (Steady)</div>
            <div class="fs-4 fw-bold text-primary mt-1"><?php echo $velocity['class_b_count']; ?> SKUs</div>
            <span class="stat-pill bg-primary-subtle text-primary mt-1">Generate ~15% of revenue</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Class C Products (Long Tail)</div>
            <div class="fs-4 fw-bold text-warning mt-1"><?php echo $velocity['class_c_count']; ?> SKUs</div>
            <span class="stat-pill bg-warning-subtle text-warning mt-1">Generate ~5% of revenue</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Top Selling Star SKU</div>
            <div class="fs-5 fw-bold text-truncate text-dark mt-1" style="max-width: 100%;">
                <?php echo htmlspecialchars($velocity['top_seller']['product_name'] ?? 'N/A'); ?>
            </div>
            <span class="stat-pill bg-info-subtle text-info mt-1">
                <?php echo $velocity['top_seller']['revenue_share_pct'] ?? 0; ?>% Revenue Share
            </span>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-ranking-star text-primary me-2"></i> Product Sales Velocity Ranking & ABC Pareto Classification</h5>
                <small class="text-muted">Identifies which products are selling fastest (daily burn rate), monthly run rate, and cumulative revenue contribution.</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-hover table-ai align-middle mb-0">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                                <th>Burn Velocity</th>
                                <th>Share %</th>
                                <th>Pareto Tier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($velocity['products'] as $p): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($p['product_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($p['category_name'] ?? 'General'); ?> • <?php echo htmlspecialchars($p['brand'] ?? '--'); ?></small>
                                </td>
                                <td><span class="fw-bold"><?php echo (int)$p['total_qty_sold']; ?></span> units</td>
                                <td class="fw-semibold">PKR <?php echo number_format($p['total_revenue'], 0); ?></td>
                                <td>
                                    <div><strong><?php echo $p['velocity_daily']; ?></strong> /day</div>
                                    <small class="text-muted"><?php echo $p['velocity_monthly']; ?> /month</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: <?php echo min(100, $p['revenue_share_pct'] * 3); ?>%"></div>
                                        </div>
                                        <small class="fw-bold"><?php echo $p['revenue_share_pct']; ?>%</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $p['velocity_class']; ?>">
                                        Class <?php echo $p['abc_class']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="fas fa-chart-pie text-info me-2"></i> ABC Revenue Distribution
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <div style="height: 240px; width: 100%;">
                    <canvas id="abcDonutChart"></canvas>
                </div>
                <div class="mt-3 text-center small text-muted">
                    <p class="mb-1"><strong>Pareto 80/20 Principle:</strong></p>
                    <p class="mb-0">Class A items require tightest stock surveillance and zero-stockout priority. Class C items should be purchased strictly on-demand.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- TAB 4: RESTOCK & STOCKOUT PREDICTOR         -->
<!-- ========================================== -->
<?php if ($tab === 'restock'): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget border-danger">
            <div class="text-muted small fw-semibold">Critical Stockout Imminent</div>
            <div class="fs-4 fw-bold text-danger mt-1"><?php echo $restock['critical_count']; ?> Items</div>
            <span class="stat-pill bg-danger-subtle text-danger mt-1">Expected out in &le; 7 days</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget border-warning">
            <div class="text-muted small fw-semibold">Reorder Warning</div>
            <div class="fs-4 fw-bold text-warning mt-1"><?php echo $restock['warning_count']; ?> Items</div>
            <span class="stat-pill bg-warning-subtle text-warning mt-1">Reorder within 8-15 days</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget border-success">
            <div class="text-muted small fw-semibold">Healthy Stock Level</div>
            <div class="fs-4 fw-bold text-success mt-1"><?php echo $restock['healthy_count']; ?> Items</div>
            <span class="stat-pill bg-success-subtle text-success mt-1">Ample inventory buffer</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Lead Time Simulation</div>
            <div class="fs-4 fw-bold text-primary mt-1"><?php echo $restock['lead_time_days']; ?> Days</div>
            <span class="stat-pill bg-light text-muted mt-1">Supplier shipment window</span>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-boxes-packing text-primary me-2"></i> Intelligent Stockout Countdown & AI Recommended Reorder Quantities</h5>
            <small class="text-muted">Calculates exact projected run-out dates based on daily burn rate and recommends optimal economic order quantity (EOQ).</small>
        </div>
        <span class="badge bg-primary px-3 py-2">Sorted by Urgency Priority</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-ai align-middle mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Daily Burn Rate</th>
                        <th>Days Left</th>
                        <th>Projected Stockout Date</th>
                        <th>AI Recommended Order</th>
                        <th>Urgency</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($restock['predictions'] as $item): ?>
                    <tr class="<?php echo ($item['urgency'] === 'CRITICAL' || $item['urgency'] === 'OUT_OF_STOCK') ? 'table-danger-subtle' : ''; ?>">
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($item['category_name'] ?? ''); ?> • Min Threshold: <?php echo $item['min_quantity']; ?></small>
                        </td>
                        <td>
                            <span class="badge <?php echo $item['current_stock'] <= $item['min_quantity'] ? 'bg-danger' : 'bg-secondary'; ?> fs-6">
                                <?php echo $item['current_stock']; ?> units
                            </span>
                        </td>
                        <td>
                            <strong><?php echo $item['daily_burn_rate']; ?></strong> units/day
                        </td>
                        <td>
                            <?php if ($item['days_to_stockout'] >= 999): ?>
                                <span class="text-muted">Stable / Dormant</span>
                            <?php elseif ($item['days_to_stockout'] <= 0): ?>
                                <span class="badge bg-danger">0 Days (Depleted)</span>
                            <?php else: ?>
                                <strong class="<?php echo $item['days_to_stockout'] <= 7 ? 'text-danger' : ($item['days_to_stockout'] <= 15 ? 'text-warning' : 'text-success'); ?>">
                                    <?php echo $item['days_to_stockout']; ?> Days
                                </strong>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo $item['stockout_date']; ?></strong>
                        </td>
                        <td>
                            <?php if ($item['recommended_reorder_qty'] > 0): ?>
                                <span class="badge bg-primary fs-6">+<?php echo $item['recommended_reorder_qty']; ?> Units</span>
                                <div class="small text-muted">~PKR <?php echo number_format($item['reorder_cost_estimate'], 0); ?></div>
                            <?php else: ?>
                                <span class="text-muted small">No order needed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $item['urgency_badge']; ?>">
                                <?php echo $item['urgency_label']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (hasRole(['Admin', 'Manager']) && $item['recommended_reorder_qty'] > 0): ?>
                                <a href="/inventory_system/modules/stock/stock_in.php?product_id=<?php echo $item['product_id']; ?>&quantity=<?php echo $item['recommended_reorder_qty']; ?>" class="btn btn-sm btn-outline-success fw-bold">
                                    <i class="fas fa-arrow-circle-down me-1"></i> Quick Restock
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Adequate</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- TAB 5: DEAD STOCK & CAPITAL OPTIMIZATION    -->
<!-- ========================================== -->
<?php if ($tab === 'dead_stock'): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget border-warning">
            <div class="text-muted small fw-semibold">Total Capital Locked</div>
            <div class="fs-4 fw-bold text-danger mt-1">PKR <?php echo number_format($dead['total_locked_capital'], 0); ?></div>
            <span class="stat-pill bg-danger-subtle text-danger mt-1">Tied up in unsold stock</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Dormant Products Count</div>
            <div class="fs-4 fw-bold text-warning mt-1"><?php echo $dead['dead_items_count']; ?> SKUs</div>
            <span class="stat-pill bg-warning-subtle text-warning mt-1">0 sales in last <?php echo $dead['threshold_days']; ?> days</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Total Idle Units</div>
            <div class="fs-4 fw-bold text-secondary mt-1"><?php echo number_format($dead['total_locked_units']); ?> Units</div>
            <span class="stat-pill bg-light text-muted mt-1">Occupying warehouse space</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ai-stat-widget">
            <div class="text-muted small fw-semibold">Inventory Holding Cost</div>
            <div class="fs-4 fw-bold text-danger mt-1">PKR <?php echo number_format($dead['total_locked_capital'] * 0.015, 0); ?>/mo</div>
            <span class="stat-pill bg-light text-muted mt-1">~1.5% monthly carrying cost</span>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-hand-holding-dollar text-danger me-2"></i> Dormant Inventory & Purchasing Halt Recommendations</h5>
            <small class="text-muted">Items with zero or near-zero sales velocity. <strong>AI Directive:</strong> DO NOT create new purchase orders for these items.</small>
        </div>
        <span class="badge bg-danger px-3 py-2">Procurement Restraint Active</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-ai align-middle mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock In Hand</th>
                        <th>Cost Price</th>
                        <th>Locked Capital</th>
                        <th>Last Sold Date</th>
                        <th>AI Recommendation & Action Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dead['dead_items'])): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-check-circle text-success me-1"></i> Outstanding performance! No dormant or dead stock detected in the active window.</td></tr>
                    <?php else: foreach ($dead['dead_items'] as $d): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($d['product_name']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($d['category_name'] ?? ''); ?> • <?php echo htmlspecialchars($d['brand'] ?? ''); ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary fs-6"><?php echo $d['stock_qty']; ?> units</span>
                        </td>
                        <td>PKR <?php echo number_format($d['cost_price'], 2); ?></td>
                        <td>
                            <strong class="text-danger">PKR <?php echo number_format($d['locked_capital'], 0); ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?php echo $d['last_sold_date']; ?></span>
                            <div class="small text-muted"><?php echo $d['days_inactive']; ?> days dormant</div>
                        </td>
                        <td>
                            <div class="small fw-semibold text-<?php echo $d['badge']; ?> mb-1">
                                <?php echo htmlspecialchars($d['recommendation']); ?>
                            </div>
                            <span class="badge bg-danger-subtle text-danger border border-danger">
                                <i class="fas fa-ban me-1"></i> FREEZE REPURCHASE
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- TAB 6: AI EXECUTIVE BRIEFING                -->
<!-- ========================================== -->
<?php if ($tab === 'executive'): ?>
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-file-waveform text-primary me-2"></i> Executive AI Natural Language Intelligence Briefing</h5>
            <small class="text-muted">Generated dynamically by neural statistical synthesis of your live inventory data.</small>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            <i class="fas fa-print me-1"></i> Print Executive Summary
        </button>
    </div>
    <div class="card-body">
        <?php foreach ($insights as $insight): ?>
        <div class="executive-alert border-<?php echo $insight['color']; ?>">
            <div class="d-flex align-items-start gap-3">
                <div class="text-<?php echo $insight['color']; ?> fs-4 mt-1">
                    <i class="<?php echo $insight['icon']; ?>"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark"><?php echo $insight['title']; ?></h6>
                    <p class="mb-0 text-secondary" style="font-size: 13.5px; line-height: 1.6;">
                        <?php echo $insight['message']; ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- AI Assistant Modal Dialog -->
<div class="modal fade" id="aiAssistantModal" tabindex="-1" aria-labelledby="aiAssistantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="aiAssistantModalLabel">
                    <i class="fas fa-robot me-2"></i> Inventory AI Copilot Assistant
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Ask any question about expected sales, profit forecasts, stockouts, dead stock, or reordering recommendations:</p>
                
                <div class="mb-3">
                    <div class="fw-semibold small text-secondary mb-1">Quick Prompt Chips:</div>
                    <span class="chip-btn" onclick="askAiQuestion('Which products will stock out first?')">⚡ Which products will stock out first?</span>
                    <span class="chip-btn" onclick="askAiQuestion('What is our expected profit and revenue?')">💰 What is our expected profit & revenue?</span>
                    <span class="chip-btn" onclick="askAiQuestion('How much capital is locked in dead stock?')">🛑 How much is locked in dead stock?</span>
                    <span class="chip-btn" onclick="askAiQuestion('Which products are selling more?')">🚀 Which products are selling more?</span>
                    <span class="chip-btn" onclick="askAiQuestion('What items should we not buy?')">⛔ What items should we NOT buy?</span>
                </div>

                <div class="input-group mb-3">
                    <input type="text" id="aiQuestionInput" class="form-control" placeholder="Type your inventory intelligence question here...">
                    <button class="btn btn-primary" id="btnSubmitAiQuestion" type="button">
                        <i class="fas fa-paper-plane me-1"></i> Analyze
                    </button>
                </div>

                <div id="aiResponseContainer" class="p-3 bg-light rounded border d-none">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="spinner-border spinner-border-sm text-primary d-none" id="aiSpinner"></span>
                        <strong class="text-primary"><i class="fas fa-brain me-1"></i> AI Reasoning Output:</strong>
                    </div>
                    <div id="aiResponseText" class="text-dark small" style="line-height: 1.6;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- FYP Demo Data Modal -->
<div class="modal fade" id="fypDataModal" tabindex="-1" aria-labelledby="fypDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="fypDataModalLabel">
                    <i class="fas fa-database me-2"></i> FYP Examiner Showcase Toolkit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">
                    During your Final Year Project defense or demonstration, statistical AI models shine best when evaluated on a rich multi-month time series.
                </p>
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle me-1"></i> <strong>Simulation Dataset:</strong> Generates ~150 realistic orders over the past 90 days with weekend trends, high-velocity star items, low-stock triggers, and intentional dead stock items.
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-success fw-bold py-2" id="btnSeedFypData">
                        <i class="fas fa-wand-magic-sparkles me-1"></i> Generate 90-Day Rich Historical Dataset
                    </button>
                    <button class="btn btn-outline-danger py-2" id="btnResetFypData">
                        <i class="fas fa-trash-can me-1"></i> Reset Simulated Data (Keep Baseline)
                    </button>
                </div>
                <div id="fypActionMsg" class="mt-3 text-center small fw-semibold"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Forecast Chart Rendering
<?php if ($tab === 'forecast'): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('aiForecastChart');
    if (!ctx) return;

    const histDates = <?php echo json_encode($forecast['history_dates']); ?>;
    const histValues = <?php echo json_encode($forecast['history_values']); ?>;
    const foreDates = <?php echo json_encode($forecast['forecast_dates']); ?>;
    const foreValues = <?php echo json_encode($forecast['forecast_values']); ?>;
    const foreUpper = <?php echo json_encode($forecast['forecast_upper']); ?>;
    const foreLower = <?php echo json_encode($forecast['forecast_lower']); ?>;

    // Combine labels
    const allLabels = [...histDates, ...foreDates];

    // Align datasets
    const histDataFilled = [...histValues, ...new Array(foreDates.length).fill(null)];
    
    // Connect forecast line seamlessly to last historical point
    const lastHistVal = histValues.length ? histValues[histValues.length - 1] : 0;
    const padCount = histDates.length > 0 ? histDates.length - 1 : 0;
    const foreDataFilled = [...new Array(padCount).fill(null), lastHistVal, ...foreValues];
    const upperFilled = [...new Array(padCount).fill(null), lastHistVal, ...foreUpper];
    const lowerFilled = [...new Array(padCount).fill(null), lastHistVal, ...foreLower];

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Historical Actual Sales',
                    data: histDataFilled,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 2,
                    fill: false
                },
                {
                    label: 'AI Projected Forecast',
                    data: foreDataFilled,
                    borderColor: '#10b981',
                    borderDash: [5, 5],
                    borderWidth: 2.5,
                    tension: 0.3,
                    pointRadius: 3,
                    pointBackgroundColor: '#10b981',
                    fill: false
                },
                {
                    label: 'Upper 95% Confidence Band',
                    data: upperFilled,
                    borderColor: 'rgba(16, 185, 129, 0.25)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: '+1',
                    backgroundColor: 'rgba(16, 185, 129, 0.12)'
                },
                {
                    label: 'Lower 95% Confidence Band',
                    data: lowerFilled,
                    borderColor: 'rgba(16, 185, 129, 0.25)',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { boxWidth: 14, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.parsed.y === null) return '';
                            return ctx.dataset.label + ': PKR ' + Number(ctx.parsed.y).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => 'PKR ' + val.toLocaleString()
                    }
                },
                x: {
                    ticks: { maxTicksLimit: 14 }
                }
            }
        }
    });
});
<?php endif; ?>

// ABC Donut Chart
<?php if ($tab === 'velocity'): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('abcDonutChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Class A (Star Items)', 'Class B (Steady Movers)', 'Class C (Moderate/Slow)'],
            datasets: [{
                data: [<?php echo $velocity['class_a_count']; ?>, <?php echo $velocity['class_b_count']; ?>, <?php echo $velocity['class_c_count']; ?>],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
<?php endif; ?>

// What-If Sensitivity Simulator
<?php if ($tab === 'profit_loss'): ?>
document.addEventListener('DOMContentLoaded', function() {
    const baseRevenue = <?php echo $pl['projected_revenue']; ?>;
    const baseCogs = <?php echo $pl['projected_cogs']; ?>;

    const sDemand = document.getElementById('sliderDemand');
    const sDiscount = document.getElementById('sliderDiscount');
    const sCost = document.getElementById('sliderCost');

    const vDemand = document.getElementById('valDemand');
    const vDiscount = document.getElementById('valDiscount');
    const vCost = document.getElementById('valCost');

    const simRev = document.getElementById('simRev');
    const simCost = document.getElementById('simCost');
    const simProfit = document.getElementById('simProfit');
    const simMargin = document.getElementById('simMargin');

    function updateSimulation() {
        const dVal = parseFloat(sDemand.value);
        const discVal = parseFloat(sDiscount.value);
        const cVal = parseFloat(sCost.value);

        vDemand.textContent = (dVal >= 0 ? '+' : '') + dVal + '%';
        vDiscount.textContent = discVal + '%';
        vCost.textContent = (cVal >= 0 ? '+' : '') + cVal + '%';

        // Apply sensitivity multipliers
        const demandMultiplier = 1 + (dVal / 100);
        const discountMultiplier = 1 - (discVal / 100);
        const costMultiplier = 1 + (cVal / 100);

        const newRev = Math.max(0, baseRevenue * demandMultiplier * discountMultiplier);
        const newCost = Math.max(0, baseCogs * demandMultiplier * costMultiplier);
        const newProfit = newRev - newCost;
        const newMargin = newRev > 0 ? ((newProfit / newRev) * 100).toFixed(1) : 0;

        simRev.textContent = 'PKR ' + Math.round(newRev).toLocaleString();
        simCost.textContent = 'PKR ' + Math.round(newCost).toLocaleString();
        simProfit.textContent = 'PKR ' + Math.round(newProfit).toLocaleString();
        simMargin.textContent = newMargin + '%';

        if (newProfit < 0) {
            simProfit.className = 'fs-4 fw-bold text-danger';
        } else {
            simProfit.className = 'fs-4 fw-bold text-success';
        }
    }

    [sDemand, sDiscount, sCost].forEach(el => el.addEventListener('input', updateSimulation));

    document.getElementById('btnResetSim').addEventListener('click', function() {
        sDemand.value = 0;
        sDiscount.value = 0;
        sCost.value = 0;
        updateSimulation();
    });
});
<?php endif; ?>

// AI Copilot Assistant AJAX
function askAiQuestion(q) {
    document.getElementById('aiQuestionInput').value = q;
    sendAiQuery(q);
}

document.getElementById('btnSubmitAiQuestion').addEventListener('click', function() {
    const q = document.getElementById('aiQuestionInput').value;
    if (q.trim()) sendAiQuery(q);
});

document.getElementById('aiQuestionInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const q = this.value;
        if (q.trim()) sendAiQuery(q);
    }
});

function sendAiQuery(q) {
    const container = document.getElementById('aiResponseContainer');
    const textEl = document.getElementById('aiResponseText');
    const spinner = document.getElementById('aiSpinner');

    container.classList.remove('d-none');
    spinner.classList.remove('d-none');
    textEl.innerHTML = 'Synthesizing inventory models and calculating statistics...';

    fetch('index.php?ajax_action=ai_ask&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            spinner.classList.add('d-none');
            textEl.innerHTML = data.answer;
        })
        .catch(err => {
            spinner.classList.add('d-none');
            textEl.innerHTML = '<span class="text-danger">Failed to process query. Please try again.</span>';
        });
}

// FYP Demo Data Generation & Reset
const btnSeed = document.getElementById('btnSeedFypData');
const btnReset = document.getElementById('btnResetFypData');
const fypMsg = document.getElementById('fypActionMsg');

if (btnSeed) {
    btnSeed.addEventListener('click', function() {
        if (!confirm('Generate 90 days of realistic sales data for demonstration?')) return;
        btnSeed.disabled = true;
        btnSeed.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating 90-Day Dataset...';
        fypMsg.innerHTML = '<span class="text-info">Populating transactions and calculating trends...</span>';

        fetch('index.php?ajax_action=seed_demo')
            .then(r => r.json())
            .then(data => {
                btnSeed.disabled = false;
                btnSeed.innerHTML = '<i class="fas fa-wand-magic-sparkles me-1"></i> Generate 90-Day Rich Historical Dataset';
                if (data.success) {
                    fypMsg.innerHTML = '<span class="text-success">' + data.message + ' Reloading...</span>';
                    setTimeout(() => location.reload(), 1200);
                } else {
                    fypMsg.innerHTML = '<span class="text-danger">' + data.message + '</span>';
                }
            })
            .catch(err => {
                btnSeed.disabled = false;
                btnSeed.innerHTML = 'Generate 90-Day Rich Historical Dataset';
                fypMsg.innerHTML = '<span class="text-danger">Error seeding data.</span>';
            });
    });
}

if (btnReset) {
    btnReset.addEventListener('click', function() {
        if (!confirm('Reset simulation data back to baseline?')) return;
        btnReset.disabled = true;
        btnReset.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Resetting...';

        fetch('index.php?ajax_action=reset_demo')
            .then(r => r.json())
            .then(data => {
                btnReset.disabled = false;
                btnReset.innerHTML = '<i class="fas fa-trash-can me-1"></i> Reset Simulated Data (Keep Baseline)';
                if (data.success) {
                    fypMsg.innerHTML = '<span class="text-success">' + data.message + ' Reloading...</span>';
                    setTimeout(() => location.reload(), 1200);
                }
            });
    });
}
</script>

<?php include $_SERVER['DOCUMENT_ROOT'].'/inventory_system/includes/page_end.php'; ?>
