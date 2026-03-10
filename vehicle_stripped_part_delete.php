<?php
require_once __DIR__ . "/config/config.php";

$part_id    = (int)($_GET['part_id'] ?? 0);
$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);

if ($part_id <= 0 || $vehicle_id <= 0) {
    die("Invalid request");
}

/*
|--------------------------------------------------------------------------
| 1️⃣ Fetch image filenames
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT file_name
    FROM stripped_part_images
    WHERE part_id = ?
");
$stmt->bind_param("i", $part_id);
$stmt->execute();
$res = $stmt->get_result();

$images = [];
while ($row = $res->fetch_assoc()) {
    $images[] = $row['file_name'];
}
$stmt->close();

/*
|--------------------------------------------------------------------------
| 2️⃣ Delete image files from disk
|--------------------------------------------------------------------------
| Folder: /uploads/stripped_parts/{part_id}/
*/
$baseDir = __DIR__ . "/uploads/stripped_parts/" . $part_id . "/";

foreach ($images as $img) {
    $filePath = $baseDir . $img;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

/* Remove directory if empty */
if (is_dir($baseDir)) {
    @rmdir($baseDir);
}

/*
|--------------------------------------------------------------------------
| 3️⃣ Delete image records
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    DELETE FROM stripped_part_images
    WHERE part_id = ?
");
$stmt->bind_param("i", $part_id);
$stmt->execute();
$stmt->close();

/*
|--------------------------------------------------------------------------
| 4️⃣ Delete stripped part
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    DELETE FROM vehicle_stripped_parts
    WHERE id = ?
");
$stmt->bind_param("i", $part_id);
$stmt->execute();
$stmt->close();

/*
|--------------------------------------------------------------------------
| 5️⃣ Redirect back to vehicle
|--------------------------------------------------------------------------
*/
header("Location: vehicle_view.php?id=" . $vehicle_id);
exit;
