<?php
require_once "config/config.php";

$vehicle_id = intval($_POST['vehicle_id']);
$doc_type   = $_POST['doc_type'] ?? '';

if ($vehicle_id <= 0 || $doc_type == '') {
    die("Missing vehicle or document type.");
}

if (!isset($_FILES['paper']) || $_FILES['paper']['error'] !== 0) {
    die("No file uploaded.");
}

$upload_dir = "uploads/vehicles/papers/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$original = basename($_FILES['paper']['name']);
$filename = time() . "_" . $original;
$path     = $upload_dir . $filename;

if (!move_uploaded_file($_FILES['paper']['tmp_name'], $path)) {
    die("Failed to move upload.");
}

$sql = "INSERT INTO vehicle_papers (vehicle_id, file_name, doc_type) 
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $vehicle_id, $filename, $doc_type);
$stmt->execute();

header("Location: vehicle_profile.php?vehicle_id=" . $vehicle_id . "&paper_uploaded=1");
exit;
?>
