<?php
require_once __DIR__ . "/config/config.php";

$stock=$_GET['stock_code'] ?? '';
$part=$_GET['part_name'] ?? '';
$search=$_GET['search'] ?? '';
$level=$_GET['level'] ?? '';
$parent=$_GET['parent'] ?? '';

if(!$stock){
exit;
}


/* PART DETAILS */

if($part){

$stmt=$conn->prepare("
SELECT part_name,qty,location,position_code
FROM vehicle_stripped_parts
WHERE stock_code=? AND part_name=?
");

$stmt->bind_param("ss",$stock,$part);
$stmt->execute();
$res=$stmt->get_result();

echo "<table class='parts-table'>";
echo "<tr>
<th>Part</th>
<th>Qty</th>
<th>Location</th>
<th>Position</th>
</tr>";

while($row=$res->fetch_assoc()){

echo "<tr>";
echo "<td>".$row['part_name']."</td>";
echo "<td>".$row['qty']."</td>";
echo "<td>".$row['location']."</td>";
echo "<td>".$row['position_code']."</td>";
echo "</tr>";

}

echo "</table>";

exit;

}


/* SEARCH */

if($search){

$stmt=$conn->prepare("
SELECT DISTINCT part_name
FROM vehicle_stripped_parts
WHERE stock_code=? AND part_name LIKE ?
ORDER BY part_name
LIMIT 50
");

$like="%".$search."%";

$stmt->bind_param("ss",$stock,$like);
$stmt->execute();
$res=$stmt->get_result();

while($row=$res->fetch_assoc()){

$p=htmlspecialchars($row['part_name']);

echo "<div class='node'
onclick=\"loadParts('$p','$stock')\">
$p
</div>";

}

exit;

}


/* CATEGORY */

if($level=="category"){

$stmt=$conn->prepare("
SELECT DISTINCT cat.name
FROM vehicle_stripped_parts vsp
JOIN components c ON vsp.component_id=c.id
JOIN types t ON c.type_id=t.id
JOIN subcategories s ON t.subcategory_id=s.id
JOIN categories cat ON s.category_id=cat.id
WHERE vsp.stock_code=?
ORDER BY cat.name
");

$stmt->bind_param("s",$stock);
$stmt->execute();
$res=$stmt->get_result();

while($row=$res->fetch_assoc()){

$name=$row['name'];

$id="cat_".md5($name);

echo "<div class='tree-header'
onclick=\"loadLevel('subcategory','$name','$id')\">
▶ $name
</div>";

echo "<div id='$id' class='tree-children'></div>";

}

exit;

}


/* SUBCATEGORY */

if($level=="subcategory"){

$stmt=$conn->prepare("
SELECT DISTINCT s.name
FROM vehicle_stripped_parts vsp
JOIN components c ON vsp.component_id=c.id
JOIN types t ON c.type_id=t.id
JOIN subcategories s ON t.subcategory_id=s.id
JOIN categories cat ON s.category_id=cat.id
WHERE vsp.stock_code=? AND cat.name=?
ORDER BY s.name
");

$stmt->bind_param("ss",$stock,$parent);
$stmt->execute();
$res=$stmt->get_result();

while($row=$res->fetch_assoc()){

$name=$row['name'];

$id="sub_".md5($name);

echo "<div class='tree-header'
onclick=\"loadLevel('type','$name','$id')\">
▶ $name
</div>";

echo "<div id='$id' class='tree-children'></div>";

}

exit;

}


/* TYPE */

if($level=="type"){

$stmt=$conn->prepare("
SELECT DISTINCT t.name
FROM vehicle_stripped_parts vsp
JOIN components c ON vsp.component_id=c.id
JOIN types t ON c.type_id=t.id
JOIN subcategories s ON t.subcategory_id=s.id
WHERE vsp.stock_code=? AND s.name=?
ORDER BY t.name
");

$stmt->bind_param("ss",$stock,$parent);
$stmt->execute();
$res=$stmt->get_result();

while($row=$res->fetch_assoc()){

$name=$row['name'];

$id="type_".md5($name);

echo "<div class='tree-header'
onclick=\"loadLevel('component','$name','$id')\">
▶ $name
</div>";

echo "<div id='$id' class='tree-children'></div>";

}

exit;

}


/* COMPONENT */

if($level=="component"){

$stmt=$conn->prepare("
SELECT DISTINCT c.name
FROM vehicle_stripped_parts vsp
JOIN components c ON vsp.component_id=c.id
JOIN types t ON c.type_id=t.id
WHERE vsp.stock_code=? AND t.name=?
ORDER BY c.name
");

$stmt->bind_param("ss",$stock,$parent);
$stmt->execute();
$res=$stmt->get_result();

while($row=$res->fetch_assoc()){

$name=$row['name'];

echo "<div class='node'
onclick=\"loadParts('$name','$stock')\">
$name
</div>";

}

exit;

}