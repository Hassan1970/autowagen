<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$part_id    = isset($_POST['part_id']) ? (int)$_POST['part_id'] : 0;
$vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;

if ($part_id <= 0) {
    die("No part specified.");
}

$baseDir  = __DIR__ . "/uploads/stripped_parts/" . $part_id;
$mainDir  = $baseDir . "/main";
$thumbDir = $baseDir . "/thumbs";

// Create directories if not exist
if (!is_dir($baseDir))  mkdir($baseDir, 0777, true);
if (!is_dir($mainDir))  mkdir($mainDir, 0777, true);
if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);

$allowedExt = ['jpg','jpeg','png','gif','webp'];

foreach ($_FILES['photos']['error'] as $idx => $err) {
    if ($err !== UPLOAD_ERR_OK) {
        continue;
    }

    $tmpName = $_FILES['photos']['tmp_name'][$idx];
    $name    = $_FILES['photos']['name'][$idx];

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        continue;
    }

    // Generate safe unique filename
    $newName = time() . "_" . mt_rand(1000,9999) . "." . $ext;
    $destMain = $mainDir . "/" . $newName;
    $destThumb = $thumbDir . "/" . $newName;

    if (!move_uploaded_file($tmpName, $destMain)) {
        continue;
    }

    // Create thumbnail
    createThumbnail($destMain, $destThumb, 300, 220);
}

// Redirect back
header("Location: stripped_part_photos.php?part_id=" . $part_id . "&vehicle_id=" . $vehicle_id);
exit;

/**
 * Simple thumbnail creator using GD
 */
function createThumbnail($src, $dest, $maxW, $maxH) {
    $info = getimagesize($src);
    if (!$info) return false;

    list($w, $h) = $info;
    $type = $info[2]; // IMAGETYPE_*

    switch ($type) {
        case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($src); break;
        case IMAGETYPE_PNG:  $img = imagecreatefrompng($src);  break;
        case IMAGETYPE_GIF:  $img = imagecreatefromgif($src);  break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                $img = imagecreatefromwebp($src);
                break;
            }
        default: return false;
    }

    // Calculate new size
    $ratio = min($maxW / $w, $maxH / $h);
    if ($ratio > 1) $ratio = 1;
    $newW = (int)($w * $ratio);
    $newH = (int)($h * $ratio);

    $thumb = imagecreatetruecolor($newW, $newH);

    // Preserve transparency for PNG/GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0,0,0,127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }

    imagecopyresampled($thumb, $img, 0,0,0,0, $newW, $newH, $w,$h);

    switch ($type) {
        case IMAGETYPE_JPEG: imagejpeg($thumb, $dest, 85); break;
        case IMAGETYPE_PNG:  imagepng($thumb, $dest); break;
        case IMAGETYPE_GIF:  imagegif($thumb, $dest); break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagewebp')) imagewebp($thumb, $dest, 85);
            break;
    }

    imagedestroy($img);
    imagedestroy($thumb);
    return true;
}
