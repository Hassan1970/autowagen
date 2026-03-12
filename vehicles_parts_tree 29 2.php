<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* GET VEHICLES WITH PART COUNTS */

$sql = "
SELECT
    v.stock_code,
    COUNT(sp.id) AS total_parts
FROM vehicles v
LEFT JOIN vehicle_stripped_parts sp
ON sp.vehicle_id = v.id
GROUP BY v.id
ORDER BY v.stock_code ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<title>Vehicle Parts Tree</title>

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

.vehicle{
background:#111;
padding:12px;
margin-bottom:6px;
cursor:pointer;
border:1px solid #333;
}

.vehicle:hover{
background:#1a1a1a;
}

.parts{
display:none;
margin-left:20px;
}

.part{
padding:8px;
border-bottom:1px solid #222;
}

/* STOCK COLORS */

.qty-zero{color:#ff4444;font-weight:bold;}
.qty-last{color:#ffcc00;font-weight:bold;}
.qty-good{color:#00ff88;font-weight:bold;}

.location{
color:#888;
font-size:12px;
margin-left:10px;
}

</style>

<script>

function toggleParts(vehicle){

let box=document.getElementById("parts_"+vehicle);

if(box.style.display==="block"){
box.style.display="none";
return;
}

fetch("vehicle_parts_api.php?vehicle="+vehicle)
.then(r=>r.json())
.then(rows=>{

let html="";

rows.forEach(p=>{

let icon="ðŸŸ¢";

if(p.qty==0) icon="ðŸ”´";
if(p.qty==1) icon="ðŸŸ¡";

html+=`
<div class="part">
${p.part_name}
<span class="location">${p.location}</span>
<span class="${p.qty_class}">
${icon} ${p.qty}
</span>
</div>
`;

});

box.innerHTML=html;
box.style.display="block";

});

}

</script>

</head>

<body>

<div class="wrap">

<h2>Vehicle Parts Tree</h2>

<?php while($row=$result->fetch_assoc()): ?>

<div
class="vehicle"
onclick="toggleParts('<?= $row['stock_code'] ?>')"
>

<?= htmlspecialchars($row['stock_code']) ?>
(<?= $row['total_parts'] ?> parts)

</div>

<div
class="parts"
id="parts_<?= $row['stock_code'] ?>"
></div>

<?php endwhile; ?>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>