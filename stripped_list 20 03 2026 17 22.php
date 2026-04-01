<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

function h($v){
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/* ================= SEARCH ================= */
$search = trim($_GET['q'] ?? '');

$where  = "1=1";
$params = [];
$types  = "";

if($search !== ''){
    $where .= " AND (
        v.stock_code LIKE ?
        OR v.make LIKE ?
        OR v.model LIKE ?
        OR sp.part_name LIKE ?
    )";

    $like = "%{$search}%";
    $params = [$like,$like,$like,$like];
    $types  = "ssss";
}

/* ================= QUERY ================= */
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
?>

<!DOCTYPE html>
<html>
<head>
<title>Stripped Parts Inventory</title>

<style>

body{
    background:#000;
    color:#fff;
    font-family:Arial;
}

.wrap{
    width:95%;
    margin:20px auto;
}

h1{
    color:#ff3333;
}

/* LEGEND */
.legend{
    margin-bottom:10px;
}

.legend span{
    margin-right:15px;
}

/* SEARCH */
.search-bar{
    margin-bottom:10px;
}

.search-bar input{
    padding:6px;
    width:300px;
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

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

th,td{
    border:1px solid #b00000;
    padding:8px;
}

th{
    background:#111;
    color:#ff3333;
}

tr:hover{
    background:#1a1a1a;
}

/* QTY COLORS */
.qty-out{color:red;}
.qty-last{color:orange;}
.qty-ok{color:lime;}

.thumb{
    width:70px;
    height:50px;
    object-fit:cover;
    border:1px solid #b00000;
}

</style>
</head>

<body>

<div class="wrap">

<h1>Stripped Parts Inventory</h1>

<!-- LEGEND -->
<div class="legend">
<span style="color:red;">● OUT</span>
<span style="color:orange;">● LAST</span>
<span style="color:lime;">● IN STOCK</span>
</div>

<!-- SEARCH -->
<form class="search-bar" method="GET">
<input type="text" name="q" placeholder="Search stripped parts..." value="<?= h($search) ?>">
<button type="submit">Search</button>
<a href="stripped_list.php"><button type="button">Show All</button></a>
</form>

<!-- TABLE -->
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

<?php if($result->num_rows === 0): ?>
<tr>
<td colspan="7" style="text-align:center;color:#777;">
No parts found
</td>
</tr>
<?php endif; ?>

<?php while($row = $result->fetch_assoc()): ?>

<?php
$qty = (int)$row['qty'];

$qtyClass = "qty-ok";
if($qty == 0) $qtyClass = "qty-out";
elseif($qty == 1) $qtyClass = "qty-last";
?>

<!-- 🔥 CLICK ONLY IF QTY > 0 -->
<?php if($qty > 0): ?>
<tr onclick='selectStrippedPart(<?= json_encode($row) ?>)' style="cursor:pointer;">
<?php else: ?>
<tr style="opacity:0.3;cursor:not-allowed;">
<?php endif; ?>

<td><?= $row['id'] ?></td>
<td><?= h($row['stock_code']) ?></td>
<td><?= h($row['part_name']) ?></td>

<td class="<?= $qtyClass ?>">
● <?= $qty ?>
</td>

<td><?= h($row['location']) ?></td>

<td>
<?php if(!empty($row['photo']) && file_exists(__DIR__.'/'.$row['photo'])): ?>
<img src="<?= h($row['photo']) ?>" class="thumb">
<?php else: ?>
<span style="color:#777;">No photo</span>
<?php endif; ?>
</td>

<td><?= h($row['date_stripped']) ?></td>

</tr>

<?php endwhile; ?>

</table>

</div>

<!-- 🔥 SEND DATA TO POS -->
<script>
function selectStrippedPart(part){

window.opener.postMessage({
    type: "STRIPPED_PART_SELECTED",
    part: part
}, "*");

window.close();
}
</script>

</body>
</html>

<?php include __DIR__ . "/includes/footer.php"; ?>