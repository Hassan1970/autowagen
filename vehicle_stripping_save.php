<?php
require_once __DIR__.'/config/config.php';

$vehicle_id=(int)$_POST['vehicle_id'];
$category_id=(int)$_POST['category_id'];
$subcategory_id=(int)$_POST['subcategory_id'];
$type_id=(int)$_POST['type_id'];
$component_id=$_POST['component_id']? (int)$_POST['component_id']:null;
$part_name=trim($_POST['part_name']);
$condition=$_POST['condition'];
$location=trim($_POST['location']);

if(!$vehicle_id||!$category_id||!$subcategory_id||!$type_id||$part_name=='')
    die("Missing required fields");

/* 🔹 GET VEHICLE STOCK CODE */
$stmt=$conn->prepare("SELECT stock_code FROM vehicles WHERE id=?");
$stmt->bind_param("i",$vehicle_id);
$stmt->execute();
$res=$stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$res) die("Vehicle not found");

$stock_code=$res['stock_code'];

/* 🔹 INSERT PART */
$stmt=$conn->prepare("
INSERT INTO vehicle_stripped_parts
(vehicle_id,stock_code,category_id,subcategory_id,type_id,component_id,part_name,part_condition,location)
VALUES (?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param("isiiiisss",
    $vehicle_id,
    $stock_code,
    $category_id,
    $subcategory_id,
    $type_id,
    $component_id,
    $part_name,
    $condition,
    $location
);

$stmt->execute();
$part_id=$stmt->insert_id;
$stmt->close();

/* CREATE UPLOAD FOLDER */
$dir="uploads/stripped_parts/".$part_id;
if(!is_dir($dir)) mkdir($dir,0777,true);

/* SAVE IMAGES */
if(!empty($_FILES['photos']['name'][0])){
    foreach($_FILES['photos']['tmp_name'] as $i=>$tmp){
        $name=uniqid()."_".basename($_FILES['photos']['name'][$i]);
        move_uploaded_file($tmp,"$dir/$name");

        $img=$conn->prepare("
            INSERT INTO stripped_part_images(part_id,file_name)
            VALUES (?,?)
        ");
        $img->bind_param("is",$part_id,$name);
        $img->execute();
        $img->close();
    }
}

/* REDIRECT */
header("Location: vehicle_stripping_entry.php?vehicle_id=".$vehicle_id);
exit;
