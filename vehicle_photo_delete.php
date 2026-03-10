<?php
require_once __DIR__ . "/config/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$photo_id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
$return_to  = isset($_GET['return_to']) ? trim($_GET['return_to']) : 'profile';

if ($photo_id <= 0 || $vehicle_id <= 0) {
    die("Invalid request.");
}

// Get file_name
$stmt = $conn->prepare("SELECT file_name FROM vehicle_photos WHERE id = ? AND vehicle_id = ? LIMIT 1");
$stmt->bind_param("ii", $photo_id, $vehicle_id);
$stmt->execute();
$res  = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($res) {
    $file = $res['file_name'];

    $baseDir  = __DIR__ . "/uploads/vehicles/photos/";
    $thumbDir = $baseDir . "thumbs/";

    $mainPath  = $baseDir  . $file;
    $thumbPath = $thumbDir . "thumb_" . $file;

    if (is_file($mainPath))  { @unlink($mainPath); }
    if (is_file($thumbPath)) { @unlink($thumbPath); }

    // Delete DB row
    $del = $conn->prepare("DELETE FROM vehicle_photos WHERE id = ? AND vehicle_id = ? LIMIT 1");
    $del->bind_param("ii", $photo_id, $vehicle_id);
    $del->execute();
    $del->close();
}

// Redirect
if ($return_to === 'view') {
    header("Location: vehicle_view.php?id=" . $vehicle_id . "&photo_deleted=1");
} else {
    header("Location: vehicle_profile.php?vehicle_id=" . $vehicle_id . "&photo_deleted=1");
}
exit;
