<?php
$page_title = "Part History";

require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$part = trim($_GET['part'] ?? '');

if($part == ''){
die("Invalid Part");
}

$stmt = $conn->prepare("

SELECT
v.stock_code,
sp.qty,
sp.location,
sp.date_stripped

FROM vehicle_stripped_parts sp
JOIN vehicles v ON v.id = sp.vehicle_id

WHERE sp.part_name = ?

ORDER BY sp.date_stripped DESC

");

$stmt->bind_param("s",$part);
$stmt->execute();

$result = $stmt->get_result();

?>

<style>

.wrap{
width:95%;
margin:25px auto;
}

h2{
color:#ff3333;
margin-bottom:15px;
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

</style>

<div class="wrap">

<h2>Part History: <?= htmlspecialchars($part) ?></h2>

<a href="inventory.php">
<button style="padding:8px 12px;background:#b00000;color:#fff;border:none;">
← Back to Inventory
</button>
</a>

<table>

<tr>
<th>Vehicle</th>
<th>Qty</th>
<th>Location</th>
<th>Date Stripped</th>
</tr>

<?php if($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['stock_code']) ?></td>

<td><?= (int)$row['qty'] ?></td>

<td><?= htmlspecialchars($row['location']) ?></td>

<td><?= htmlspecialchars($row['date_stripped']) ?></td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="4">No history found</td>
</tr>

<?php endif; ?>

</table>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>