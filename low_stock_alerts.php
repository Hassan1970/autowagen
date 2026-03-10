<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* GET LOW STOCK PARTS */

$sql = "
SELECT
v.stock_code,
sp.part_name,
sp.qty,
sp.location
FROM vehicle_stripped_parts sp
JOIN vehicles v
ON v.id = sp.vehicle_id
WHERE sp.qty <= 1
ORDER BY sp.qty ASC, sp.part_name ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>

<title>Low Stock Alerts</title>

<style>

body{
background:#000;
color:#fff;
font-family:Arial;
}

.wrap{
width:95%;
margin:30px auto;
}

h2{
color:#ff3333;
margin-bottom:20px;
}

table{
width:100%;
border-collapse:collapse;
font-size:14px;
}

th,td{
border:1px solid #333;
padding:10px;
}

th{
background:#111;
color:#ff3333;
}

tr:nth-child(even){
background:#0b0b0b;
}

/* STOCK COLORS */

.qty-zero{
color:#ff4444;
font-weight:bold;
}

.qty-last{
color:#ffcc00;
font-weight:bold;
}

</style>

</head>

<body>

<div class="wrap">

<h2>LOW STOCK ALERTS</h2>

<table>

<tr>
<th>Vehicle</th>
<th>Part</th>
<th>Qty</th>
<th>Location</th>
</tr>

<?php if($result && $result->num_rows > 0): ?>

<?php while($row=$result->fetch_assoc()): ?>

<?php

$vehicle = $row['stock_code'] ?? '';
$part    = $row['part_name'] ?? '';
$location= $row['location'] ?? '';

$qty = (int)($row['qty'] ?? 0);

$icon="🟡";
$class="qty-last";

if($qty==0){
$icon="🔴";
$class="qty-zero";
}

?>

<tr>

<td><?= htmlspecialchars($vehicle) ?></td>

<td><?= htmlspecialchars($part) ?></td>

<td class="<?= $class ?>">
<?= $icon ?> <?= $qty ?>
</td>

<td><?= htmlspecialchars($location) ?></td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="4" style="text-align:center;color:#888;">
No Low Stock Parts
</td>
</tr>

<?php endif; ?>

</table>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>