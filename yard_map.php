<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* ===============================
   PART COUNTS
=============================== */

$parts_counts = [];

$sql = "
SELECT location, COUNT(id) total
FROM vehicle_stripped_parts
WHERE location IS NOT NULL
AND location <> ''
GROUP BY location
";

$res = $conn->query($sql);

while($r = $res->fetch_assoc()){
$parts_counts[$r['location']] = $r['total'];
}


/* ===============================
   VEHICLE COUNTS
=============================== */

$vehicle_counts = [];
$vehicle_codes = [];
$vehicle_ids = [];

$sql = "
SELECT id,stock_code,yard_location
FROM vehicles
WHERE yard_location IS NOT NULL
AND yard_location <> ''
";

$res = $conn->query($sql);

while($r = $res->fetch_assoc()){

$loc = $r['yard_location'];

$vehicle_counts[$loc] = ($vehicle_counts[$loc] ?? 0) + 1;
$vehicle_codes[$loc] = $r['stock_code'];
$vehicle_ids[$loc] = $r['id'];

}


/* ===============================
   LOAD LOCATIONS
=============================== */

$locations = [];

$res = $conn->query("
SELECT code
FROM yard_locations
ORDER BY code
");

while($r = $res->fetch_assoc()){
$locations[] = $r['code'];
}


/* ===============================
   GROUP LOCATIONS BY ROW
=============================== */

$rows = [];

foreach($locations as $loc){

$row = preg_replace('/[0-9]/','',$loc);

$rows[$row][] = $loc;

}

?>

<!DOCTYPE html>
<html>
<head>

<title>Smart Yard Map</title>

<style>

body{
background:#000;
color:#fff;
font-family:Arial;
}

.wrap{
width:92%;
margin:30px auto;
}

h2{
color:#ff3333;
margin-bottom:25px;
}

.row{
display:flex;
flex-wrap:wrap;
gap:18px;
margin-bottom:25px;
}

.block{

width:170px;
height:140px;

border:2px solid #333;
background:#111;

display:flex;
flex-direction:column;
justify-content:center;
align-items:center;

cursor:pointer;

font-size:22px;

}

.block:hover{
background:#1a1a1a;
}

/* COLORS */

.green{border-color:#00ff88;color:#00ff88;}
.yellow{border-color:#ffcc00;color:#ffcc00;}
.red{border-color:#ff4444;color:#ff4444;}

.info{
font-size:13px;
color:#aaa;
margin-top:4px;
}

.vehicle{
font-size:15px;
margin-top:6px;
color:#fff;
}

</style>

</head>

<body>

<div class="wrap">

<h2>Smart Yard Map</h2>

<?php

foreach($rows as $row=>$list){

echo "<div class='row'>";

foreach($list as $loc){

$parts = $parts_counts[$loc] ?? 0;
$vehicles = $vehicle_counts[$loc] ?? 0;
$stock = $vehicle_codes[$loc] ?? '';

$total = $parts + $vehicles;

/* COLOR */

$class="red";

if($total>=10) $class="green";
elseif($total>=1) $class="yellow";

/* LINK */

$vehicle_id = $vehicle_ids[$loc] ?? 0;

if($vehicle_id){
$link="vehicle_view.php?id=".$vehicle_id;
}else{
$link="location_parts.php?location=".$loc;
}

?>

<div class="block <?= $class ?>"
onclick="window.location='<?= $link ?>'">

<?= $loc ?>

<?php if($stock): ?>
<div class="vehicle"><?= $stock ?></div>
<?php endif; ?>

<div class="info">🚗 <?= $vehicles ?> vehicles</div>

<div class="info">🔧 <?= $parts ?> parts</div>

</div>

<?php

}

echo "</div>";

}

?>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>