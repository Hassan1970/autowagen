<?php
$page_title="Master Inventory";
require_once __DIR__."/config/config.php";
include __DIR__."/includes/header.php";

$search=trim($_GET['search'] ?? '');
$partFilter=$_GET['part'] ?? 'All';
$modelFilter=$_GET['model'] ?? 'All';

/* DASHBOARD */

$totalParts=$conn->query("SELECT COUNT(*) c FROM vehicle_stripped_parts")->fetch_assoc()['c'];
$outStock=$conn->query("SELECT COUNT(*) c FROM vehicle_stripped_parts WHERE qty=0")->fetch_assoc()['c'];
$lowStock=$conn->query("SELECT COUNT(*) c FROM vehicle_stripped_parts WHERE qty=1")->fetch_assoc()['c'];
$vehicles=$conn->query("SELECT COUNT(DISTINCT vehicle_id) c FROM vehicle_stripped_parts")->fetch_assoc()['c'];

/* PART LIST */

$partsList=$conn->query("
SELECT DISTINCT part_name
FROM vehicle_stripped_parts
ORDER BY part_name ASC
");

/* MODEL LIST */

$modelList=$conn->query("
SELECT DISTINCT CONCAT(make,' ',model) model_name
FROM vehicles
ORDER BY make,model
");

/* FILTERS */

$where=[];

if($search!=''){
$s=$conn->real_escape_string($search);
$where[]="part_name LIKE '%$s%'";
}

if($partFilter!='All'){
$p=$conn->real_escape_string($partFilter);
$where[]="part_name='$p'";
}

if($modelFilter!='All'){
$m=$conn->real_escape_string($modelFilter);
$where[]="CONCAT(make,' ',model)='$m'";
}

$whereSQL='';
if(count($where)>0){
$whereSQL=" WHERE ".implode(" AND ",$where);
}

/* INVENTORY QUERY */

$sql="

SELECT * FROM (

SELECT
'Stripped' type,
sp.part_name,
v.stock_code vehicle,
sp.qty,
COALESCE(sp.location,'Not Set') location,
v.make,
v.model
FROM vehicle_stripped_parts sp
LEFT JOIN vehicles v ON v.id=sp.vehicle_id

UNION ALL

SELECT
'OEM',
op.part_name,
'-',
op.stock_qty,
'-',
'-',
'-'
FROM oem_parts op

UNION ALL

SELECT
'Replacement',
rp.part_name,
'-',
rp.stock_qty,
'-',
'-',
'-'
FROM replacement_parts rp

UNION ALL

SELECT
'3rd Party',
tp.description,
'-',
1,
'-',
'-',
'-'
FROM third_party_parts tp

) master_inventory

$whereSQL

ORDER BY part_name ASC
";

$result=$conn->query($sql);
?>

<style>

.wrap{width:95%;margin:25px auto;}
.fixed-header{position:sticky;top:0;background:#000;z-index:20;padding-bottom:10px;}

.dashboard{display:flex;gap:10px;flex-wrap:wrap;}

.card{
background:#111;
padding:12px;
border:1px solid #333;
text-align:center;
min-width:140px;
}

.card h3{margin:0;font-size:13px;color:#aaa;}
.card p{margin:4px 0;font-size:20px;font-weight:bold;}

.top-row{display:flex;justify-content:space-between;flex-wrap:wrap;align-items:flex-start;}

.filter-panel select{padding:6px;margin-left:6px;}
.filter-panel button{padding:6px 10px;background:#b00000;border:none;color:#fff;cursor:pointer;}

.filters{margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;}

.filter-btn{
background:#222;
border:1px solid #333;
color:#fff;
padding:6px 12px;
cursor:pointer;
}

.filter-btn:hover{background:#b00000;}

.search-legend{
display:flex;
align-items:center;
justify-content:space-between;
margin-top:12px;
}

.search-bar input{padding:6px;width:240px;}

.legend{flex:1;text-align:center;font-weight:bold;}

.table-container{max-height:600px;overflow:auto;}

table{width:100%;border-collapse:collapse;}

th,td{border:1px solid #333;padding:10px;}

thead th{
position:sticky;
top:0;
background:#111;
color:#ff3333;
cursor:pointer;
}

tr:nth-child(even){background:#0b0b0b;}
tr:hover{background:#1a0000;}

.type-stripped{color:#ff8844;font-weight:bold}
.type-oem{color:#00c3ff;font-weight:bold}
.type-replacement{color:#ffcc00;font-weight:bold}
.type-third{color:#00ff88;font-weight:bold}

.qty-zero{color:#ff4444;font-weight:bold}
.qty-last{color:#ffcc00;font-weight:bold}
.qty-good{color:#00ff88;font-weight:bold}

.vehicle-link{color:#00c3ff;text-decoration:none;font-weight:bold}
.location-link{color:#00ff88;text-decoration:none;font-weight:bold}
.part-link{color:#fff;text-decoration:none;font-weight:bold}

.part-link:hover{color:#ffcc00;text-decoration:underline;}

</style>

<div class="wrap">

<div class="fixed-header">

<h2>Master Inventory</h2>

<div class="top-row">

<div class="dashboard">

<div class="card">
<h3>Total Parts</h3>
<p><?=number_format($totalParts)?></p>
</div>

<div class="card">
<h3>Out of Stock</h3>
<p><?=$outStock?></p>
</div>

<div class="card">
<h3>Low Stock</h3>
<p><?=$lowStock?></p>
</div>

<div class="card">
<h3>Vehicles Stripped</h3>
<p><?=$vehicles?></p>
</div>

</div>

<form method="GET" class="filter-panel">

Part:
<select name="part">
<option value="All">All</option>

<?php while($p=$partsList->fetch_assoc()): ?>

<option value="<?=$p['part_name']?>" <?php if($partFilter==$p['part_name']) echo "selected"; ?>>
<?=$p['part_name']?>
</option>

<?php endwhile; ?>

</select>

Vehicle Model:

<select name="model">

<option value="All">All</option>

<?php while($m=$modelList->fetch_assoc()): ?>

<option value="<?=$m['model_name']?>" <?php if($modelFilter==$m['model_name']) echo "selected"; ?>>
<?=$m['model_name']?>
</option>

<?php endwhile; ?>

</select>

<button type="submit">Apply Filter</button>

</form>

</div>

<div class="filters">

<button class="filter-btn" onclick="filterType('all')">All</button>
<button class="filter-btn" onclick="filterType('Stripped')">Stripped</button>
<button class="filter-btn" onclick="filterType('OEM')">OEM</button>
<button class="filter-btn" onclick="filterType('Replacement')">Replacement</button>
<button class="filter-btn" onclick="filterType('3rd Party')">3rd Party</button>
<button class="filter-btn" onclick="filterStock('out')">Out of Stock</button>
<button class="filter-btn" onclick="filterStock('in')">In Stock</button>

</div>

<div class="search-legend">

<form method="GET" class="search-bar">
<input type="text" name="search" placeholder="Search part..." value="<?=htmlspecialchars($search)?>">
<button type="submit">Search</button>
</form>

<div class="legend">
🟠 Stripped 🔵 OEM 🟡 Replacement 🟢 3rd Party 🔴 OUT 🟡 LAST 🟢 STOCK
</div>

</div>

</div>

<div class="table-container">

<table id="inventoryTable">

<thead>
<tr>
<th onclick="sortTable(0)">Type</th>
<th onclick="sortTable(1)">Part</th>
<th onclick="sortTable(2)">Vehicle</th>
<th onclick="sortTable(3)">Qty</th>
<th onclick="sortTable(4)">Location</th>
<th>Vehicles</th>
</tr>
</thead>

<tbody>

<?php while($row=$result->fetch_assoc()): ?>

<?php

$typeClass='';
if($row['type']=='Stripped')$typeClass='type-stripped';
if($row['type']=='OEM')$typeClass='type-oem';
if($row['type']=='Replacement')$typeClass='type-replacement';
if($row['type']=='3rd Party')$typeClass='type-third';

$qty=(int)$row['qty'];

/* VEHICLE COUNT */

$vehicleCount=0;

if($row['type']=='Stripped'){
$partEsc=$conn->real_escape_string($row['part_name']);

$vehicleCount=$conn->query("
SELECT COUNT(DISTINCT vehicle_id) c
FROM vehicle_stripped_parts
WHERE part_name='$partEsc'
")->fetch_assoc()['c'];
}

?>

<tr data-type="<?=$row['type']?>" data-qty="<?=$qty?>">

<td class="<?=$typeClass?>"><?=$row['type']?></td>

<td>
<a class="part-link" href="part_history.php?part=<?=urlencode($row['part_name'])?>">
<?=htmlspecialchars($row['part_name'])?>
</a>
</td>

<td>

<?php
$vehicle=$row['vehicle'];

if($vehicle!='-' && $vehicle!=''){
echo '<a class="vehicle-link" href="vehicle_parts_view.php?vehicle='.urlencode($vehicle).'">'.$vehicle.'</a>';
}else{
echo '-';
}
?>

</td>

<td>

<?php
if($qty==0)echo'<span class="qty-zero">🔴'.$qty.'</span>';
elseif($qty==1)echo'<span class="qty-last">🟡'.$qty.'</span>';
else echo'<span class="qty-good">🟢'.$qty.'</span>';
?>

</td>

<td>

<?php
$loc=$row['location'];

if($loc!='-' && $loc!='Not Set'){
echo '<a class="location-link" href="location_parts.php?location='.urlencode($loc).'">'.$loc.'</a>';
}else{
echo $loc;
}
?>

</td>

<td>

<?php
if($row['type']=='Stripped' && $vehicleCount>0){
echo '<a class="vehicle-link" href="part_vehicles.php?part='.urlencode($row['part_name']).'">🔍 '.$vehicleCount.'</a>';
}else{
echo '-';
}
?>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

<script>

/* TYPE FILTER */

function filterType(type){

let rows=document.querySelectorAll("#inventoryTable tbody tr");

rows.forEach(row=>{

let rowType=row.getAttribute("data-type");

if(type==="all"){
row.style.display="";
}
else if(rowType===type){
row.style.display="";
}
else{
row.style.display="none";
}

});

}

/* STOCK FILTER */

function filterStock(mode){

let rows=document.querySelectorAll("#inventoryTable tbody tr");

rows.forEach(row=>{

let qty=parseInt(row.getAttribute("data-qty"));

if(mode==="out"){
row.style.display=(qty===0)?"":"none";
}

if(mode==="in"){
row.style.display=(qty>0)?"":"none";
}

});

}

/* TABLE SORTING */

function sortTable(n){

let table=document.getElementById("inventoryTable");
let rows=[...table.rows].slice(1);
let asc=true;

rows.sort((a,b)=>{

let x=a.cells[n].innerText.toLowerCase();
let y=b.cells[n].innerText.toLowerCase();

if(!isNaN(x) && !isNaN(y)){
return asc ? x-y : y-x;
}

return asc ? x.localeCompare(y) : y.localeCompare(x);

});

rows.forEach(r=>table.tBodies[0].appendChild(r));

}

</script>

<?php include __DIR__."/includes/footer.php"; ?>