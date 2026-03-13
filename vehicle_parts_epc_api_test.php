<?php
require_once __DIR__ . "/config/config.php";

$stock = $_GET['stock_code'] ?? '';
$part  = $_GET['part_name'] ?? '';
$search = $_GET['search'] ?? '';

if(!$stock){
    exit;
}

/* ==============================
   LOAD PART DETAILS
============================== */

if($part){

$stmt=$conn->prepare("
SELECT part_name, qty, location, position_code
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

/* ==============================
   SEARCH
============================== */

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

/* ==============================
   LOAD EPC TREE (OPTIMIZED)
============================== */

$stmt=$conn->prepare("
SELECT
vsp.part_name,
vsp.qty,
c.name AS component,
t.name AS type,
s.name AS subcategory,
cat.name AS category
FROM vehicle_stripped_parts vsp
LEFT JOIN components c ON vsp.component_id=c.id
LEFT JOIN types t ON c.type_id=t.id
LEFT JOIN subcategories s ON t.subcategory_id=s.id
LEFT JOIN categories cat ON s.category_id=cat.id
WHERE vsp.stock_code=?
AND vsp.component_id IS NOT NULL
ORDER BY
cat.name,
s.name,
t.name,
c.name,
vsp.part_name
");

$stmt->bind_param("s",$stock);
$stmt->execute();
$res=$stmt->get_result();

/* ==============================
   BUILD TREE IN MEMORY
============================== */

$tree=[];

while($row=$res->fetch_assoc()){

$cat=$row['category'] ?? 'Other';
$sub=$row['subcategory'] ?? 'Other';
$type=$row['type'] ?? 'Other';
$comp=$row['component'] ?? 'Other';

$tree[$cat][$sub][$type][$comp][]=$row;

}

/* ==============================
   PRINT TREE
============================== */

$cid=0;

foreach($tree as $cat=>$subs){

$cid++;
$catid="cat".$cid;

echo "<div class='tree-header'
onclick=\"toggleTree('$catid')\">
▶ $cat
</div>";

echo "<div id='$catid' class='tree-children'>";

foreach($subs as $sub=>$types){

$sid=$catid."_".md5($sub);

echo "<div class='tree-header'
onclick=\"toggleTree('$sid')\">
▶ $sub
</div>";

echo "<div id='$sid' class='tree-children'>";

foreach($types as $type=>$comps){

$tid=$sid."_".md5($type);

echo "<div class='tree-header'
onclick=\"toggleTree('$tid')\">
▶ $type
</div>";

echo "<div id='$tid' class='tree-children'>";

foreach($comps as $comp=>$parts){

$pid=$tid."_".md5($comp);

echo "<div class='tree-header'
onclick=\"toggleTree('$pid')\">
▶ $comp
</div>";

echo "<div id='$pid' class='tree-children'>";

foreach($parts as $p){

$name=htmlspecialchars($p['part_name']);
$qty=$p['qty'];

echo "<div class='node'
onclick=\"loadParts('$name','$stock')\">
$name $qty
</div>";

}

echo "</div>";

}

echo "</div>";

}

echo "</div>";

}

echo "</div>";

}