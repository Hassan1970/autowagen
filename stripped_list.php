<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$search = trim($_GET['q'] ?? '');
$qtyFilter = $_GET['qty'] ?? '';
$vehicleFilter = $_GET['vehicle'] ?? '';

$whereParts = [];
$params = [];
$types = "";

/* SEARCH FILTER */

if ($search !== '') {
    $whereParts[] = "(v.stock_code LIKE ? OR sp.part_name LIKE ? OR sp.location LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

/* VEHICLE FILTER */

if ($vehicleFilter !== '') {
    $whereParts[] = "v.stock_code = ?";
    $params[] = $vehicleFilter;
    $types .= "s";
}

/* QTY FILTER */

if ($qtyFilter !== '') {

    if ($qtyFilter == "0") {
        $whereParts[] = "sp.qty = 0";
    }

    if ($qtyFilter == "1") {
        $whereParts[] = "sp.qty = 1";
    }

    if ($qtyFilter == "2plus") {
        $whereParts[] = "sp.qty >= 2";
    }
}

$where = "";

if ($whereParts) {
    $where = "WHERE " . implode(" AND ", $whereParts);
}

/* MAIN QUERY */

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
{$where}
ORDER BY sp.date_stripped DESC
";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

/* VEHICLE LIST FOR FILTER */

$vehicles = [];

$vsql = "SELECT DISTINCT stock_code FROM vehicles ORDER BY stock_code";
$vresult = $conn->query($vsql);

while ($vrow = $vresult->fetch_assoc()) {
    $vehicles[] = $vrow['stock_code'];
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Stripped Parts Inventory</title>

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

.search-box{
margin-bottom:15px;
}

input[type=text]{
width:300px;
padding:8px;
background:#111;
border:1px solid #b00000;
color:#fff;
}

button{
padding:8px 12px;
background:#b00000;
border:none;
color:#fff;
cursor:pointer;
margin-left:5px;
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

tr.clickable:hover{
background:#1a0000;
cursor:pointer;
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

.filter-select{
background:#111;
color:#fff;
border:1px solid #333;
padding:3px;
font-size:12px;
margin-left:5px;
}

.legend{
margin-bottom:10px;
font-size:14px;
}

.vehicle-link{
color:#4da6ff;
text-decoration:none;
font-weight:bold;
}

.vehicle-link:hover{
text-decoration:underline;
}

</style>

<script>

function sendToPOS(id){

    if(window.opener && !window.opener.closed){

        window.opener.postMessage({
            type:"STRIPPED_PART_SELECTED",
            part_id:id
        },"*");

        window.close();

    }else{

        alert("This window was not opened from POS.");

    }
}

function applyFilter(){

    const vehicle = document.getElementById("vehicleFilter").value;
    const qty = document.getElementById("qtyFilter").value;

    const url = new URL(window.location.href);

    if(vehicle === ""){
        url.searchParams.delete("vehicle");
    }else{
        url.searchParams.set("vehicle", vehicle);
    }

    if(qty === ""){
        url.searchParams.delete("qty");
    }else{
        url.searchParams.set("qty", qty);
    }

    window.location.href = url.toString();
}

</script>

</head>

<body>

<div class="wrap">

<h2>Stripped Parts Inventory</h2>

<div class="legend">
🔴 OUT &nbsp;&nbsp; 🟡 LAST &nbsp;&nbsp; 🟢 IN STOCK
</div>

<div class="search-box">

<form method="GET" style="display:inline-block">

<input
type="text"
name="q"
placeholder="Search stripped parts..."
value="<?= htmlspecialchars($search ?? '') ?>">

<button type="submit">Search</button>

</form>

<a href="stripped_list.php">
<button>Show All</button>
</a>

</div>

<table>

<tr>

<th>
Vehicle
<select id="vehicleFilter" class="filter-select" onchange="applyFilter()">

<option value="">All</option>

<?php foreach($vehicles as $v): ?>

<option
value="<?= htmlspecialchars($v ?? '') ?>"
<?= ($vehicleFilter==$v) ? "selected" : "" ?>
>

<?= htmlspecialchars($v ?? '') ?>

</option>

<?php endforeach; ?>

</select>
</th>

<th>Part</th>

<th>
Qty
<select id="qtyFilter" class="filter-select" onchange="applyFilter()">

<option value="">All</option>

<option value="0" <?= $qtyFilter=="0"?"selected":"" ?>>0</option>
<option value="1" <?= $qtyFilter=="1"?"selected":"" ?>>1</option>
<option value="2plus" <?= $qtyFilter=="2plus"?"selected":"" ?>>2+</option>

</select>
</th>

<th>Price</th>
<th>Location</th>
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

$icon = "🟢";

if($qty == 0){
    $icon = "🔴";
}
elseif($qty == 1){
    $icon = "🟡";
}

?>

<tr class="clickable"
onclick="sendToPOS(<?= (int)$row['id'] ?>)">

<td>

<a
class="vehicle-link"
href="vehicle_parts_view.php?vehicle=<?= urlencode($row['stock_code'] ?? '') ?>"
onclick="event.stopPropagation()"
>

<?= htmlspecialchars($row['stock_code'] ?? '') ?>

</a>

</td>

<td><?= htmlspecialchars($row['part_name'] ?? '') ?></td>

<td class="<?= $qtyClass ?>">
<?= $icon ?> <?= $qty ?>
</td>

<td></td>

<td><?= htmlspecialchars($row['location'] ?? '') ?></td>

<td><?= htmlspecialchars($row['date_stripped'] ?? '') ?></td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="6" class="empty">No Stripped Parts Found</td>
</tr>

<?php endif; ?>

</table>

</div>

</body>
</html>

<?php include __DIR__."/includes/footer.php"; ?>