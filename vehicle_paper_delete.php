<?php
require_once "config/config.php";

$id = intval($_GET['id']);
$vehicle_id = intval($_GET['vehicle_id']);

if ($id <= 0 || $vehicle_id <= 0) {
    die("Invalid request.");
}

// find file name
$stmt = $conn->prepare("SELECT file_name FROM vehicle_papers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$file = $res->fetch_assoc()['file_name'] ?? '';
$stmt->close();

// delete file
if ($file) {
    $path = "uploads/vehicles/papers/" . $file;
    if (file_exists($path)) {
        unlink($path);
    }
}

// delete db row
$stmt = $conn->prepare("DELETE FROM vehicle_papers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: vehicle_profile.php?vehicle_id=" . $vehicle_id . "&paper_deleted=1");
exit;
?>
