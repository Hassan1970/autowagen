<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// -------------------------------
// GET inventory ID
// -------------------------------
$inv_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($inv_id <= 0) {
    die("<h2 style='color:red; text-align:center;'>Invalid Inventory ID</h2>");
}

// -------------------------------
// LOAD inventory row (to confirm it exists)
// -------------------------------
$stmt = $conn->prepare("SELECT vehicle_id FROM stripped_inventory WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $inv_id);
$stmt->execute();
$res = $stmt->get_result();
$item = $res->fetch_assoc();
$stmt->close();

if (!$item) {
    die("<h2 style='color:red; text-align:center;'>Inventory item not found.</h2>");
}

$vehicle_id = (int)$item['vehicle_id'];

// -------------------------------
// DELETE INVENTORY ITEM
// -------------------------------
$stmt = $conn->prepare("DELETE FROM stripped_inventory WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $inv_id);
$stmt->execute();
$stmt->close();

// -------------------------------
// REMOVE PHOTOS (if any)
// -------------------------------
$folder = __DIR__ . "/uploads/inventory/" . $inv_id;

if (is_dir($folder)) {
    $files = glob($folder . "/*");
    foreach ($files as $f) {
        if (is_file($f)) unlink($f);
    }
    rmdir($folder);
}

// -------------------------------
// REDIRECT BACK
// -------------------------------
header("Location: stripped_inventory_list.php?deleted=1");
exit;

?>
