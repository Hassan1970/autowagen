<?php

/*********************************************************
 * ADMIN – CATEGORY REVENUE REPORT
 * Phase 3.6.1 – FINAL
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- LOAD CATEGORY REVENUE ---------------- */

$sql = "
    SELECT
        c.name AS category_name,
        COUNT(si.id) AS items_sold,
        SUM(si.selling_price) AS total_revenue
    FROM stripped_inventory si
    JOIN categories c ON c.id = si.category_id
    WHERE si.sold_status = 'SOLD'
    GROUP BY si.category_id
    ORDER BY total_revenue DESC
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
<title>Category Revenue Report</title>

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

<h2>Category Revenue Report</h2>

<p>
This report shows <strong>actual revenue generated</strong> from sold stripped parts,
grouped by category.
</p>

<table>
<tr>
    <th>Category</th>
    <th>Items Sold</th>
    <th>Total Revenue</th>
</tr>

<?php if (empty($rows)): ?>
<tr>
    <td colspan="3">No sold items found.</td>
</tr>
<?php else: ?>

<?php
$grand_total = 0;
$total_items = 0;
?>

<?php foreach ($rows as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['category_name']) ?></td>
    <td><?= (int)$r['items_sold'] ?></td>
    <td><?= number_format($r['total_revenue'], 2) ?></td>
</tr>
<?php
$grand_total += $r['total_revenue'];
$total_items += $r['items_sold'];
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
