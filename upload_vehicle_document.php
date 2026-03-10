<?php
require_once "config/config.php";

// Validate inputs
$vehicle_id = intval($_POST['vehicle_id']);
$document_type = $_POST['document_type'] ?? '';

if ($vehicle_id <= 0 || empty($document_type)) {
    die("Missing vehicle or document type.");
}

// Validate file
if (!isset($_FILES['doc_file']) || $_FILES['doc_file']['error'] !== 0) {
    die("No file selected.");
}

// Create upload directory if missing
$upload_dir = "uploads/vehicle_docs/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// File name
$filename = time() . "_" . basename($_FILES['doc_file']['name']);
$target = $upload_dir . $filename;

// Upload file
if (!move_uploaded_file($_FILES['doc_file']['tmp_name'], $target)) {
    die("Failed to upload file.");
}

// Insert record
$sql = "INSERT INTO vehicle_documents (vehicle_id, document_type, file_name)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $vehicle_id, $document_type, $filename);
$stmt->execute();

// Redirect back
header("Location: vehicle_profile.php?vehicle_id=" . $vehicle_id . "&paper_uploaded=1");
exit;
?>
