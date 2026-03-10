<?php
require_once __DIR__ . '/../config/config.php';

/* =========================================================
   OWNER-ONLY ACCESS (TEMP SIMPLE LOCK)
   ========================================================= */
$OWNER_KEY = 'autowagen-owner-2025'; // 🔴 CHANGE THIS SECRET

if (!isset($_GET['key']) || $_GET['key'] !== $OWNER_KEY) {
    http_response_code(403);
    exit('Access denied.');
}

/* =========================================================
   DATE FILTERS (MONTH / YEAR)
   ========================================================= */
$month = isset($_GET['month']) && $_GET['month'] !== '' ? (int)$_GET['month'] : null;
$year  = isset($_GET['year'])  && $_GET['year']  !== '' ? (int)$_GET['year']  : null;

$where = [];

if ($year) {
    $where[] = "YEAR(snapshot_date) = $year";
}
if ($month && $month >= 1 && $month <= 12) {
    $where[] = "MONTH(snapshot_date) = $month";
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* =========================================================
   DASHBOARD QUERIES (SNAPSHOT-BASED)
   ========================================================= */

/* ---- TOTAL PROFIT ---- */
$totalProfit = $conn->query("
    SELECT SUM(profit) AS v
    FROM vehicle_profit_snapshots
    $whereSql
")->fetch_assoc()['v'] ?? 0;

/* ---- TOTAL REVENUE & COSTS ---- */
$totals = $conn->query("
    SELECT
        SUM(stripped_revenue) AS revenue,
        SUM(total_costs) AS costs
    FROM vehicle_profit_snapshots
    $whereSql
")->fetch_assoc();

$totals['revenue'] = $totals['revenue'] ?? 0;
$totals['costs']   = $totals['costs'] ?? 0;

/* ---- AVERAGE PROFIT ---- */
$avgProfit = $conn->query("
    SELECT AVG(profit) AS v
    FROM vehicle_profit_snapshots
    $whereSql
")->fetch_assoc()['v'] ?? 0;

/* ---- TOP PROFITABLE VEHICLES ---- */
$topVehicles = $conn->query("
    SELECT vehicle_id, profit
    FROM vehicle_profit_snapshots
    $whereSql
    ORDER BY profit DESC
    LIMIT 5
");

/* ---- LOSS-MAKING VEHICLES (FIXED LOGIC) ---- */
$lossWhereSql = $whereSql
    ? $whereSql . " AND profit < 0"
    : "WHERE profit < 0";

$lossVehicles = $conn->query("
    SELECT vehicle_id, profit
    FROM vehicle_profit_snapshots
    $lossWhereSql
    ORDER BY profit ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Owner Dashboard</title>
<style>
    body {
        background:#000;
        color:#fff;
        font-family: Arial, sans-serif;
        margin:20px;
    }
    h1 { color:red; }
    .filters { margin-bottom:20px; }
    .kpi {
        display:flex;
        gap:20px;
        margin-bottom:30px;
        flex-wrap:wrap;
    }
    .box {
        background:#111;
        border:1px solid #333;
        padding:20px;
        width:220px;
    }
    table {
        width:100%;
        border-collapse:collapse;
        margin-top:15px;
    }
    th, td {
        border:1px solid #333;
        padding:10px;
        text-align:right;
    }
    th {
        background:#111;
        color:red;
    }
    td:first-child, th:first-child {
        text-align:left;
    }
    .pos { color:#00ff88; font-weight:bold; }
    .neg { color:#ff4444; font-weight:bold; }
</style>
</head>
<body>

<h1>📊 Owner Dashboard</h1>

<div class="filters">
<form method="get">
    <input type="hidden" name="key" value="<?= htmlspecialchars($OWNER_KEY) ?>">

    Month:
    <select name="month">
        <option value="">All</option>
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= ($month === $m) ? 'selected' : '' ?>>
                <?= date('F', mktime(0,0,0,$m,1)) ?>
            </option>
        <?php endfor; ?>
    </select>

    Year:
    <input type="number" name="year" value="<?= htmlspecialchars($year) ?>" placeholder="YYYY" style="width:80px">

    <button type="submit">Apply</button>
</form>
</div>

<div class="kpi">
    <div class="box">💰 Total Profit<br><strong>R <?= number_format($totalProfit,2) ?></strong></div>
    <div class="box">📈 Avg Profit / Vehicle<br><strong>R <?= number_format($avgProfit,2) ?></strong></div>
    <div class="box">🧾 Total Revenue<br><strong>R <?= number_format($totals['revenue'],2) ?></strong></div>
    <div class="box">📦 Total Costs<br><strong>R <?= number_format($totals['costs'],2) ?></strong></div>
</div>

<h2>🚗 Top Profitable Vehicles</h2>
<table>
<tr><th>Vehicle ID</th><th>Profit</th></tr>
<?php if ($topVehicles && $topVehicles->num_rows > 0): ?>
<?php while ($r = $topVehicles->fetch_assoc()): ?>
<tr>
    <td><?= (int)$r['vehicle_id'] ?></td>
    <td class="pos">R <?= number_format($r['profit'],2) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="2">No data</td></tr>
<?php endif; ?>
</table>

<h2>⚠️ Loss-Making Vehicles</h2>
<table>
<tr><th>Vehicle ID</th><th>Profit</th></tr>
<?php if ($lossVehicles && $lossVehicles->num_rows > 0): ?>
<?php while ($r = $lossVehicles->fetch_assoc()): ?>
<tr>
    <td><?= (int)$r['vehicle_id'] ?></td>
    <td class="neg">R <?= number_format($r['profit'],2) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="2">No loss-making vehicles 🎉</td></tr>
<?php endif; ?>
</table>

</body>
</html>
