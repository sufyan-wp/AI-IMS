# 🚀 AI-Powered Smart Inventory & Stock Management System (IMS)
> **Final Year Project (FYP)** — A Next-Generation Predictive Inventory Intelligence & Point-of-Sale System.

![PHP](https://img.shields.io/badge/PHP-8.2-777bb4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479a1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952b3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-4.0-ff6384?style=for-the-badge&logo=chartdotjs&logoColor=white)

---

## 🌟 Key Features & AI Capabilities

### 1. 🔮 AI Demand & Sales Forecasting
* **Holt's Double Exponential Smoothing**: Dynamically captures sales level ($\alpha = 0.35$) and momentum trajectory ($\beta = 0.15$).
* **Ordinary Least Squares (OLS) Regression**: Fits linear trendlines with $R^2$ model accuracy scoring.
* **95% Confidence Prediction Envelope**: Visually computes standard error bounds for upper and lower predictive envelopes.
* **Multi-Horizon Forecasts**: Switchable horizons for **7D, 14D, 30D, and 90D**.

### 2. 💰 AI Profit & Loss Predictor & Sensitivity Simulator
* **Financial Forecasting**: Estimates gross revenue, Cost of Goods Sold (COGS), projected net profit, and profit margins.
* **Interactive What-If Simulator**: Real-time sliders to simulate **Demand Shift (-40% to +60%)**, **Promotional Discounts (0% to 25%)**, and **Supplier Inflation (-10% to +25%)** with live profit recalculation.
* **Margin Risk Watchlist**: Flags products selling near cost or vulnerable to discount erosion.

### 3. 🚀 High-Velocity Best Sellers & ABC Pareto (80/20) Analysis
* **Sales Velocity Tracking**: Daily burn rate (units/day) and monthly run-rate per SKU.
* **Pareto 80/20 Classification**:
  * **Class A (Star SKUs 🔥)**: Top revenue drivers generating ~80% of revenue.
  * **Class B (Steady Movers ⚡)**: Consistent performers generating ~15% of revenue.
  * **Class C (Moderate/Slow 🐢)**: Long-tail products generating ~5% of revenue.

### 4. ⚡ Restock & Stockout Predictor
* **Stockout Run-Out Countdown**: Calculates exact remaining days before inventory depletion ($\lfloor \text{Stock} / \text{Burn Rate} \rfloor$).
* **Exact Projected Stockout Date**: Calculates precise calendar depletion dates.
* **Dynamic EOQ Reorder Point**: Recommends optimal replenishment quantities with one-click restock integration.

### 5. 🛑 Dead Stock & Capital Optimization
* **Dormant Stock Detection**: Identifies products with zero or near-zero sales over 30–90 days.
* **Locked Capital Tracking**: Computes total PKR trapped in unsold inventory and monthly holding costs.
* **AI Directives**: Flags SKUs with **"FREEZE REPURCHASE"** and suggests clearance markdowns or bundling.

### 6. 📱 Mobile Camera Barcode & QR Scanner
* **Browser-Native Scanning**: Uses phone camera directly via HTML5 Camera API — no app installation needed!
* **POS Auto-Add**: Automatically detects barcodes, beeps with Web Audio oscillator, and adds the scanned item to the cart.
* **Product Search Scanner**: Scan barcodes to immediately filter and locate products in inventory.

### 7. 👥 Role-Based Access Control (RBAC)
* **Admin**: Complete system control, user management, backup/restore, activity logs, settings, and full AI analytics.
* **Manager**: Inventory management, categories, suppliers, stock in/out, and AI predictive analytics.
* **Salesman**: Product search by category/keyword, invoice creation, returns, and POS billing. *(AI suite is secured and restricted to Admin & Manager)*.

---

## 🛠️ Installation & Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/sufyan-wp/fyp.git
   ```
2. **Move to XAMPP Web Root**:
   Move the project folder into your web directory:
   ```
   C:/xampp/htdocs/inventory_system
   ```
3. **Database Setup**:
   * Open phpMyAdmin: `http://localhost/phpmyadmin`
   * Create database named: `inventory_system`
   * Import SQL file located at: `database/inventory_system.sql`
4. **Configure Database Connection**:
   Check `config/database.php` (default XAMPP credentials):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'inventory_system');
   ```
5. **Launch Application**:
   Open browser and visit:
   ```
   http://localhost/inventory_system
   ```

---

## 🔑 Default Login Credentials

| Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` |
| **Manager** | `manager` | `admin123` |
| **Salesman** | `salesman` | `admin123` |

---

## 🎓 FYP Demo Toolkit
The AI Analytics hub includes a built-in **FYP Showcase Toolkit**:
* **1-Click 90-Day Historical Data Seeder**: Generates ~150 realistic orders with weekday/weekend patterns, bestsellers, critical stockouts, and intentional dead stock to demonstrate rich time-series curves to examiners.
* **1-Click Reset**: Restores original baseline data instantly.
