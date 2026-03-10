<?php
require_once __DIR__ . "/config/config.php";

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

if ($id <= 0 || $vehicle_id <= 0) {
    die("Invalid request.");
}

// ---------------------------------------------
// VERIFY RECORD BELONGS TO THIS VEHICLE
// ---------------------------------------------
$check = $conn->prepare("
    SELECT photo 
    FROM vehicle_stripped_parts 
    WHERE id = ? AND vehicle_id = ? 
    LIMIT 1
");
$check->bind_param("ii", $id, $vehicle_id);
$check->execute();
$res  = $check->get_result();
$part = $res->fetch_assoc();
$check->close();

if (!$part) {
    die("Record not found or does not belong to vehicle.");
}

// ---------------------------------------------
// DELETE PHOTO (if exists and path is valid)
// ---------------------------------------------
if (!empty($part['photo'])) {
    // If `photo` column stores a relative path like "uploads/stripping/xxx.jpg"
    $filepath = __DIR__ . "/" . $part['photo'];

    if (file_exists($filepath)) {
        @unlink($filepath);
    }
}

// ---------------------------------------------
// DELETE DATABASE RECORD
// ---------------------------------------------
$del = $conn->prepare("DELETE FROM vehicle_stripped_parts WHERE id = ?");
$del->bind_param("i", $id);
$del->execute();
$del->close();

// ---------------------------------------------
// REDIRECT BACK TO THE LIST PAGE
// ---------------------------------------------
header("Location: vehicle_stripping_list.php?vehicle_id=" . $vehicle_id);
exit;
?>
