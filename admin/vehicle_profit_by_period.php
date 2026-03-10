<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

/**
 * period = month | year
 * default = month
 */
$period = $_GET['period'] ?? 'month';

if ($period === 'year') {
    $sql = "
        SELECT
            YEAR(snapshot_date) AS period,
            COUNT(DISTINCT vehicle_id) AS vehicles_closed,
            SUM(stripped_revenue) AS total_revenue,
            SUM(total_costs) AS total_costs,
            SUM(profit) AS total_profit
        FROM vehicle_profit_snapshots
        GROUP BY period
        ORDER BY period DESC
    ";
    $title = "Vehicle Profitability by Year (Snapshot-Based)";
} else {
    $sql = "
        SELECT
            DATE_FORMAT(snapshot_date, '%Y-%m') AS period,
            COUNT(DISTINCT vehicle_id) AS vehicles_closed,
            SUM(stripped_revenue) AS total_revenue,
            SUM(total_costs) AS total_costs,
            SUM(profit) AS total_profit
        FROM vehicle_profit_snapshots
        GROUP BY period
        ORDER BY period DESC
    ";
    $title = "Vehicle Profitability by Month (Snapshot-Based)";
}

$result = $conn->query($sql);
if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        h1 {
            color: red;
        }
        .filters a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }
        .filters a.active {
            color: red;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid red;
            padding: 8px;
            text-align: right;
        }
        th {
            color: red;
        }
        td:first-child, th:first-child {
            text-align: left;
        }
        .profit-positive {
            color: #00ff88;
            font-weight: bold;
        }
        .profit-negative {
            color: #ff4444;
            font-weight: bold;
        }
    </style>
</head>

<body>

<h1><?= htmlspecialchars($title) ?></h1>

<div class="filters">
    <a href="?period=month" class="<?= $period === 'month' ? 'active' : '' ?>">Monthly</a>
    <a href="?period=year" class="<?= $period === 'year' ? 'active' : '' ?>">Yearly</a>
</div>

<table>
    <tr>
        <th>Period</th>
        <th>Vehicles Closed</th>
        <th>Total Revenue (R)</th>
        <th>Total Costs (R)</th>
        <th>Total Profit (R)</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['period']) ?></td>
            <td><?= number_format($row['vehicles_closed']) ?></td>
            <td><?= number_format($row['total_revenue'], 2) ?></td>
            <td><?= number_format($row['total_costs'], 2) ?></td>
            <td class="<?= ($row['total_profit'] >= 0) ? 'profit-positive' : 'profit-negative' ?>">
                <?= number_format($row['total_profit'], 2) ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<p style="margin-top:10px; font-size:12px; color:#aaa;">
    Figures are derived exclusively from locked vehicle profit snapshots.
    No live recalculation is performed.
</p>

</body>
</html>
