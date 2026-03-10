<?php
require_once __DIR__ . "/config/config.php";

$vehicle=$_GET['vehicle'] ?? '';

if(!$vehicle){
echo json_encode([]);
exit;
}

$sql="
SELECT
sp.part_name,
sp.qty,
sp.location
FROM vehicle_stripped_parts sp
JOIN vehicles v
ON v.id=sp.vehicle_id
WHERE v.stock_code=?
ORDER BY sp.part_name
";

$stmt=$conn->prepare($sql);
$stmt->bind_param("s",$vehicle);
$stmt->execute();

$res=$stmt->get_result();

$data=[];

while($row=$res->fetch_assoc()){

$qty=(int)$row['qty'];

$class="qty-good";

if($qty==0) $class="qty-zero";
elseif($qty==1) $class="qty-last";

$data[]=array(
"part_name"=>$row['part_name'],
"qty"=>$qty,
"location"=>$row['location'],
"qty_class"=>$class
);

}

echo json_encode($data);