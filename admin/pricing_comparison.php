<?php

/*********************************************************
 * ADMIN – PRICING HISTORY & TREND REPORT
 * Phase 3.5.3 – FINAL
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- LOAD PRICING HISTORY ---------------- */

$sql = "
    SELECT 
        c.name AS category_name,
        pr.part_condition,
        pr.suggested_price,
        pr.is_active,
        pr.created_at
    FROM pricing_rules pr
    JOIN categories c ON c.id = pr.category_id
    ORDER BY c.name, pr.part_condition, pr.created_at DESC
";

$result = $conn->query($sql);

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pricing History & Trends</title>

<style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f4f4f4; }
    .active { color: green; font-weight: bold; }
    .inactive { color: #999; }
</style>

</head>
<body>

<h2>Pricing History & Trends</h2>

<p>
This report shows <strong>all pricing rules over time</strong>, including inactive (historical) rules.
</p>

<table>
<tr>
    <th>Category</th>
    <th>Condition</th>
    <th>Price</th>
    <th>Status</th>
    <th>Date Added</th>
</tr>

<?php if (empty($history)): ?>
<tr>
    <td colspan="5">No pricing history found.</td>
</tr>
<?php else: ?>
<?php foreach ($history as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['category_name']) ?></td>
    <td><?= htmlspecialchars($row['part_condition']) ?></td>
    <td><?= number_format($row['suggested_price'], 2) ?></td>
    <td class="<?= $row['is_active'] ? 'active' : 'inactive' ?>">
        <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
    </td>
    <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</table>

</body>
</html>
