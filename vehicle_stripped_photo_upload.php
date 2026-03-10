<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set("display_errors", 1);

$part_id = (int)($_POST['part_id'] ?? 0);

if ($part_id <= 0) {
    die("Invalid part id");
}

if (!isset($_FILES['photo'])) {
    die("No file uploaded");
}

$upload_root = __DIR__ . "/uploads/stripped_parts";
$part_dir    = $upload_root . "/" . $part_id;

if (!is_dir($upload_root)) {
    mkdir($upload_root, 0755, true);
}

if (!is_dir($part_dir)) {
    mkdir($part_dir, 0755, true);
}

$ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','webp'];

if (!in_array($ext, $allowed)) {
    die("Invalid file type");
}

$filename = time() . "_" . basename($_FILES['photo']['name']);
$target   = $part_dir . "/" . $filename;

if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
    die("Failed to save file");
}

$relative_path = "uploads/stripped_parts/" . $part_id . "/" . $filename;

/* Save into DB */
$stmt = $conn->prepare("
    INSERT INTO stripped_part_images (part_id, file_name)
    VALUES (?, ?)
");
$stmt->bind_param("is", $part_id, $relative_path);
$stmt->execute();
$stmt->close();

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
