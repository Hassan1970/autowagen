<?php
/*********************************************************
 * ADMIN – PRICING OVERVIEW REPORT
 * Phase 3.5.1
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- LOAD ACTIVE PRICING RULES ---------------- */

$pricing = [];

$sql = "
    SELECT 
        c.name AS category_name,
        pr.part_condition,
        pr.suggested_price,
        pr.created_at
    FROM pricing_rules pr
    JOIN categories c ON c.id = pr.category_id
    WHERE pr.is_active = 1
    ORDER BY c.name, pr.part_condition
";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $pricing[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pricing Overview</title>

<style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f4f4f4; }
</style>
</head>

<body>

<h2>Pricing Overview (Active Rules)</h2>

<p>
This report shows the <strong>current active pricing</strong> used by the system.
</p>

<table>
<tr>
    <th>Category</th>
    <th>Condition</th>
    <th>Active Price</th>
    <th>Set On</th>
</tr>

<?php if (empty($pricing)): ?>
<tr>
    <td colspan="4">No active pricing rules found.</td>
</tr>
<?php else: ?>
<?php foreach ($pricing as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['category_name']) ?></td>
    <td><?= htmlspecialchars($row['part_condition']) ?></td>
    <td><?= number_format($row['suggested_price'], 2) ?></td>
    <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</table>

</body>
</html>
