```php
<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

/* ===============================
OEM LOW STOCK
=============================== */
$oem = $conn->query("
SELECT part_name, stock_qty
FROM oem_parts
WHERE stock_qty <= 3
ORDER BY stock_qty ASC
");

/* ===============================
REPLACEMENT LOW STOCK
=============================== */
$replacement = $conn->query("
SELECT part_name, stock_qty
FROM replacement_parts
WHERE stock_qty <= 3
ORDER BY stock_qty ASC
");

/* ===============================
THIRD PARTY SOLD
=============================== */
$third = $conn->query("
SELECT description, stock_status
FROM third_party_parts
WHERE stock_status != 'IN_STOCK'
");

/* ===============================
STRIPPED PARTS LOW
=============================== */
$stripped = $conn->query("
SELECT part_name, qty
FROM vehicle_stripped_parts
WHERE qty <= 1
ORDER BY qty ASC
");

$oemCount = $oem ? $oem->num_rows : 0;
$repCount = $replacement ? $replacement->num_rows : 0;
$thirdCount = $third ? $third->num_rows : 0;
$stripCount = $stripped ? $stripped->num_rows : 0;

?>

<!DOCTYPE html>
<html>
<head>
<title>Inventory Alerts Center</title>

<style>

body{
background:#000;
color:#fff;
font-family:Arial;
}

.wrap{
width:95%;
margin:auto;
padding:30px;
}

h1{
color:#ff2e2e;
margin-bottom:30px;
}

.cards{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-bottom:40px;
}

.card{
background:#111;
padding:25px;
border-radius:10px;
border:2px solid #222;
text-align:center;
}

.card h2{
color:#00ff9c;
margin:0;
}

.card p{
color:#aaa;
margin-top:5px;
}

.section{
margin-bottom:40px;
}

.section h2{
color:#ff2e2e;
margin-bottom:10px;
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
}

.low{color:#ffcc00;}
.last{color:#ff4444;font-weight:bold;}
.out{color:#ff0000;font-weight:bold;}

</style>
</head>

<body>

<div class="wrap">

<h1>⚠ Inventory Alerts Center</h1>

<div class="cards">

<div class="card">
<h2><?php echo $oemCount; ?></h2>
<p>OEM Low Stock</p>
</div>

<div class="card">
<h2><?php echo $repCount; ?></h2>
<p>Replacement Low</p>
</div>

<div class="card">
<h2><?php echo $thirdCount; ?></h2>
<p>Third Party Sold</p>
</div>

<div class="card">
<h2><?php echo $stripCount; ?></h2>
<p>Stripped Parts Left</p>
</div>

</div>


<!-- OEM -->

<div class="section">

<h2>OEM PARTS — LOW STOCK</h2>

<table>

<tr>
<th>Part Name</th>
<th>Qty</th>
<th>Status</th>
</tr>

<?php if($oem) while($row = $oem->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['part_name']) ?></td>
<td><?= $row['stock_qty'] ?></td>

<td>

<?php
if($row['stock_qty']==0) echo "<span class='out'>OUT</span>";
elseif($row['stock_qty']==1) echo "<span class='last'>LAST</span>";
else echo "<span class='low'>LOW</span>";
?>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>



<!-- REPLACEMENT -->

<div class="section">

<h2>REPLACEMENT PARTS — LOW STOCK</h2>

<table>

<tr>
<th>Part Name</th>
<th>Qty</th>
<th>Status</th>
</tr>

<?php if($replacement) while($row = $replacement->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['part_name']) ?></td>
<td><?= $row['stock_qty'] ?></td>

<td>

<?php
if($row['stock_qty']==0) echo "<span class='out'>OUT</span>";
elseif($row['stock_qty']==1) echo "<span class='last'>LAST</span>";
else echo "<span class='low'>LOW</span>";
?>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>



<!-- THIRD PARTY -->

<div class="section">

<h2>THIRD-PARTY PARTS — SOLD</h2>

<table>

<tr>
<th>Part Name</th>
<th>Status</th>
</tr>

<?php if($third) while($row = $third->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['description']) ?></td>
<td class="out"><?= htmlspecialchars($row['stock_status']) ?></td>

</tr>

<?php endwhile; ?>

</table>

</div>



<!-- STRIPPED -->

<div class="section">

<h2>STRIPPED PARTS — LAST AVAILABLE</h2>

<table>

<tr>
<th>Part Name</th>
<th>Qty</th>
</tr>

<?php if($stripped) while($row = $stripped->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['part_name']) ?></td>
<td class="last"><?= $row['qty'] ?></td>

</tr>

<?php endwhile; ?>

</table>

</div>


</div>

</body>
</html>
```
