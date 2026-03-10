<?php
require_once "config/config.php";

$id = intval($_GET['id']);
$vehicle_id = intval($_GET['vehicle_id']);

if ($id <= 0 || $vehicle_id <= 0) {
    die("Invalid request.");
}

// Find file to delete
$sql = "SELECT file_name FROM vehicle_documents WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $file = "uploads/vehicle_docs/" . $row['file_name'];

    if (file_exists($file)) {
        unlink($file);
    }
}

// Delete database record
$sql = "DELETE FROM vehicle_documents WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

// Redirect back
header("Location: vehicle_profile.php?vehicle_id=" . $vehicle_id . "&paper_deleted=1");
exit;
?>
