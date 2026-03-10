<?php

/*********************************************************
 * ADMIN – FAST vs SLOW MOVING PARTS
 * Phase 3.6.3 – FIXED (NO sold_at COLUMN)
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- LOAD SOLD PARTS WITH SPEED ---------------- */

$sql = "
    SELECT
        c.name AS category_name,
        si.part_condition,
        si.selling_price,
        si.created_at AS stripped_date,
        DATEDIFF(NOW(), si.created_at) AS days_to_sell
    FROM stripped_inventory si
    JOIN categories c ON c.id = si.category_id
    WHERE si.sold_status = 'SOLD'
    ORDER BY days_to_sell ASC
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
<title>Fast vs Slow Moving Parts</title>

<style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
    th { background-color: #f4f4f4; text-align: center; }

    td:first-child,
    td:nth-child(2) { text-align: left; }

    .fast { color: green; font-weight: bold; }
    .medium { color: #d39e00; font-weight: bold; }
    .slow { color: red; font-weight: bold; }
</style>

</head>
<body>

<h2>Fast vs Slow Moving Parts</h2>

<p>
This report shows how long parts take to sell after being stripped.
</p>

<table>
<tr>
    <th>Category</th>
    <th>Condition</th>
    <th>Selling Price</th>
    <th>Days Since Stripped</th>
    <th>Speed</th>
    <th>Stripped Date</th>
</tr>

<?php if (empty($rows)): ?>
<tr>
    <td colspan="6">No sold parts found.</td>
</tr>
<?php else: ?>

<?php foreach ($rows as $r): ?>
<?php
    if ($r['days_to_sell'] <= 7) {
        $speed = 'Fast';
        $class = 'fast';
    } elseif ($r['days_to_sell'] <= 30) {
        $speed = 'Medium';
        $class = 'medium';
    } else {
        $speed = 'Slow';
        $class = 'slow';
    }
?>
<tr>
    <td><?= htmlspecialchars($r['category_name']) ?></td>
    <td><?= htmlspecialchars($r['part_condition']) ?></td>
    <td><?= number_format($r['selling_price'], 2) ?></td>
    <td><?= (int)$r['days_to_sell'] ?></td>
    <td class="<?= $class ?>"><?= $speed ?></td>
    <td><?= date('Y-m-d', strtotime($r['stripped_date'])) ?></td>
</tr>
<?php endforeach; ?>

<?php endif; ?>

</table>

</body>
</html>
