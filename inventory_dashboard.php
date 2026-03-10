<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* =========================
   VEHICLES IN YARD
========================= */

$q1 = "SELECT COUNT(*) AS total FROM vehicles";
$r1 = $conn->query($q1);
$vehicles_total = $r1->fetch_assoc()['total'] ?? 0;


/* =========================
   TOTAL STRIPPED PARTS
========================= */

$q2 = "
SELECT SUM(qty) AS total
FROM vehicle_stripped_parts
";

$r2 = $conn->query($q2);
$stripped_total = $r2->fetch_assoc()['total'] ?? 0;


/* =========================
   LOW STOCK PARTS
========================= */

$q3 = "
SELECT COUNT(*) AS total
FROM vehicle_stripped_parts
WHERE qty <= 1
";

$r3 = $conn->query($q3);
$low_stock_total = $r3->fetch_assoc()['total'] ?? 0;


/* =========================
   PARTS SOLD TODAY
========================= */

$q4 = "
SELECT SUM(qty) AS total
FROM invoice_items
WHERE DATE(created_at) = CURDATE()
";

$r4 = $conn->query($q4);

$parts_sold_today = 0;

if($r4){
$row = $r4->fetch_assoc();
$parts_sold_today = $row['total'] ?? 0;
}


/* =========================
   TOP SELLING PART
========================= */

$q5 = "
SELECT part_name, SUM(qty) AS total_sold
FROM invoice_items
GROUP BY part_name
ORDER BY total_sold DESC
LIMIT 1
";

$r5 = $conn->query($q5);

$top_part = "N/A";

if($r5 && $r5->num_rows > 0){
$row = $r5->fetch_assoc();
$top_part = $row['part_name'] ?? "N/A";
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Inventory Control Center</title>

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
margin-bottom:30px;
}

/* DASHBOARD GRID */

.grid{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:20px;
}

.card{
background:#111;
border:1px solid #333;
padding:25px;
text-align:center;
}

.card-title{
font-size:16px;
color:#aaa;
margin-bottom:10px;
}

.card-value{
font-size:32px;
font-weight:bold;
color:#00ff88;
}

.card-value.red{
color:#ff4444;
}

.card-value.yellow{
color:#ffcc00;
}

.card-value.blue{
color:#4da6ff;
}

.card-value.purple{
color:#cc66ff;
}

</style>

</head>

<body>

<div class="wrap">

<h2>Inventory Control Center</h2>

<div class="grid">

<!-- VEHICLES -->

<div class="card">
<div class="card-title">Vehicles in Yard</div>
<div class="card-value blue">
<?= (int)$vehicles_total ?>
</div>
</div>


<!-- STRIPPED PARTS -->

<div class="card">
<div class="card-title">Total Stripped Parts</div>
<div class="card-value">
<?= (int)$stripped_total ?>
</div>
</div>


<!-- LOW STOCK -->

<div class="card">
<div class="card-title">Low Stock Parts</div>
<div class="card-value red">
<?= (int)$low_stock_total ?>
</div>
</div>


<!-- SOLD TODAY -->

<div class="card">
<div class="card-title">Parts Sold Today</div>
<div class="card-value yellow">
<?= (int)$parts_sold_today ?>
</div>
</div>


<!-- TOP SELLING PART -->

<div class="card">
<div class="card-title">Top Selling Part</div>
<div class="card-value purple">
<?= htmlspecialchars($top_part ?? '') ?>
</div>
</div>

</div>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>