<?php

/*********************************************************
 * ADMIN – INVENTORY VALUE ON HAND
 * Phase 3.6.6 – FINAL
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- LOAD INVENTORY VALUE ---------------- */

$sql = "
    SELECT
        c.name AS category_name,
        COUNT(si.id) AS items_in_stock,
        SUM(si.selling_price) AS stock_value
    FROM stripped_inventory si
    JOIN categories c ON c.id = si.category_id
    WHERE si.sold_status = 'AVAILABLE'
    GROUP BY si.category_id
    ORDER BY stock_value DESC
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
<title>Inventory Value on Hand</title>

<style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
    th { background-color: #f4f4f4; text-align: center; }

    td:first-child { text-align: left; }
    .total { font-weight: bold; }
</style>

</head>
<body>

<h2>Inventory Value on Hand</h2>

<p>
This report shows the <strong>current value of unsold stripped parts</strong>,
based on your selling prices.
</p>

<table>
<tr>
    <th>Category</th>
    <th>Items in Stock</th>
    <th>Stock Value</th>
</tr>

<?php if (empty($rows)): ?>
<tr>
    <td colspan="3">No inventory found.</td>
</tr>
<?php else: ?>

<?php
$grand_total = 0;
$total_items = 0;
?>

<?php foreach ($rows as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['category_name']) ?></td>
    <td><?= (int)$r['items_in_stock'] ?></td>
    <td><?= number_format($r['stock_value'], 2) ?></td>
</tr>
<?php
$grand_total += $r['stock_value'];
$total_items += $r['items_in_stock'];
?>
<?php endforeach; ?>

<tr class="total">
    <td>TOTAL</td>
    <td><?= $total_items ?></td>
    <td><?= number_format($grand_total, 2) ?></td>
</tr>

<?php endif; ?>

</table>

</body>
</html>
