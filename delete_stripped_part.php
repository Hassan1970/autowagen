<?php
require_once __DIR__ . "/config/config.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid ID.");
}

// Get vehicle_id & main photo for redirect & file delete
$stmt = $conn->prepare("
    SELECT vehicle_id, photo 
    FROM vehicle_stripped_parts 
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    die("Record not found.");
}

$vehicle_id = (int)$res['vehicle_id'];
$photo      = $res['photo'];

// Delete main photo file if exists
if (!empty($photo)) {
    $filePath = __DIR__ . "/uploads/stripped_parts/" . $photo;
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

// Delete gallery photos (if you add this feature later)
$delImg = $conn->prepare("DELETE FROM stripped_part_images WHERE part_id = ?");
$delImg->bind_param("i", $id);
$delImg->execute();

// Delete stripped part record
$del = $conn->prepare("DELETE FROM vehicle_stripped_parts WHERE id = ?");
$del->bind_param("i", $id);
$del->execute();

// ✅ REDIRECT TO VEHICLE PROFILE
header("Location: vehicle_profile.php?vehicle_id={$vehicle_id}&deleted=1");
exit;
