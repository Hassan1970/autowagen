<?php
require_once __DIR__ . "/config/config.php";

function h($v){
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Replacement Part</title>

<style>
body{background:#000;color:#fff;font-family:Arial;}
.wrap{width:95%;margin:20px auto;}

h1{color:#cc66ff;}

.legend span{margin-right:15px;}

input,select{
padding:6px;
background:#111;
border:1px solid #333;
color:#fff;
}

button{
padding:6px 10px;
background:#b00000;
border:none;
color:#fff;
cursor:pointer;
}

table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #333;padding:8px;}
th{background:#111;color:#cc66ff;}
tr:hover{background:#1a1a1a;cursor:pointer;}

.qty-out{color:red;}
.qty-last{color:orange;}
.qty-ok{color:lime;}

.price{color:#00ffcc;font-weight:bold;}
.cost{color:#ffaa00;font-weight:bold;}
.meta{font-size:11px;color:#888;}
</style>

<script>
function selectReplacement(part){

window.opener.postMessage({
type:"ITEM_SELECTED",
item:{
    id: part.id,
    name: part.part_name,
    price: part.selling_price || 0,
    source:"NEW",
    vehicle_stock_code:""
}
},"*");

window.close();
}

/* FILTER */
function filterTable(){

let search = document.getElementById("searchBox").value.toLowerCase();
let qtyFilter = document.getElementById("qtyFilter").value;
let clickable = document.getElementById("clickable").checked;

document.querySelectorAll("tbody tr").forEach(row=>{

let text = row.innerText.toLowerCase();
let qty = parseInt(row.getAttribute("data-qty")) || 0;

let show = true;

if(search && !text.includes(search)) show=false;
if(qtyFilter==="in" && qty<=1) show=false;
if(qtyFilter==="last" && qty!==1) show=false;
if(qtyFilter==="out" && qty!==0) show=false;
if(clickable && qty<=0) show=false;

row.style.display = show ? "" : "none";

});
}

function resetFilter(){
document.getElementById("searchBox").value="";
document.getElementById("qtyFilter").value="all";
document.getElementById("clickable").checked=false;

document.querySelectorAll("tbody tr").forEach(r=>{
r.style.display="";
});
}
</script>

</head>
<body>

<div class="wrap">

<h1>Replacement Parts</h1>

<!-- LEGEND -->
<div class="legend">
<span style="color:red;">● OUT</span>
<span style="color:orange;">● LAST</span>
<span style="color:lime;">● IN STOCK</span>
</div>

<!-- FILTER BAR -->
<div style="display:flex;gap:10px;flex-wrap:wrap;margin:10px 0;">

<input type="text" id="searchBox" placeholder="Search replacement parts...">

<select id="qtyFilter">
<option value="all">Qty All</option>
<option value="in">In Stock</option>
<option value="last">Last</option>
<option value="out">Out</option>
</select>

<label style="display:flex;align-items:center;">
<input type="checkbox" id="clickable"> Only Clickable
</label>

<button onclick="filterTable()">Search</button>
<button onclick="resetFilter()">Show All</button>

</div>

<table>
<thead>
<tr>
<th>ID</th>
<th>Part No</th>
<th>Part Name</th>
<th>Qty</th>
<th>Cost</th>
<th>Selling</th>
<th>Notes</th>
<th>Date</th>
</tr>
</thead>

<tbody>

<?php

$sql = "
SELECT 
id,
part_number,
part_name,
stock_qty,
cost_price,
selling_price,
notes,
created_at
FROM replacement_parts
ORDER BY part_name ASC
";

$result = $conn->query($sql);

while($row = $result->fetch_assoc()):

$qty = (int)$row['stock_qty'];

$class = "qty-ok";
if($qty==0) $class="qty-out";
elseif($qty==1) $class="qty-last";
?>

<tr 
data-qty="<?= $qty ?>"
<?= $qty > 0 ? "onclick='selectReplacement(".json_encode($row).")'" : "" ?>
style="<?= $qty > 0 ? "" : "opacity:0.3;" ?>"
>

<td><?= $row['id'] ?></td>

<td class="meta"><?= h($row['part_number']) ?></td>

<td><?= h($row['part_name']) ?></td>

<td class="<?= $class ?>">● <?= $qty ?></td>

<td class="cost">
R <?= number_format((float)$row['cost_price'],2) ?>
</td>

<td class="price">
R <?= number_format((float)$row['selling_price'],2) ?>
</td>

<td><?= h($row['notes']) ?></td>

<td><?= h($row['created_at']) ?></td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>

</body>
</html>