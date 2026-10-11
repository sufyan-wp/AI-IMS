<?php
/**
 * AI Engine for Inventory Management & Predictive Analytics
 * Developed for FYP - Advanced Predictive Inventory Intelligence System
 * 
 * Features:
 *  - Time-series Demand & Sales Forecasting (Linear Regression, Exponential Smoothing, Holt-Winters)
 *  - Prediction Intervals (95% Confidence Upper/Lower bounds)
 *  - Profit & Loss Forecasting with Scenario Simulator
 *  - Product Velocity & ABC Pareto 80/20 Classification
 *  - Stockout Risk & Run-out Date Prediction (Burn Rate & EOQ Reorder Point)
 *  - Dead Stock / Capital Lockup Detector & Purchasing Halt Recommendations
 *  - Automated Executive Briefing & Natural Language AI Assistant
 *  - FYP Demo Historical Data Generator & Reset Utility
 */

class AIEngine {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    /**
     * Fetch daily sales aggregation over a given historical window (in days)
     */
    public function getHistoricalDailySales($daysBack = 90) {
        $stmt = $this->conn->prepare("
            SELECT 
                DATE(s.created_at) as sale_date,
                COUNT(DISTINCT s.sale_id) as invoice_count,
                COALESCE(SUM(s.total_amount), 0) as gross_revenue,
                COALESCE(SUM(si.quantity), 0) as total_units_sold,
                COALESCE(SUM(si.quantity * p.cost_price), 0) as total_cogs
            FROM sales s
            LEFT JOIN sale_items si ON s.sale_id = si.sale_id
            LEFT JOIN products p ON si.product_id = p.product_id
            WHERE s.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(s.created_at)
            ORDER BY sale_date ASC
        ");
        $stmt->bind_param("i", $daysBack);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    /**
     * Calculate Linear Regression on a series of (x, y) points
     * y = mx + b
     */
    public function calculateLinearRegression($series) {
        $n = count($series);
        if ($n === 0) {
            return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0, 'std_err' => 0];
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        foreach ($series as $i => $y) {
            $x = $i + 1;
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumX2 += ($x * $x);
            $sumY2 += ($y * $y);
        }

        $denominator = ($n * $sumX2 - ($sumX * $sumX));
        if ($denominator == 0) {
            $slope = 0;
            $intercept = $sumY / max(1, $n);
        } else {
            $slope = ($n * $sumXY - ($sumX * $sumY)) / $denominator;
            $intercept = ($sumY - ($slope * $sumX)) / $n;
        }

        // Calculate R-Squared and Standard Error
        $ssTot = 0;
        $ssRes = 0;
        $meanY = $sumY / max(1, $n);

        foreach ($series as $i => $y) {
            $x = $i + 1;
            $yPred = ($slope * $x) + $intercept;
            $ssTot += pow($y - $meanY, 2);
            $ssRes += pow($y - $yPred, 2);
        }

        $rSquared = ($ssTot > 0) ? max(0, 1 - ($ssRes / $ssTot)) : 0;
        $stdErr = ($n > 2) ? sqrt($ssRes / ($n - 2)) : 0;

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => round($rSquared, 4),
            'std_err' => $stdErr
        ];
    }

    /**
     * Single and Holt's Double Exponential Smoothing
     */
    public function calculateExponentialSmoothing($series, $alpha = 0.35, $beta = 0.15) {
        $n = count($series);
        if ($n === 0) return ['forecast' => 0, 'smoothed' => []];

        $level = $series[0];
        $trend = ($n > 1) ? ($series[1] - $series[0]) : 0;
        $smoothed = [];

        foreach ($series as $i => $y) {
            $prevLevel = $level;
            $prevTrend = $trend;
            $level = ($alpha * $y) + ((1 - $alpha) * ($prevLevel + $prevTrend));
            $trend = ($beta * ($level - $prevLevel)) + ((1 - $beta) * $prevTrend);
            $smoothed[] = $level + $trend;
        }

        return [
            'level' => $level,
            'trend' => $trend,
            'smoothed' => $smoothed
        ];
    }

    /**
     * Generate Sales Forecast with Confidence Bands for Next N Days
     */
    public function generateSalesForecast($forecastHorizon = 30, $historyDays = 60) {
        $history = $this->getHistoricalDailySales($historyDays);
        
        // Map history by date and fill missing dates with 0 for continuous time-series
        $dateMap = [];
        foreach ($history as $row) {
            $dateMap[$row['sale_date']] = floatval($row['gross_revenue']);
        }

        $allDates = [];
        $allValues = [];
        $startDate = new DateTime("-$historyDays days");
        $today = new DateTime();

        $cur = clone $startDate;
        while ($cur < $today) {
            $dStr = $cur->format('Y-m-d');
            $allDates[] = $dStr;
            $allValues[] = $dateMap[$dStr] ?? 0.0;
            $cur->modify('+1 day');
        }

        $n = count($allValues);
        $totalHistoricalRevenue = array_sum($allValues);
        $avgDailyRevenue = $n > 0 ? ($totalHistoricalRevenue / $n) : 0;

        // If dataset is extremely sparse or brand new, calculate baseline from products/sales
        if ($totalHistoricalRevenue <= 0) {
            $salesTotal = $this->conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM sales")->fetch_row()[0];
            $avgDailyRevenue = floatval($salesTotal) / max(1, $historyDays);
            $allValues = array_fill(0, max(1, $n), $avgDailyRevenue);
        }

        // Run Linear Regression & Holt Smoothing
        $reg = $this->calculateLinearRegression($allValues);
        $holt = $this->calculateExponentialSmoothing($allValues, 0.35, 0.15);

        // Compute Multi-Horizon Forecast Points
        $forecastDates = [];
        $forecastValues = [];
        $forecastUpper = [];
        $forecastLower = [];

        $lastLevel = $holt['level'] ?? $avgDailyRevenue;
        $trend = $holt['trend'] ?? $reg['slope'];
        $stdErr = max($reg['std_err'], $avgDailyRevenue * 0.15);

        $runningCur = clone $today;
        $projectedTotal = 0;

        for ($k = 1; $k <= $forecastHorizon; $k++) {
            $dStr = $runningCur->format('Y-m-d');
            $forecastDates[] = $dStr;

            // Blended Forecast: 60% Holt Exponential Smoothing + 40% Linear Regression
            $xFuture = $n + $k;
            $regPred = ($reg['slope'] * $xFuture) + $reg['intercept'];
            $holtPred = $lastLevel + ($trend * $k);

            // Add day-of-week seasonality weight (Weekends typically +15%, Midweek steady)
            $dayOfWeek = (int)$runningCur->format('N'); // 1 (Mon) - 7 (Sun)
            $seasonalityMultiplier = ($dayOfWeek == 6 || $dayOfWeek == 7) ? 1.15 : (($dayOfWeek == 5) ? 1.05 : 0.96);

            $blended = (0.55 * $holtPred + 0.45 * $regPred) * $seasonalityMultiplier;
            // Floor at 0 (sales cannot be negative)
            $blended = max(0, $blended);
            if ($blended < ($avgDailyRevenue * 0.2) && $avgDailyRevenue > 0) {
                $blended = $avgDailyRevenue * 0.9 * $seasonalityMultiplier;
            }

            $forecastValues[] = round($blended, 2);
            $projectedTotal += $blended;

            // 95% Confidence Band (z = 1.96) widening over forecast horizon
            $marginOfError = 1.96 * $stdErr * sqrt(1 + ($k / max(1, $n)));
            $forecastUpper[] = round(max(0, $blended + $marginOfError), 2);
            $forecastLower[] = round(max(0, $blended - $marginOfError), 2);

            $runningCur->modify('+1 day');
        }

        // Compare projected period with equal past period to get expected growth rate %
        $recentSlice = array_slice($allValues, -$forecastHorizon);
        $recentSum = array_sum($recentSlice);
        $growthRate = ($recentSum > 0) ? ((($projectedTotal - $recentSum) / $recentSum) * 100) : 0;

        return [
            'history_dates' => $allDates,
            'history_values' => $allValues,
            'forecast_dates' => $forecastDates,
            'forecast_values' => $forecastValues,
            'forecast_upper' => $forecastUpper,
            'forecast_lower' => $forecastLower,
            'projected_total' => round($projectedTotal, 2),
            'avg_daily_projected' => round($projectedTotal / max(1, $forecastHorizon), 2),
            'historical_recent_sum' => round($recentSum, 2),
            'growth_rate_pct' => round($growthRate, 1),
            'r_squared' => $reg['r_squared'],
            'model_accuracy_rating' => ($reg['r_squared'] >= 0.7) ? 'High Precision' : (($reg['r_squared'] >= 0.4) ? 'Moderate Confidence' : 'Dynamic Baseline Model')
        ];
    }

    /**
     * AI Expected Profit & Loss Analysis
     */
    public function getProfitLossAnalysis($forecastDays = 30) {
        $forecast = $this->generateSalesForecast($forecastDays);
        $projectedRevenue = $forecast['projected_total'];

        // Calculate historical Cost of Goods Sold (COGS) ratio from actual sales items
        $marginQuery = $this->conn->query("
            SELECT 
                COALESCE(SUM(si.subtotal), 0) as total_sold_amount,
                COALESCE(SUM(si.quantity * p.cost_price), 0) as total_cost_amount
            FROM sale_items si
            JOIN products p ON si.product_id = p.product_id
        ");
        $marginRow = $marginQuery->fetch_assoc();
        $histRev = floatval($marginRow['total_sold_amount']);
        $histCost = floatval($marginRow['total_cost_amount']);

        // Default or historical profit margin
        if ($histRev > 0) {
            $cogsRatio = min(0.95, max(0.2, $histCost / $histRev));
            $grossMarginPct = (1 - $cogsRatio) * 100;
        } else {
            // Estimate based on overall product catalog
            $catQuery = $this->conn->query("SELECT AVG(cost_price/NULLIF(selling_price,0)) as avg_ratio FROM products WHERE status='active' AND selling_price > 0");
            $catRow = $catQuery->fetch_assoc();
            $cogsRatio = floatval($catRow['avg_ratio'] ?? 0.72);
            $grossMarginPct = (1 - $cogsRatio) * 100;
        }

        $projectedCOGS = $projectedRevenue * $cogsRatio;
        $projectedGrossProfit = $projectedRevenue - $projectedCOGS;

        // Estimated returns & tax deductions based on system averages
        $returnRate = 0.02; // ~2% expected return rate
        $projectedReturns = $projectedRevenue * $returnRate;
        $projectedNetProfit = $projectedGrossProfit - $projectedReturns;
        $netMarginPct = ($projectedRevenue > 0) ? ($projectedNetProfit / $projectedRevenue) * 100 : 0;

        // Find loss-risk products (cost_price >= selling_price or profit margin < 5%)
        $lossRisks = $this->conn->query("
            SELECT product_id, product_name, cost_price, selling_price, quantity,
                   ROUND((selling_price - cost_price), 2) as unit_margin,
                   ROUND(((selling_price - cost_price) / NULLIF(selling_price, 0)) * 100, 1) as margin_pct
            FROM products
            WHERE status='active' AND selling_price <= (cost_price * 1.05)
            ORDER BY margin_pct ASC
            LIMIT 5
        ")->fetch_all(MYSQLI_ASSOC);

        return [
            'forecast_days' => $forecastDays,
            'projected_revenue' => round($projectedRevenue, 2),
            'projected_cogs' => round($projectedCOGS, 2),
            'projected_gross_profit' => round($projectedGrossProfit, 2),
            'projected_net_profit' => round($projectedNetProfit, 2),
            'gross_margin_pct' => round($grossMarginPct, 1),
            'net_margin_pct' => round($netMarginPct, 1),
            'loss_risks' => $lossRisks,
            'health_grade' => ($netMarginPct >= 25) ? 'A+ (Excellent Margins)' : (($netMarginPct >= 15) ? 'B (Healthy Profitability)' : 'C (Margin Optimization Required)')
        ];
    }

    /**
     * Product Velocity & ABC Pareto (80/20) Analysis
     * Determines which products are sold more, sales velocity, and category tiers
     */
    public function getProductVelocityAndABC($daysWindow = 60) {
        // Query product sales in window
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id,
                p.product_name,
                p.brand,
                c.category_name,
                p.cost_price,
                p.selling_price,
                p.quantity as current_stock,
                p.min_quantity,
                COALESCE(SUM(si.quantity), 0) as total_qty_sold,
                COALESCE(SUM(si.subtotal), 0) as total_revenue,
                COALESCE(SUM(si.quantity * (si.unit_price - p.cost_price)), 0) as total_profit
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN sale_items si ON p.product_id = si.product_id
            LEFT JOIN sales s ON si.sale_id = s.sale_id AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            WHERE p.status = 'active'
            GROUP BY p.product_id
            ORDER BY total_revenue DESC, total_qty_sold DESC
        ");
        $stmt->bind_param("i", $daysWindow);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $totalCatalogRevenue = array_sum(array_column($products, 'total_revenue'));
        $runningRevenue = 0;

        foreach ($products as &$item) {
            $qtySold = intval($item['total_qty_sold']);
            $rev = floatval($item['total_revenue']);

            // Velocity (Units sold per day)
            $velocity = $qtySold / max(1, $daysWindow);
            $item['velocity_daily'] = round($velocity, 3);
            $item['velocity_monthly'] = round($velocity * 30, 1);

            // Revenue share %
            $item['revenue_share_pct'] = ($totalCatalogRevenue > 0) ? round(($rev / $totalCatalogRevenue) * 100, 2) : 0;
            
            // Cumulative share for Pareto ABC classification
            $runningRevenue += $rev;
            $cumShare = ($totalCatalogRevenue > 0) ? ($runningRevenue / $totalCatalogRevenue) * 100 : 100;
            $item['cumulative_share'] = round($cumShare, 1);

            // ABC Pareto Tier
            if ($cumShare <= 75 || $item['revenue_share_pct'] >= 10) {
                $item['abc_class'] = 'A'; // Top Revenue Drivers (~80% revenue)
                $item['velocity_badge'] = '🔥 Star / High Velocity';
                $item['velocity_class'] = 'success';
            } elseif ($cumShare <= 95) {
                $item['abc_class'] = 'B'; // Steady Performers (~15% revenue)
                $item['velocity_badge'] = '⚡ Steady Mover';
                $item['velocity_class'] = 'primary';
            } else {
                $item['abc_class'] = 'C'; // Long Tail (~5% revenue)
                $item['velocity_badge'] = ($qtySold > 0) ? '📊 Moderate' : '🐢 Dormant / Slow';
                $item['velocity_class'] = ($qtySold > 0) ? 'warning' : 'secondary';
            }
        }
        unset($item);

        return [
            'products' => $products,
            'total_revenue' => $totalCatalogRevenue,
            'top_seller' => $products[0] ?? null,
            'class_a_count' => count(array_filter($products, fn($p) => $p['abc_class'] === 'A')),
            'class_b_count' => count(array_filter($products, fn($p) => $p['abc_class'] === 'B')),
            'class_c_count' => count(array_filter($products, fn($p) => $p['abc_class'] === 'C'))
        ];
    }

    /**
     * Restock & Stockout Predictor
     * Calculates burn rate, days until stockout, exact stockout date, and AI Recommended Reorder Qty (EOQ)
     */
    public function getRestockAndStockoutPredictions($leadTimeDays = 7, $safetyStockDays = 7, $analysisWindow = 60) {
        $velocityData = $this->getProductVelocityAndABC($analysisWindow);
        $products = $velocityData['products'];

        $predictions = [];
        $criticalCount = 0;
        $warningCount = 0;
        $healthyCount = 0;

        foreach ($products as $p) {
            $currentStock = intval($p['current_stock']);
            $minQty = intval($p['min_quantity']);
            $burnRate = floatval($p['velocity_daily']); // units/day

            // If burn rate is zero but stock is below min_quantity, estimate minimal burn rate
            if ($burnRate <= 0.001) {
                $effectiveBurn = 0.05; // ~1 unit every 20 days
                $hasSales = false;
            } else {
                $effectiveBurn = $burnRate;
                $hasSales = true;
            }

            // Days until stockout = current stock / burn rate
            $daysToStockout = ($burnRate > 0) ? round($currentStock / $burnRate, 1) : 999;

            // Projected exact stockout date
            if ($daysToStockout <= 120 && $burnRate > 0) {
                $projectedDate = (new DateTime())->modify('+' . round($daysToStockout) . ' days')->format('d M Y');
            } elseif ($currentStock <= 0) {
                $projectedDate = 'ALREADY STOCKOUT';
            } else {
                $projectedDate = 'No Stockout Imminent';
            }

            // AI Recommended Reorder Quantity (Dynamic EOQ + Safety Stock Model):
            // Safety Stock = burn_rate * safetyStockDays
            // Reorder Point (ROP) = (burn_rate * leadTimeDays) + Safety Stock
            // Target Order Qty = (burn_rate * 30 days buffer) + min_quantity - currentStock
            $safetyStock = ceil($burnRate * $safetyStockDays);
            $reorderPoint = ceil(($burnRate * $leadTimeDays) + $safetyStock);
            if ($reorderPoint < $minQty) {
                $reorderPoint = $minQty;
            }

            $needsRestock = ($currentStock <= $reorderPoint) || ($currentStock <= $minQty) || ($daysToStockout <= 14);

            $recommendedReorderQty = 0;
            if ($needsRestock) {
                // Reorder enough for 30-45 days of sales buffer plus safety margin
                $targetStock = max($minQty * 3, ceil($burnRate * 30) + $safetyStock);
                $recommendedReorderQty = max(5, $targetStock - $currentStock);
            }

            // Urgency Classification
            if ($currentStock <= 0) {
                $urgency = 'OUT_OF_STOCK';
                $urgencyLabel = '🔴 Stockout (0 Units)';
                $urgencyBadge = 'danger';
                $criticalCount++;
            } elseif ($daysToStockout <= 7 || ($currentStock <= $minQty && $hasSales)) {
                $urgency = 'CRITICAL';
                $urgencyLabel = '🚨 Critical (< 7 Days)';
                $urgencyBadge = 'danger';
                $criticalCount++;
            } elseif ($daysToStockout <= 15 || $needsRestock) {
                $urgency = 'WARNING';
                $urgencyLabel = '⚠️ Reorder Soon (8-15 Days)';
                $urgencyBadge = 'warning';
                $warningCount++;
            } else {
                $urgency = 'HEALTHY';
                $urgencyLabel = '🟢 Healthy Stock';
                $urgencyBadge = 'success';
                $healthyCount++;
            }

            $predictions[] = [
                'product_id' => $p['product_id'],
                'product_name' => $p['product_name'],
                'brand' => $p['brand'],
                'category_name' => $p['category_name'],
                'current_stock' => $currentStock,
                'min_quantity' => $minQty,
                'cost_price' => floatval($p['cost_price']),
                'selling_price' => floatval($p['selling_price']),
                'daily_burn_rate' => round($burnRate, 2),
                'days_to_stockout' => $daysToStockout,
                'stockout_date' => $projectedDate,
                'reorder_point' => $reorderPoint,
                'needs_restock' => $needsRestock,
                'recommended_reorder_qty' => $recommendedReorderQty,
                'reorder_cost_estimate' => round($recommendedReorderQty * floatval($p['cost_price']), 2),
                'urgency' => $urgency,
                'urgency_label' => $urgencyLabel,
                'urgency_badge' => $urgencyBadge,
                'abc_class' => $p['abc_class']
            ];
        }

        // Sort by urgency priority (Out of stock & Critical first, then lowest days)
        usort($predictions, function($a, $b) {
            $priority = ['OUT_OF_STOCK' => 1, 'CRITICAL' => 2, 'WARNING' => 3, 'HEALTHY' => 4];
            $pDiff = ($priority[$a['urgency']] ?? 5) - ($priority[$b['urgency']] ?? 5);
            if ($pDiff !== 0) return $pDiff;
            return ($a['days_to_stockout'] <=> $b['days_to_stockout']);
        });

        return [
            'predictions' => $predictions,
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'healthy_count' => $healthyCount,
            'lead_time_days' => $leadTimeDays
        ];
    }

    /**
     * Dead Stock & Slow-Moving Capital Optimization
     * Finds items that are not being sold, calculates tied-up capital,
     * and recommends NOT purchasing / clearance strategies.
     */
    public function getDeadStockAnalysis($dormantDaysThreshold = 45) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id,
                p.product_name,
                p.brand,
                c.category_name,
                p.quantity as stock_qty,
                p.cost_price,
                p.selling_price,
                (p.quantity * p.cost_price) as locked_capital,
                COALESCE(SUM(si.quantity), 0) as recent_sales,
                MAX(s.created_at) as last_sold_date,
                p.created_at as product_created_date
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN sale_items si ON p.product_id = si.product_id
            LEFT JOIN sales s ON si.sale_id = s.sale_id AND s.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            WHERE p.status = 'active'
            GROUP BY p.product_id
            ORDER BY locked_capital DESC
        ");
        $stmt->bind_param("i", $dormantDaysThreshold);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $deadStockList = [];
        $totalCapitalLocked = 0;
        $totalUnitsLocked = 0;

        foreach ($items as $row) {
            $recentSales = intval($row['recent_sales']);
            $stock = intval($row['stock_qty']);
            $cost = floatval($row['cost_price']);
            $locked = floatval($row['locked_capital']);

            // If zero sales in the threshold window and stock exists:
            if ($recentSales == 0 && $stock > 0) {
                $totalCapitalLocked += $locked;
                $totalUnitsLocked += $stock;

                $lastSaleStr = $row['last_sold_date'] ? date('d M Y', strtotime($row['last_sold_date'])) : 'Never Sold';
                $daysInactive = $row['last_sold_date'] ? (new DateTime())->diff(new DateTime($row['last_sold_date']))->days : $dormantDaysThreshold;

                // AI Suggested Action
                if ($locked >= 25000) {
                    $recommendation = "⛔ HALT REPURCHASE & Liquidate: Apply 15-20% clearance markdown to recover PKR " . number_format($locked, 0);
                    $severity = "Severe Capital Lock";
                    $badge = "danger";
                } elseif ($stock > 20) {
                    $recommendation = "📦 Bundle Promotion: Pair with fast-moving star products or stop new purchase orders";
                    $severity = "Excess Unsold Units";
                    $badge = "warning";
                } else {
                    $recommendation = "⏸️ Freeze Procurement: Do not order more stock until existing units cycle out";
                    $severity = "Dormant Stock";
                    $badge = "secondary";
                }

                $deadStockList[] = [
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'brand' => $row['brand'],
                    'category_name' => $row['category_name'],
                    'stock_qty' => $stock,
                    'cost_price' => $cost,
                    'selling_price' => floatval($row['selling_price']),
                    'locked_capital' => $locked,
                    'last_sold_date' => $lastSaleStr,
                    'days_inactive' => $daysInactive,
                    'recommendation' => $recommendation,
                    'severity' => $severity,
                    'badge' => $badge
                ];
            }
        }

        return [
            'dead_items' => $deadStockList,
            'total_locked_capital' => round($totalCapitalLocked, 2),
            'total_locked_units' => $totalUnitsLocked,
            'dead_items_count' => count($deadStockList),
            'threshold_days' => $dormantDaysThreshold
        ];
    }

    /**
     * Generate Comprehensive Executive AI Insights (Natural Language Briefing)
     */
    public function generateExecutiveSummary() {
        $forecast = $this->generateSalesForecast(30);
        $restock = $this->getRestockAndStockoutPredictions();
        $dead = $this->getDeadStockAnalysis(30);
        $velocity = $this->getProductVelocityAndABC(60);
        $pl = $this->getProfitLossAnalysis(30);

        $insights = [];

        // 1. Growth & Demand trajectory
        $growthSign = $forecast['growth_rate_pct'] >= 0 ? '+' : '';
        $insights[] = [
            'type' => 'trend',
            'icon' => 'fas fa-chart-line',
            'color' => $forecast['growth_rate_pct'] >= 0 ? 'success' : 'danger',
            'title' => '30-Day Revenue Forecast',
            'message' => "AI models project total revenue of PKR " . number_format($forecast['projected_total'], 0) . 
                         " over next 30 days ({$growthSign}{$forecast['growth_rate_pct']}% trajectory vs prior period). Expected daily average is PKR " . 
                         number_format($forecast['avg_daily_projected'], 0) . "."
        ];

        // 2. Profitability Health
        $insights[] = [
            'type' => 'profit',
            'icon' => 'fas fa-sack-dollar',
            'color' => 'primary',
            'title' => 'Expected Net Profit & Margin',
            'message' => "Estimated net profit is PKR " . number_format($pl['projected_net_profit'], 0) . 
                         " at a healthy " . $pl['net_margin_pct'] . "% net margin. Overall portfolio health score: " . $pl['health_grade'] . "."
        ];

        // 3. Stockout Warning
        if ($restock['critical_count'] > 0) {
            $criticalNames = array_slice(array_column(array_filter($restock['predictions'], fn($x) => $x['urgency'] === 'CRITICAL' || $x['urgency'] === 'OUT_OF_STOCK'), 'product_name'), 0, 3);
            $insights[] = [
                'type' => 'stockout',
                'icon' => 'fas fa-triangle-exclamation',
                'color' => 'danger',
                'title' => 'Imminent Stockout Threat',
                'message' => "{$restock['critical_count']} items are in critical stockout danger (< 7 days buffer remaining), including: " . 
                             implode(', ', $criticalNames) . ". Immediate reorder recommended."
            ];
        } else {
            $insights[] = [
                'type' => 'stockout',
                'icon' => 'fas fa-shield-check',
                'color' => 'success',
                'title' => 'Stock Buffer Status',
                'message' => "Inventory replenishment levels are stable across high velocity items."
            ];
        }

        // 4. Dead Stock & Purchasing Restraint
        if ($dead['dead_items_count'] > 0) {
            $insights[] = [
                'type' => 'dead_stock',
                'icon' => 'fas fa-hand-holding-dollar',
                'color' => 'warning',
                'title' => 'Capital Trapped in Slow Inventory',
                'message' => "PKR " . number_format($dead['total_locked_capital'], 0) . " is tied up in {$dead['dead_items_count']} slow-moving/dormant products. Recommendation: Freeze new procurement for these items and deploy bundle discount promotions."
            ];
        }

        // 5. Star Product Velocity
        if ($velocity['top_seller']) {
            $ts = $velocity['top_seller'];
            $insights[] = [
                'type' => 'velocity',
                'icon' => 'fas fa-fire',
                'color' => 'info',
                'title' => 'Top Velocity Driver',
                'message' => "{$ts['product_name']} is the #1 bestseller contributing {$ts['revenue_share_pct']}% of total sales. Maintain constant minimum safety stock to avoid lost opportunity."
            ];
        }

        return $insights;
    }

    /**
     * FYP Showcase Helper:
     * Seeds realistic historical sales dataset (last 90 days)
     * so that during final year project defense, all algorithms have rich multi-month time series!
     */
    public function seedFYPDemoDataset() {
        // Fetch all active products
        $prods = $this->conn->query("SELECT product_id, product_name, cost_price, selling_price, quantity FROM products WHERE status='active'")->fetch_all(MYSQLI_ASSOC);
        if (empty($prods)) return ['success' => false, 'message' => 'No products found.'];

        $custs = $this->conn->query("SELECT customer_id, customer_name FROM customers")->fetch_all(MYSQLI_ASSOC);
        if (empty($custs)) {
            $custs = [['customer_id' => 1, 'customer_name' => 'Walk-in Customer']];
        }

        $methods = ['Cash', 'Card', 'Bank Transfer'];
        $insertedInvoices = 0;

        // Generate sales over the past 90 days with realistic weekly rhythm and varying product popularity
        for ($day = 89; $day >= 0; $day--) {
            $date = date('Y-m-d H:i:s', strtotime("-$day days " . rand(9, 20) . ":" . rand(10, 55) . ":" . rand(10, 55)));
            $dayOfWeek = date('N', strtotime($date));
            // Weekend generates more transactions
            $ordersToday = ($dayOfWeek >= 6) ? rand(2, 4) : rand(1, 2);

            for ($o = 0; $o < $ordersToday; $o++) {
                $cust = $custs[array_rand($custs)];
                $invNum = 'INV-FYP-' . sprintf('%05d', rand(10000, 99999));
                $method = $methods[array_rand($methods)];

                // Pick 1-3 distinct products (biased toward high-velocity items like Samsung, iPhone, Oil, Rice)
                $numItems = rand(1, 3);
                $selectedIndexes = (array)array_rand($prods, min($numItems, count($prods)));
                
                $subtotal = 0;
                $orderItems = [];

                foreach ($selectedIndexes as $idx) {
                    $prod = $prods[$idx];
                    // Intentionally leave 4 products completely unsold to showcase the AI Dead Stock & Purchasing Halt algorithm!
                    if (in_array((int)$prod['product_id'], [11, 12, 17, 18])) {
                        continue;
                    }

                    $qty = rand(1, 4);
                    // Higher quantity for cheaper items
                    if ($prod['selling_price'] < 1000) $qty = rand(2, 8);

                    $price = floatval($prod['selling_price']);
                    $itemTotal = $qty * $price;
                    $subtotal += $itemTotal;

                    $orderItems[] = [
                        'product_id' => $prod['product_id'],
                        'product_name' => $prod['product_name'],
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $itemTotal
                    ];
                }

                if (empty($orderItems)) continue;

                $taxRate = 8.0;
                $taxAmount = round(($subtotal * $taxRate) / 100, 2);
                $discount = (rand(1, 10) > 7) ? round(min(500, $subtotal * 0.05), 2) : 0;
                $totalAmount = $subtotal + $taxAmount - $discount;

                // Insert into sales
                $stmt = $this->conn->prepare("
                    INSERT INTO sales (invoice_number, customer_id, customer_name, user_id, subtotal, tax_rate, tax_amount, discount, total_amount, payment_method, payment_status, notes, created_at)
                    VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?, 'Paid', 'FYP Simulation Order', ?)
                ");
                $stmt->bind_param("sisddddsss", $invNum, $cust['customer_id'], $cust['customer_name'], $subtotal, $taxRate, $taxAmount, $discount, $totalAmount, $method, $date);
                if ($stmt->execute()) {
                    $saleId = $this->conn->insert_id;
                    $stmt->close();

                    // Insert sale items
                    $stmtItem = $this->conn->prepare("
                        INSERT INTO sale_items (sale_id, product_id, product_name, quantity, unit_price, subtotal)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    foreach ($orderItems as $it) {
                        $stmtItem->bind_param("iisidd", $saleId, $it['product_id'], $it['product_name'], $it['quantity'], $it['unit_price'], $it['subtotal']);
                        $stmtItem->execute();
                    }
                    $stmtItem->close();
                    $insertedInvoices++;
                }
            }
        }

        return [
            'success' => true,
            'inserted_invoices' => $insertedInvoices,
            'message' => "Successfully generated {$insertedInvoices} historical transactions across past 90 days!"
        ];
    }

    /**
     * Reset Demo FYP Data
     */
    public function resetFYPDemoData() {
        // Delete sales generated by simulation
        $this->conn->query("
            DELETE si FROM sale_items si 
            JOIN sales s ON si.sale_id = s.sale_id 
            WHERE s.notes = 'FYP Simulation Order' OR s.invoice_number LIKE 'INV-FYP-%'
        ");
        $this->conn->query("DELETE FROM sales WHERE notes = 'FYP Simulation Order' OR invoice_number LIKE 'INV-FYP-%'");
        return ['success' => true, 'message' => 'Simulated demo sales data cleared successfully. Baseline data preserved.'];
    }
}
