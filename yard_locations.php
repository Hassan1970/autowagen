<?php
$page_title = "Yard Locations";
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* ==========================================
   GET LOCATION SUMMARY
========================================== */

$sql = "
SELECT 
    yl.code AS location,
    COUNT(vsp.id) AS parts_count,
    COALESCE(SUM(vsp.qty),0) AS total_qty
FROM yard_locations yl
LEFT JOIN vehicle_stripped_parts vsp 
    ON vsp.location = yl.code
GROUP BY yl.code
ORDER BY yl.code ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>

<style>

.wrap{
width:95%;
margin:25px auto;
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

tr:hover{
background:#1a0000;
cursor:pointer;
}

.empty{
text-align:center;
color:#888;
padding:20px;
}

</style>

</head>

<body>

<div class="wrap">

<h2>Yard Locations</h2>

<table>

<tr>
<th>Location</th>
<th>Total Parts</th>
<th>Total Quantity</th>
</tr>

<?php if($result && $result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr onclick="window.location='location_parts.php?location=<?= urlencode($row['location']) ?>'">

<td><?= htmlspecialchars($row['location']) ?></td>

<td><?= (int)$row['parts_count'] ?></td>

<td><?= (int)$row['total_qty'] ?></td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="3" class="empty">No locations found</td>
</tr>

<?php endif; ?>

</table>

</div>

</body>
</html>

<?php include __DIR__ . "/includes/footer.php"; ?>