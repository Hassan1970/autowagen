<?php
require_once __DIR__ . '/../config/config.php';

/*
 Snapshot-based vehicle profit ranking
 Uses vehicle_profit_snapshots ONLY
*/

$sql = "
SELECT
    v.id AS vehicle_id,
    v.stock_code,
    v.make,
    v.model,
    COUNT(vps.id) AS snapshots,
    SUM(vps.stripped_revenue) AS total_revenue,
    SUM(vps.total_costs) AS total_costs,
    SUM(vps.profit) AS total_profit
FROM vehicles v
JOIN vehicle_profit_snapshots vps ON vps.vehicle_id = v.id
GROUP BY v.id, v.stock_code, v.make, v.model
ORDER BY total_profit DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vehicle Profit Ranking</title>

<style>
body {
    background:#000;
    color:#fff;
    font-family: Arial, Helvetica, sans-serif;
    margin:0;
}

.container {
    width:95%;
    margin:30px auto;
}

h1 {
    color:#ff2b2b;
    margin-bottom:15px;
}

.subtitle {
    color:#aaa;
    font-size:13px;
    margin-bottom:20px;
}

table {
    width:100%;
    border-collapse:collapse;
    font-size:14px;
}

th {
    background:#111;
    color:#ff2b2b;
    text-align:left;
    padding:12px 10px;
    border-bottom:2px solid #ff2b2b;
}

td {
    padding:10px;
    border-bottom:1px solid #222;
}

tr:hover {
    background:#111;
}

.rank {
    font-weight:bold;
    color:#ffcc00;
}

.money {
    text-align:right;
    font-family: monospace;
}

.profit-positive {
    color:#2ecc71;
    font-weight:bold;
}

.profit-negative {
    color:#ff4d4d;
    font-weight:bold;
}

.footer-note {
    margin-top:15px;
    font-size:12px;
    color:#888;
}
</style>
</head>

<body>

<div class="container">

<h1>Vehicle Profit Ranking</h1>
<div class="subtitle">
Ranked by total profit using locked vehicle profit snapshots.
</div>

<table>
<thead>
<tr>
    <th>#</th>
    <th>Stock Code</th>
    <th>Make</th>
    <th>Model</th>
    <th>Snapshots</th>
    <th class="money">Revenue (R)</th>
    <th class="money">Costs (R)</th>
    <th class="money">Profit (R)</th>
</tr>
</thead>
<tbody>

<?php
$rank = 1;
if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        $profitClass = ($row['total_profit'] >= 0)
            ? 'profit-positive'
            : 'profit-negative';
?>
<tr>
    <td class="rank"><?php echo $rank++; ?></td>
    <td><?php echo htmlspecialchars($row['stock_code']); ?></td>
    <td><?php echo htmlspecialchars($row['make']); ?></td>
    <td><?php echo htmlspecialchars($row['model']); ?></td>
    <td><?php echo (int)$row['snapshots']; ?></td>
    <td class="money">
        <?php echo number_format($row['total_revenue'], 2); ?>
    </td>
    <td class="money">
        <?php echo number_format($row['total_costs'], 2); ?>
    </td>
    <td class="money <?php echo $profitClass; ?>">
        <?php echo number_format($row['total_profit'], 2); ?>
    </td>
</tr>
<?php
    endwhile;
else:
?>
<tr>
    <td colspan="8">No snapshot data available.</td>
</tr>
<?php endif; ?>

</tbody>
</table>

<div class="footer-note">
Figures are derived exclusively from locked vehicle profit snapshots.  
No live recalculation is performed.
</div>

</div>

</body>
</html>
