<?php
require_once "config/config.php";
include "includes/header.php";

// Helper for safe output
if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Date range (default: last 30 days)
$from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-30 days'));
$to   = isset($_GET['to'])   ? $_GET['to']   : date('Y-m-d');

$from_dt = $from . ' 00:00:00';
$to_dt   = $to . ' 23:59:59';

$sql = "
    SELECT 
        p.id,
        p.oem_number,
        p.part_name,
        p.stock_qty,
        p.cost_price,
        p.selling_price,
        SUM(sm.qty) AS sold_qty,
        SUM(sm.qty * p.cost_price) AS total_cost,
        SUM(sm.qty * p.selling_price) AS total_revenue,
        SUM(sm.qty * (p.selling_price - p.cost_price)) AS total_profit
    FROM stock_movements sm
    INNER JOIN oem_parts p ON p.id = sm.part_id
    WHERE sm.movement_type = 'SALE'
      AND sm.movement_date BETWEEN ? AND ?
    GROUP BY p.id, p.oem_number, p.part_name, p.stock_qty, p.cost_price, p.selling_price
    ORDER BY total_profit DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $from_dt, $to_dt);
$stmt->execute();
$result = $stmt->get_result();

$grand_revenue = 0;
$grand_cost    = 0;
$grand_profit  = 0;
?>
<style>
.page {
    width: 95%;
    margin: 20px auto;
    background: #111;
    border: 2px solid #b00000;
    color: #fff;
    padding: 20px;
    border-radius: 8px;
}
.page h2 {
    text-align: center;
    color: #ff3333;
    margin-bottom: 20px;
}
.page form {
    display: flex;
    gap: 10px;
    justify-content: center;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
}
.page label {
    font-weight: bold;
    color: #ff6666;
}
.page input[type="date"] {
    background:#222;
    color:#fff;
    border:1px solid #444;
    padding:5px 8px;
    border-radius:4px;
}
.page button {
    background:#b00000;
    color:#fff;
    border:none;
    padding:6px 12px;
    border-radius:5px;
    cursor:pointer;
}
.page button:hover {
    background:#ff3333;
}
.summary-bar {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
    margin:15px 0;
}
.summary-card {
    flex:1 1 200px;
    background:#181818;
    border:1px solid #333;
    border-radius:8px;
    padding:10px 15px;
}
.summary-card .label {
    color:#bbb;
    font-size:12px;
    text-transform:uppercase;
}
.summary-card .value {
    font-size:18px;
    font-weight:bold;
    margin-top:5px;
}
table.report {
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
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
table.report tr:nth-child(even) {
    background:#181818;
}
table.report tr:nth-child(odd) {
    background:#151515;
}
td.num {
    text-align:right;
}
.badge {
    display:inline-block;
    padding:2px 6px;
    border-radius:4px;
    font-size:11px;
}
.badge-profit {
    background:#054d0a;
    color:#a9ffb0;
}
.badge-loss {
    background:#5a0505;
    color:#ffc2c2;
}
.notice {
    margin-top:12px;
    font-size:12px;
    color:#999;
    font-style:italic;
}
</style>

<div class="page">
    <h2>OEM Profit Report</h2>

    <form method="get">
        <label>From:
            <input type="date" name="from" value="<?php echo h($from); ?>">
        </label>
        <label>To:
            <input type="date" name="to" value="<?php echo h($to); ?>">
        </label>
        <button type="submit">Filter</button>
    </form>

    <?php if ($result->num_rows === 0): ?>
        <p>No sales found in this period. Once we start logging OEM <strong>SALE</strong> movements, this report will populate.</p>
    <?php else: ?>
        <?php
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $grand_revenue += $row['total_revenue'];
            $grand_cost    += $row['total_cost'];
            $grand_profit  += $row['total_profit'];
            $rows[] = $row;
        }
        ?>
        <div class="summary-bar">
            <div class="summary-card">
                <div class="label">Total Revenue</div>
                <div class="value">R <?php echo number_format($grand_revenue, 2); ?></div>
            </div>
            <div class="summary-card">
                <div class="label">Total Cost</div>
                <div class="value">R <?php echo number_format($grand_cost, 2); ?></div>
            </div>
            <div class="summary-card">
                <div class="label">Total Profit</div>
                <div class="value">R <?php echo number_format($grand_profit, 2); ?></div>
            </div>
        </div>

        <table class="report">
            <thead>
                <tr>
                    <th>OEM #</th>
                    <th>Part Name</th>
                    <th>Sold Qty</th>
                    <th>Cost/Unit</th>
                    <th>Sell/Unit</th>
                    <th>Total Cost</th>
                    <th>Total Revenue</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <?php
                $badgeClass = $r['total_profit'] >= 0 ? 'badge-profit' : 'badge-loss';
                $badgeText  = $r['total_profit'] >= 0 ? 'Profit' : 'Loss';
                ?>
                <tr>
                    <td><?php echo h($r['oem_number']); ?></td>
                    <td><?php echo h($r['part_name']); ?></td>
                    <td class="num"><?php echo (int)$r['sold_qty']; ?></td>
                    <td class="num"><?php echo number_format($r['cost_price'], 2); ?></td>
                    <td class="num"><?php echo number_format($r['selling_price'], 2); ?></td>
                    <td class="num"><?php echo number_format($r['total_cost'], 2); ?></td>
                    <td class="num"><?php echo number_format($r['total_revenue'], 2); ?></td>
                    <td class="num">
                        <span class="badge <?php echo $badgeClass; ?>">
                            <?php echo $badgeText; ?>
                        </span>
                        &nbsp;R <?php echo number_format($r['total_profit'], 2); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p class="notice">
        * This report uses <strong>stock_movements (SALE)</strong> + prices on <strong>oem_parts</strong>.
        Once we turn on SELL_OUT logic, this will come alive.
    </p>
</div>
<?php
$stmt->close();
$conn->close();
