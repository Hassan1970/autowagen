<?php

/*********************************************************
 * ADMIN – DEAD STOCK IDENTIFICATION
 * Phase 3.6.4 – FINAL
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- LOAD DEAD STOCK ---------------- */

$sql = "
    SELECT
        c.name AS category_name,
        si.part_condition,
        si.selling_price,
        si.created_at,
        DATEDIFF(NOW(), si.created_at) AS days_in_stock
    FROM stripped_inventory si
    JOIN categories c ON c.id = si.category_id
    WHERE si.sold_status = 'AVAILABLE'
      AND si.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY days_in_stock DESC
";

$result = $conn->query($sql);

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dead Stock Report</title>

<style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
    th { background-color: #f4f4f4; text-align: center; }

    td:first-child,
    td:nth-child(2) { text-align: left; }

    .warning { color: red; font-weight: bold; }
</style>

</head>
<body>

<h2>Dead Stock Identification</h2>

<p>
This report shows parts that have been <strong>unsold for more than 30 days</strong>.
</p>

<table>
<tr>
    <th>Category</th>
    <th>Condition</th>
    <th>Selling Price</th>
    <th>Days in Stock</th>
    <th>Stripped Date</th>
</tr>

<?php if (empty($rows)): ?>
<tr>
    <td colspan="5">No dead stock found.</td>
</tr>
<?php else: ?>

<?php foreach ($rows as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['category_name']) ?></td>
    <td><?= htmlspecialchars($r['part_condition']) ?></td>
    <td><?= number_format($r['selling_price'], 2) ?></td>
    <td class="warning"><?= (int)$r['days_in_stock'] ?></td>
    <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
</tr>
<?php endforeach; ?>

<?php endif; ?>

</table>

</body>
</html>
