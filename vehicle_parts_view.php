<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$vehicle = $_GET['vehicle'] ?? '';

if($vehicle == ''){
    die("Vehicle not specified");
}

/* GET PARTS FOR VEHICLE */

$sql = "
SELECT
    sp.id,
    sp.part_name,
    sp.qty,
    sp.location,
    sp.date_stripped
FROM vehicle_stripped_parts sp
JOIN vehicles v ON v.id = sp.vehicle_id
WHERE v.stock_code = ?
ORDER BY sp.part_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s",$vehicle);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

<title>Vehicle Parts - <?= htmlspecialchars($vehicle ?? '') ?></title>

<style>

body{background:#000;color:#fff;font-family:Arial;}

.wrap{
width:80%;
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

/* QTY COLORS */

.qty-zero{color:#ff4444;font-weight:bold;}
.qty-last{color:#ffcc00;font-weight:bold;}
.qty-good{color:#00ff88;font-weight:bold;}

button{
padding:8px 12px;
background:#b00000;
border:none;
color:#fff;
cursor:pointer;
margin-bottom:15px;
}

</style>

</head>

<body>

<div class="wrap">

<h2>Vehicle <?= htmlspecialchars($vehicle ?? '') ?> Parts</h2>

<a href="stripped_list.php">
<button>← Back to Inventory</button>
</a>

<table>

<tr>
<th>Part</th>
<th>Qty</th>
<th>Location</th>
<th>Date</th>
</tr>

<?php while($row=$result->fetch_assoc()): ?>

<?php

/* SAFE VARIABLES (Prevents NULL errors) */

$part = $row['part_name'] ?? '';
$location = $row['location'] ?? '';
$date = $row['date_stripped'] ?? '';

$qty = (int)($row['qty'] ?? 0);

/* STOCK STATUS */

$qtyClass="qty-good";

if($qty==0){
    $qtyClass="qty-zero";
}
elseif($qty==1){
    $qtyClass="qty-last";
}

$icon="🟢";

if($qty==0){
    $icon="🔴";
}
elseif($qty==1){
    $icon="🟡";
}

?>

<tr>

<td><?= htmlspecialchars($part) ?></td>

<td class="<?= $qtyClass ?>">
<?= $icon ?> <?= $qty ?>
</td>

<td><?= htmlspecialchars($location) ?></td>

<td><?= htmlspecialchars($date) ?></td>

</tr>

<?php endwhile; ?>

</table>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>