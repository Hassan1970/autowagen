<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* =============================
   VEHICLE PROFIT ESTIMATION
============================= */

$sql = "

SELECT
    v.stock_code,
    COUNT(sp.id) AS parts_remaining,
    SUM(
        IFNULL(avg_sales.avg_price,0) * sp.qty
    ) AS estimated_value

FROM vehicles v

LEFT JOIN vehicle_stripped_parts sp
ON sp.vehicle_id = v.id
AND sp.qty > 0

LEFT JOIN (

    SELECT
        part_name,
        AVG(cost_price) AS avg_price
    FROM invoice_items
    GROUP BY part_name

) avg_sales

ON avg_sales.part_name = sp.part_name

GROUP BY v.id
ORDER BY estimated_value DESC

";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>

<title>Vehicle Profit Score</title>

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

.vehicle{
color:#4da6ff;
font-weight:bold;
}

.value-high{
color:#00ff88;
font-weight:bold;
}

.value-medium{
color:#ffcc00;
font-weight:bold;
}

.value-low{
color:#ff4444;
font-weight:bold;
}

</style>

</head>

<body>

<div class="wrap">

<h2>Vehicle Profit Score</h2>

<table>

<tr>
<th>Vehicle</th>
<th>Parts Remaining</th>
<th>Estimated Value</th>
<th>Action</th>
</tr>

<?php while($row=$result->fetch_assoc()): ?>

<?php

$vehicle = $row['stock_code'] ?? '';
$parts = (int)($row['parts_remaining'] ?? 0);
$value = (float)($row['estimated_value'] ?? 0);

/* VALUE COLOR */

$class = "value-low";

if($value >= 10000){
$class="value-high";
}
elseif($value >= 5000){
$class="value-medium";
}

?>

<tr>

<td class="vehicle">

<?= htmlspecialchars($vehicle) ?>

</td>

<td>

<?= $parts ?>

</td>

<td class="<?= $class ?>">

R <?= number_format($value,2) ?>

</td>

<td>

<a href="vehicle_parts_view.php?vehicle=<?= urlencode($vehicle) ?>" style="color:#4da6ff">

View Parts

</a>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>