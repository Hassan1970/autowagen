<?php

/*********************************************************
 * ADMIN – SUGGESTED vs ACTUAL SELLING PRICE
 * Phase 3.6.2 – FINAL
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- LOAD SOLD ITEMS WITH PRICING ---------------- */

$sql = "
    SELECT
        si.id,
        c.name AS category_name,
        si.part_condition,
        pr.suggested_price,
        si.selling_price,
        (si.selling_price - pr.suggested_price) AS price_diff,
        si.created_at
    FROM stripped_inventory si
    JOIN categories c 
        ON c.id = si.category_id
    LEFT JOIN pricing_rules pr
        ON pr.category_id = si.category_id
       AND pr.part_condition = si.part_condition
       AND pr.is_active = 1
    WHERE si.sold_status = 'SOLD'
    ORDER BY si.created_at DESC
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
<title>Suggested vs Actual Selling Price</title>

<style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
    th { background-color: #f4f4f4; text-align: center; }
    td:first-child,
    td:nth-child(2),
    td:nth-child(3) { text-align: left; }

    .positive { color: green; font-weight: bold; }
    .negative { color: red; font-weight: bold; }
    .neutral  { color: #555; }
</style>

</head>
<body>

<h2>Suggested vs Actual Selling Price</h2>

<p>
This report compares <strong>suggested pricing</strong> with <strong>actual selling prices</strong>
for sold parts.
</p>

<table>
<tr>
    <th>Category</th>
    <th>Condition</th>
    <th>Suggested Price</th>
    <th>Actual Price</th>
    <th>Difference</th>
    <th>Date Sold</th>
</tr>

<?php if (empty($rows)): ?>
<tr>
    <td colspan="6">No sold items found.</td>
</tr>
<?php else: ?>

<?php foreach ($rows as $r): ?>
<?php
    if ($r['suggested_price'] === null) {
        $diffClass = 'neutral';
        $diffLabel = '—';
    } elseif ($r['price_diff'] > 0) {
        $diffClass = 'positive';
        $diffLabel = number_format($r['price_diff'], 2);
    } elseif ($r['price_diff'] < 0) {
        $diffClass = 'negative';
        $diffLabel = number_format($r['price_diff'], 2);
    } else {
        $diffClass = 'neutral';
        $diffLabel = '0.00';
    }
?>
<tr>
    <td><?= htmlspecialchars($r['category_name']) ?></td>
    <td><?= htmlspecialchars($r['part_condition']) ?></td>
    <td><?= $r['suggested_price'] !== null ? number_format($r['suggested_price'], 2) : '—' ?></td>
    <td><?= number_format($r['selling_price'], 2) ?></td>
    <td class="<?= $diffClass ?>"><?= $diffLabel ?></td>
    <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
</tr>
<?php endforeach; ?>

<?php endif; ?>

</table>

</body>
</html>
