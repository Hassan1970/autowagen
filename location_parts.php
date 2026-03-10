<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* ==========================================
   GET LOCATION
========================================== */

$location = trim($_GET['location'] ?? '');

if ($location === '') {
    die("Invalid location");
}

/* ==========================================
   FETCH PARTS IN THIS LOCATION
========================================== */

$sql = "
SELECT
    sp.id,
    v.stock_code,
    sp.part_name,
    sp.qty,
    sp.location,
    sp.date_stripped
FROM vehicle_stripped_parts sp
JOIN vehicles v ON v.id = sp.vehicle_id
WHERE sp.location = ?
ORDER BY sp.part_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$location);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>

<title>Location <?= htmlspecialchars($location) ?> Parts</title>

<style>

body{background:#000;color:#fff;font-family:Arial;}

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
font-size:13px;
}

th,td{
border:1px solid #333;
padding:10px;
text-align:left;
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
}

.empty{
text-align:center;
color:#888;
padding:20px;
}

/* QTY COLORS */

.qty-zero{
color:#ff4444;
font-weight:bold;
}

.qty-last{
color:#ffcc00;
font-weight:bold;
}

.qty-good{
color:#00ff88;
font-weight:bold;
}

.legend{
margin-bottom:10px;
}

button{
padding:8px 12px;
background:#b00000;
border:none;
color:#fff;
cursor:pointer;
margin-bottom:15px;
margin-right:10px;
}

</style>

</head>

<body>

<div class="wrap">

<h2>Location <?= htmlspecialchars($location) ?> Parts</h2>

<div class="legend">
🔴 OUT &nbsp;&nbsp; 🟡 LAST &nbsp;&nbsp; 🟢 IN STOCK
</div>

<a href="yard_locations.php">
<button>← Back to Yard Locations</button>
</a>

<!-- NEW BUTTON ADDED -->
<a href="yard_map.php">
<button>← Back to Yard Map</button>
</a>

<table>

<tr>
<th>Vehicle</th>
<th>Part</th>
<th>Qty</th>
<th>Date</th>
</tr>

<?php if($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<?php

$qty = (int)($row['qty'] ?? 0);

$qtyClass = "qty-good";

if($qty == 0){
$qtyClass = "qty-zero";
}
elseif($qty == 1){
$qtyClass = "qty-last";
}

?>

<tr>

<td><?= htmlspecialchars($row['stock_code'] ?? '') ?></td>

<td><?= htmlspecialchars($row['part_name'] ?? '') ?></td>

<td class="<?= $qtyClass ?>">
<?php

$icon = "🟢";

if($qty == 0){
$icon = "🔴";
}
elseif($qty == 1){
$icon = "🟡";
}

echo $icon . " " . $qty;

?>
</td>

<td><?= htmlspecialchars($row['date_stripped'] ?? '') ?></td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="4" class="empty">No Parts Found In This Location</td>
</tr>

<?php endif; ?>

</table>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>