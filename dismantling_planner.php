<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* =========================
   VEHICLES WITH PARTS LEFT
========================= */

$sql = "
SELECT
    v.stock_code,
    COUNT(sp.id) AS parts_remaining
FROM vehicles v
LEFT JOIN vehicle_stripped_parts sp
    ON sp.vehicle_id = v.id
    AND sp.qty > 0
GROUP BY v.id
ORDER BY parts_remaining DESC, v.stock_code ASC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
<title>Smart Vehicle Dismantling Planner</title>

<style>

body{
background:#000;
color:#fff;
font-family:Arial;
}

.wrap{
width:90%;
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

.vehicle-link{
color:#4da6ff;
text-decoration:none;
font-weight:bold;
}

.vehicle-link:hover{
text-decoration:underline;
}

.parts-high{
color:#00ff88;
font-weight:bold;
}

.parts-medium{
color:#ffcc00;
font-weight:bold;
}

.parts-low{
color:#ff4444;
font-weight:bold;
}

</style>

</head>

<body>

<div class="wrap">

<h2>Smart Vehicle Dismantling Planner</h2>

<table>

<tr>
<th>Vehicle</th>
<th>Parts Remaining</th>
<th>Action</th>
</tr>

<?php if($result && $result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<?php

$vehicle = $row['stock_code'] ?? '';
$parts = (int)($row['parts_remaining'] ?? 0);

/* COLOR PRIORITY */

$class = "parts-low";

if($parts >= 10){
$class = "parts-high";
}
elseif($parts >= 5){
$class = "parts-medium";
}

?>

<tr>

<td>

<a
class="vehicle-link"
href="vehicle_parts_view.php?vehicle=<?= urlencode($vehicle) ?>"
>

<?= htmlspecialchars($vehicle) ?>

</a>

</td>

<td class="<?= $class ?>">

<?= $parts ?>

</td>

<td>

<a
class="vehicle-link"
href="vehicle_parts_view.php?vehicle=<?= urlencode($vehicle) ?>"
>

View Parts

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="3" style="text-align:center;color:#888;">
No vehicles found
</td>
</tr>

<?php endif; ?>

</table>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>