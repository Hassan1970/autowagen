<?php
require_once __DIR__ . '/../config/config.php';

$sql = "
SELECT 
    v.id AS vehicle_id,

    COALESCE(sr.stripped_revenue, 0) AS stripped_revenue,
    COALESCE(sc.total_costs, 0) AS total_costs,

    (COALESCE(sr.stripped_revenue, 0) - COALESCE(sc.total_costs, 0)) AS vehicle_profit

FROM vehicles v

/* ================= REVENUE ================= */
LEFT JOIN (
    SELECT 
        si.vehicle_id,
        SUM(pii.quantity * pii.price) AS stripped_revenue
    FROM pos_invoice_items pii
    JOIN stripped_inventory si ON si.id = pii.part_id
    WHERE pii.part_type = 'STRIPPED'
    GROUP BY si.vehicle_id
) sr ON sr.vehicle_id = v.id

/* ================= COSTS ================= */
LEFT JOIN (
    SELECT 
        siv.vehicle_id,
        SUM(sii.cost_price * sii.stock_qty) AS total_costs
    FROM suppliers_invoice_item_vehicles siv
    JOIN suppliers_invoice_items sii ON sii.id = siv.item_id
    GROUP BY siv.vehicle_id
) sc ON sc.vehicle_id = v.id

ORDER BY v.id DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Profitability</title>
    <style>
        body { background:#000; color:#fff; font-family:Arial; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #333; padding:10px; text-align:right; }
        th { background:#111; color:red; }
        th:first-child, td:first-child { text-align:left; }
        .pos { color:#00ff88; font-weight:bold; }
        .neg { color:#ff4444; font-weight:bold; }
    </style>
</head>
<body>

<h2>🚗 Vehicle Profitability</h2>

<table>
<tr>
    <th>Vehicle ID</th>
    <th>Stripped Revenue</th>
    <th>Total Costs</th>
    <th>Profit</th>
</tr>

<?php if ($result && $result->num_rows > 0): ?>
<?php while ($r = $result->fetch_assoc()): ?>
<tr>
    <td><?= (int)$r['vehicle_id'] ?></td>
    <td>R <?= number_format($r['stripped_revenue'], 2) ?></td>
    <td>R <?= number_format($r['total_costs'], 2) ?></td>
    <td class="<?= $r['vehicle_profit'] >= 0 ? 'pos' : 'neg' ?>">
        R <?= number_format($r['vehicle_profit'], 2) ?>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No data found</td></tr>
<?php endif; ?>

</table>

</body>
</html>
