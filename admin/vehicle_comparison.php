<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';

$sql = "
    SELECT
        v.id AS vehicle_id,
        v.stock_code,
        v.make,
        v.model,
        COUNT(vps.id) AS snapshots,
        COALESCE(SUM(vps.stripped_revenue), 0) AS total_revenue,
        COALESCE(SUM(vps.total_costs), 0) AS total_costs,
        COALESCE(SUM(vps.profit), 0) AS total_profit
    FROM vehicles v
    LEFT JOIN vehicle_profit_snapshots vps
        ON vps.vehicle_id = v.id
    GROUP BY v.id, v.stock_code, v.make, v.model
    ORDER BY total_profit DESC
";

$result = $conn->query($sql);
?>

<div class="container">
    <h2 style="color:red;">Vehicle Comparison (Snapshot-Based)</h2>

    <table class="table">
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Stock Code</th>
                <th>Make</th>
                <th>Model</th>
                <th>Snapshots</th>
                <th>Total Revenue (R)</th>
                <th>Total Costs (R)</th>
                <th>Total Profit (R)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$row['vehicle_id'] ?></td>
                        <td><?= htmlspecialchars($row['stock_code']) ?></td>
                        <td><?= htmlspecialchars($row['make']) ?></td>
                        <td><?= htmlspecialchars($row['model']) ?></td>
                        <td><?= (int)$row['snapshots'] ?></td>
                        <td>R <?= number_format($row['total_revenue'], 2) ?></td>
                        <td>R <?= number_format($row['total_costs'], 2) ?></td>
                        <td style="color:<?= $row['total_profit'] >= 0 ? 'lime' : 'red' ?>">
                            R <?= number_format($row['total_profit'], 2) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No snapshot data found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="color:#888;font-size:12px;">
        Figures are derived exclusively from locked vehicle profit snapshots.
        No live recalculation is performed.
    </p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
