<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

function h($v){
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/* ================= INPUT ================= */
$search = trim($_GET['q'] ?? '');
$qty_filter      = $_GET['qty'] ?? 'all';
$vehicle_filter  = $_GET['vehicle'] ?? 'all';
$location_filter = $_GET['location'] ?? 'all';
$clickable_only  = $_GET['clickable'] ?? '';

$where  = "1=1";
$params = [];
$types  = "";

/* ================= SEARCH ================= */
if($search !== ''){
    $where .= " AND (
        v.stock_code LIKE ?
        OR v.make LIKE ?
        OR v.model LIKE ?
        OR sp.part_name LIKE ?
    )";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "ssss";
}

/* ================= FILTERS ================= */

if($qty_filter == 'out'){
    $where .= " AND sp.qty = 0";
}elseif($qty_filter == 'last'){
    $where .= " AND sp.qty = 1";
}elseif($qty_filter == 'in'){
    $where .= " AND sp.qty > 1";
}

if($vehicle_filter !== 'all'){
    $where .= " AND v.stock_code = ?";
    $params[] = $vehicle_filter;
    $types .= "s";
}

if($location_filter !== 'all'){
    $where .= " AND sp.location = ?";
    $params[] = $location_filter;
    $types .= "s";
}

if($clickable_only == '1'){
    $where .= " AND sp.qty > 0";
}

/* ================= DATA ================= */

$sql = "
SELECT
    sp.id,
    sp.part_name,
    sp.qty,
    sp.location,
    sp.photo,
    sp.date_stripped,
    v.stock_code
FROM vehicle_stripped_parts sp
JOIN vehicles v ON v.id = sp.vehicle_id
WHERE {$where}
ORDER BY sp.date_stripped DESC
";

$stmt = $conn->prepare($sql);

if($params){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$vehicles  = $conn->query("SELECT DISTINCT stock_code FROM vehicles ORDER BY stock_code");
$locations = $conn->query("SELECT DISTINCT location FROM vehicle_stripped_parts ORDER BY location");
?>

<!DOCTYPE html>
<html>
<head>
<title>Stripped Parts Inventory</title>

<style>
body{background:#000;color:#fff;font-family:Arial;}
.wrap{width:95%;margin:20px auto;}

h1{color:#ff3333;}

.legend span{margin-right:15px;}

.search-bar input,
.search-bar select{
    padding:6px;
    background:#111;
    border:1px solid #333;
    color:#fff;
}

.search-bar button{
    padding:6px 10px;
    background:#b00000;
    border:none;
    color:#fff;
    cursor:pointer;
}

table{width:100%;border-collapse:collapse;}
th,td{border:1px solid #b00000;padding:8px;}
th{background:#111;color:#ff3333;}
tr:hover{background:#1a1a1a;}

.qty-out{color:red;}
.qty-last{color:orange;}
.qty-ok{color:lime;}
</style>
</head>

<body>

<div class="wrap">

<h1>Stripped Parts Inventory</h1>

<div class="legend">
<span style="color:red;">● OUT</span>
<span style="color:orange;">● LAST</span>
<span style="color:lime;">● IN STOCK</span>
</div>

<form method="GET" id="filterForm" class="search-bar" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">

<input type="text" name="q" placeholder="Search stripped parts..." value="<?= h($search) ?>" onkeyup="autoSubmit()">

<select name="qty" onchange="autoSubmit()">
<option value="all">Qty All</option>
<option value="in" <?= $qty_filter=='in'?'selected':'' ?>>In Stock</option>
<option value="last" <?= $qty_filter=='last'?'selected':'' ?>>Last</option>
<option value="out" <?= $qty_filter=='out'?'selected':'' ?>>Out</option>
</select>

<select name="vehicle" onchange="autoSubmit()">
<option value="all">Vehicle</option>
<?php while($v = $vehicles->fetch_assoc()): ?>
<option value="<?= $v['stock_code'] ?>" <?= $vehicle_filter==$v['stock_code']?'selected':'' ?>>
<?= $v['stock_code'] ?>
</option>
<?php endwhile; ?>
</select>

<select name="location" onchange="autoSubmit()">
<option value="all">Location</option>
<?php while($l = $locations->fetch_assoc()): ?>
<option value="<?= $l['location'] ?>" <?= $location_filter==$l['location']?'selected':'' ?>>
<?= $l['location'] ?>
</option>
<?php endwhile; ?>
</select>

<label style="display:flex;align-items:center;">
<input type="checkbox" name="clickable" value="1" <?= $clickable_only=='1'?'checked':'' ?> onchange="autoSubmit()">
Only Clickable
</label>

<button type="submit">Search</button>

<a href="stripped_list.php">
<button type="button">Show All</button>
</a>

</form>

<table>
<tr>
<th>#</th>
<th>Vehicle</th>
<th>Part</th>
<th>Qty</th>
<th>Location</th>
<th>Photo</th>
<th>Date</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<?php
$qty = (int)$row['qty'];

$class = "qty-ok";
if($qty==0) $class="qty-out";
elseif($qty==1) $class="qty-last";
?>

<?php if($qty>0): ?>
<tr onclick='selectStrippedPart(<?= json_encode($row) ?>)' style="cursor:pointer;">
<?php else: ?>
<tr style="opacity:0.3;">
<?php endif; ?>

<td><?= $row['id'] ?></td>
<td><?= h($row['stock_code']) ?></td>
<td><?= h($row['part_name']) ?></td>

<td class="<?= $class ?>">● <?= $qty ?></td>

<td><?= h($row['location']) ?></td>

<td>
<?php if(!empty($row['photo']) && file_exists(__DIR__.'/'.$row['photo'])): ?>
<img src="<?= h($row['photo']) ?>" width="70">
<?php else: ?>No photo<?php endif; ?>
</td>

<td><?= h($row['date_stripped']) ?></td>

</tr>

<?php endwhile; ?>

</table>

</div>

<script>
function autoSubmit(){
clearTimeout(window.t);
window.t = setTimeout(()=>{
document.getElementById("filterForm").submit();
},300);
}

/* ✅ FIXED FUNCTION */
function selectStrippedPart(part){

window.opener.postMessage({
type:"ITEM_SELECTED",
item:{
    id: part.id,
    name: part.part_name,
    price: 0,
    source: "STRIP",
    vehicle_stock_code: part.stock_code || ""
}
},"*");

window.close();
}
</script>

</body>
</html>

<?php include __DIR__ . "/includes/footer.php"; ?>