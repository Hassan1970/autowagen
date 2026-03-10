<?php
require_once __DIR__ . "/config/config.php";
error_reporting(E_ALL);
ini_set("display_errors", 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

// Sanitize inputs
$vehicle_id     = intval($_POST['vehicle_id'] ?? 0);
$category_id    = intval($_POST['category_id'] ?? 0);
$subcategory_id = intval($_POST['subcategory_id'] ?? 0);
$type_id        = intval($_POST['type_id'] ?? 0);
$component_id   = intval($_POST['component_id'] ?? 0);
$part_name      = trim($_POST['part_name'] ?? '');
$qty            = intval($_POST['qty'] ?? 1);
$condition      = trim($_POST['part_condition'] ?? 'GOOD');
$location       = trim($_POST['location'] ?? '');
$notes          = trim($_POST['notes'] ?? '');

// Basic validation
if ($vehicle_id <= 0) { die("Vehicle not selected."); }
if ($part_name === "") { die("Part Name is required."); }

// --------------------------------------------------
// PHOTO UPLOAD (optional)
// --------------------------------------------------
$photoFileName = null;

if (!empty($_FILES['photo']['name'])) {

    $uploadDir = __DIR__ . "/uploads/stripped_parts/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tmpName = $_FILES['photo']['tmp_name'];
    $origName = $_FILES['photo']['name'];
    $fileSize = $_FILES['photo']['size'];
    $fileType = mime_content_type($tmpName);

    // Allow basic image types
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (isset($allowed[$fileType])) {
        $ext = $allowed[$fileType];
    } else {
        $ext = pathinfo($origName, PATHINFO_EXTENSION);
    }

    // Limit size (eg. 8MB)
    if ($fileSize > 8 * 1024 * 1024) {
        die("Photo too large (max 8MB).");
    }

    // Generate safe unique filename
    $photoFileName = "strip_{$vehicle_id}_" . time() . "." . $ext;
    $destPath = $uploadDir . $photoFileName;

    if (!move_uploaded_file($tmpName, $destPath)) {
        // If upload fails, we still save part, just without photo
        $photoFileName = null;
    }
}

// --------------------------------------------------
// INSERT STRIPPED PART
// --------------------------------------------------
$sql = "INSERT INTO vehicle_stripped_parts
(
    vehicle_id,
    category_id,
    subcategory_id,
    type_id,
    component_id,
    part_name,
    qty,
    part_condition,
    location,
    notes,
    photo
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iiiiisissss",
    $vehicle_id,
    $category_id,
    $subcategory_id,
    $type_id,
    $component_id,
    $part_name,
    $qty,
    $condition,
    $location,
    $notes,
    $photoFileName
);

if ($stmt->execute()) {
    // Back to stripping page for SAME vehicle
    header("Location: vehicle_stripped_entry.php?vehicle_id=" . $vehicle_id . "&saved=1");
    exit;
} else {
    echo "Database Error: " . $stmt->error;
}
