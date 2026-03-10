<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set("display_errors", 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access.");
}

$part_id    = isset($_POST['part_id']) ? (int)$_POST['part_id'] : 0;
$vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;

if ($part_id <= 0 || $vehicle_id <= 0) {
    die("Invalid stripped part.");
}

// Collect inputs
$category_id     = (int)($_POST['category_id'] ?? 0);
$subcategory_id  = (int)($_POST['subcategory_id'] ?? 0);
$type_id         = (int)($_POST['type_id'] ?? 0);
$component_id    = (int)($_POST['component_id'] ?? 0);

$qty             = (int)($_POST['qty'] ?? 1);
$condition       = trim($_POST['part_condition'] ?? "");
$location        = trim($_POST['location'] ?? "");
$notes           = trim($_POST['notes'] ?? "");

// Validate
if ($category_id <= 0 || $subcategory_id <= 0 || $type_id <= 0 || $component_id <= 0) {
    die("Missing EPC fields.");
}

// Load component name for part_name
$stmt = $conn->prepare("SELECT name FROM components WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $component_id);
$stmt->execute();
$res = $stmt->get_result();
$comp = $res->fetch_assoc();
$stmt->close();

$part_name = $comp ? $comp['name'] : "Unknown Component";

// Update stripped part
$sql = "
    UPDATE vehicle_stripped_parts
    SET 
        category_id     = ?,
        subcategory_id  = ?,
        type_id         = ?,
        component_id    = ?,
        part_name       = ?,
        qty             = ?,
        part_condition  = ?,
        location        = ?,
        notes           = ?
    WHERE id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iiiisisssi",
    $category_id,
    $subcategory_id,
    $type_id,
    $component_id,
    $part_name,
    $qty,
    $condition,
    $location,
    $notes,
    $part_id
);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: vehicle_profile.php?vehicle_id={$vehicle_id}&updated=1");
    exit;
} else {
    die("Database update error: " . $stmt->error);
}
?>
