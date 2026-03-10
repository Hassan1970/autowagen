<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) die("Invalid ID.");

$sql = "
UPDATE replacement_parts SET
    part_number = ?,
    part_name = ?,
    category_id = ?,
    subcategory_id = ?,
    type_id = ?,
    component_id = ?,
    cost_price = ?,
    selling_price = ?,
    stock_qty = ?,
    notes = ?
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssiiiiidisi",
    $_POST['part_number'],
    $_POST['part_name'],
    $_POST['category_id'],
    $_POST['subcategory_id'],
    $_POST['type_id'],
    $_POST['component_id'],
    $_POST['cost_price'],
    $_POST['selling_price'],
    $_POST['stock_qty'],
    $_POST['notes'],
    $id
);

if ($stmt->execute()) {
    header("Location: replacement_part_view.php?id=" . $id);
    exit;
} else {
    die("Update failed: " . $stmt->error);
}
