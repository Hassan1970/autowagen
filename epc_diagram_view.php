<?php
require_once __DIR__ . "/config/config.php";

$stock = $_GET['stock_code'] ?? '';
$component = $_GET['component_id'] ?? '';

if(!$component){
    exit;
}

/* GET DIAGRAM */

$stmt = $conn->prepare("
SELECT diagram_image 
FROM epc_diagram_parts
WHERE component_id=?
LIMIT 1
");

$stmt->bind_param("i",$component);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

$diagram = $row['diagram_image'] ?? '';

if(!$diagram){
    exit;
}

/* LOAD HOTSPOTS */

$stmt = $conn->prepare("
SELECT label,x_position,y_position,component_id
FROM epc_diagram_parts
WHERE diagram_image=?
");

$stmt->bind_param("s",$diagram);
$stmt->execute();
$res = $stmt->get_result();
?>

<div class="diagram-container">

<img src="epc_images/<?php echo $diagram; ?>" class="diagram-img">

<?php while($p=$res->fetch_assoc()){ ?>

<div class="hotspot"
style="left:<?php echo $p['x_position']; ?>px;
top:<?php echo $p['y_position']; ?>px;"
onclick="loadParts('<?php echo $p['component_id']; ?>','<?php echo $stock; ?>')">

<?php echo $p['label']; ?>

</div>

<?php } ?>

</div>