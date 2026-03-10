<?php
require_once "config/config.php";
include "includes/header.php";

if (!function_exists('h')) {
    function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

// Totals
$sqlTotals = "
    SELECT
        COUNT(*) AS total_parts,
        COALESCE(SUM(stock_qty),0) AS total_units,
        COALESCE(SUM(stock_qty * cost_price),0) AS total_cost_value,
        COALESCE(SUM(stock_qty * selling_price),0) AS total_revenue_value,
        COALESCE(SUM(stock_qty * (selling_price - cost_price)),0) AS total_potential_profit
    FROM oem_parts
";
$totals = $conn->query($sqlTotals)->fetch_assoc();

// Top stock by qty
$sqlTop = "
    SELECT id, oem_number, part_name, stock_qty, cost_price, selling_price
    FROM oem_parts
    ORDER BY stock_qty DESC, id ASC
    LIMIT 20
";
$resTop = $conn->query($sqlTop);
?>
<style>
.page {
    width:95%;
    margin:20px auto;
    background:#111;
    border:2px solid #b00000;
    color:#fff;
    padding:20px;
    border-radius:8px;
}
.page h2 {
    text-align:center;
    color:#ff3333;
    margin-bottom:20px;
}
.dashboard-cards {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
}
.card {
    flex:1 1 200px;
    background:#181818;
    border:1px solid #333;
    border-radius:10px;
    padding:12px 15px;
}
.card-label {
    font-size:12px;
    text-transform:uppercase;
    color:#bbb;
}
.card-value {
    font-size:20px;
    font-weight:bold;
    margin-top:6px;
}
.card-sub {
    font-size:11px;
    color:#888;
}
.section-title {
    margin-top:25px;
    font-size:16px;
    color:#ff6666;
}
table.report {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    font-size:13px;
}
table.report th, table.report td {
    border:1px solid #333;
    padding:6px 8px;
}
table.report th {
    background:#222;
    color:#ffdddd;
}
table.report tr:nth-child(even) { background:#181818; }
table.report tr:nth-child(odd) { background:#151515; }
td.num { text-align:right; }
.links-row {
    margin-top:15px;
    font-size:13px;
}
.links-row a {
    color:#ff6666;
    text-decoration:none;
    margin-right:10px;
}
.links-row a:hover {
    text-decoration:underline;
}
</style>

<div class="page">
    <h2>OEM Stock Dashboard</h2>

    <div class="dashboard-cards">
        <div class="card">
            <div class="card-label">OEM Parts</div>
            <div class="card-value"><?php echo (int)$totals['total_parts']; ?></div>
            <div class="card-sub">Unique OEM part records</div>
        </div>
        <div class="card">
            <div class="card-label">Total Units in Stock</div>
            <div class="card-value"><?php echo (int)$totals['total_units']; ?></div>
            <div class="card-sub">All OEM items on hand</div>
        </div>
        <div class="card">
            <div class="card-label">Stock Cost Value</div>
            <div class="card-value">R <?php echo number_format($totals['total_cost_value'], 2); ?></div>
            <div class="card-sub">Based on cost_price</div>
        </div>
        <div class="card">
            <div class="card-label">Potential Revenue</div>
            <div class="card-value">R <?php echo number_format($totals['total_revenue_value'], 2); ?></div>
            <div class="card-sub">If everything sells at selling_price</div>
        </div>
        <div class="card">
            <div class="card-label">Potential Profit</div>
            <div class="card-value">R <?php echo number_format($totals['total_potential_profit'], 2); ?></div>
            <div class="card-sub">Revenue minus cost</div>
        </div>
    </div>

    <div class="links-row">
        🔗 Quick links:
        <a href="oem_stock_alerts.php">Stock Alerts</a>
        <a href="oem_profit_report.php">Profit Report</a>
        <a href="oem_cost_analysis.php">Cost Analysis</a>
        <a href="oem_stock_movements.php">Stock Movements</a>
    </div>

    <div class="section-title">Top OEM Parts by Stock Quantity</div>
    <?php if ($resTop->num_rows === 0): ?>
        <p>No OEM parts found.</p>
    <?php else: ?>
        <table class="report">
            <thead>
                <tr>
                    <th>OEM #</th>
                    <th>Part Name</th>
                    <th>Stock Qty</th>
                    <th>Cost/Unit</th>
                    <th>Sell/Unit</th>
                    <th>Stock Cost Value</th>
                    <th>Stock Sell Value</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $resTop->fetch_assoc()): 
                    $stock_cost = $p['stock_qty'] * $p['cost_price'];
                    $stock_sell = $p['stock_qty'] * $p['selling_price'];
                ?>
                <tr>
                    <td><?php echo h($p['oem_number']); ?></td>
                    <td><?php echo h($p['part_name']); ?></td>
                    <td class="num"><?php echo (int)$p['stock_qty']; ?></td>
                    <td class="num"><?php echo number_format($p['cost_price'], 2); ?></td>
                    <td class="num"><?php echo number_format($p['selling_price'], 2); ?></td>
                    <td class="num">R <?php echo number_format($stock_cost, 2); ?></td>
                    <td class="num">R <?php echo number_format($stock_sell, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php
$conn->close();

