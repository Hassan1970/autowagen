<?php
require_once "config/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: oem_parts_list.php");
    exit;
}

$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$oem_number    = trim($_POST['oem_number'] ?? "");
$part_name     = trim($_POST['part_name'] ?? "");
$category_id   = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$subcategory_id= !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
$type_id       = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : null;
$component_id  = !empty($_POST['component_id']) ? (int)$_POST['component_id'] : null;
$stock_qty     = isset($_POST['stock_qty']) ? (int)$_POST['stock_qty'] : 0;
$cost_price    = isset($_POST['cost_price']) ? (float)$_POST['cost_price'] : 0;
$selling_price = isset($_POST['selling_price']) ? (float)$_POST['selling_price'] : 0;

if ($id <= 0) {
    die("Invalid OEM part.");
}
if ($oem_number === "" || $part_name === "") {
    die("OEM Number and Part Name are required.");
}

$sql = "
    UPDATE oem_parts
    SET
        oem_number    = ?,
        part_name     = ?,
        category_id   = ?,
        subcategory_id= ?,
        type_id       = ?,
        component_id  = ?,
        stock_qty     = ?,
        cost_price    = ?,
        selling_price = ?
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssiiiiiddi",
    $oem_number,
    $part_name,
    $category_id,
    $subcategory_id,
    $type_id,
    $component_id,
    $stock_qty,
    $cost_price,
    $selling_price,
    $id
);

if (!$stmt->execute()) {
    die("Error updating OEM part: " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: oem_parts_list.php");
exit;
?>
