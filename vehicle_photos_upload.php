<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ------------------------------------------------------------------
// SAFETY
// ------------------------------------------------------------------
$vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
$return_to  = $_POST['return_to'] ?? 'profile';

if ($vehicle_id <= 0) {
    die("Invalid vehicle ID.");
}

// ------------------------------------------------------------------
// FOLDERS
// ------------------------------------------------------------------
$mainDir  = __DIR__ . "/uploads/vehicles/photos/";
$thumbDir = $mainDir . "thumbs/";

if (!is_dir($mainDir))  mkdir($mainDir, 0777, true);
if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);

// ------------------------------------------------------------------
// THUMBNAIL FUNCTION
// ------------------------------------------------------------------
function make_thumb($src, $dest, $targetWidth = 300)
{
    $info = getimagesize($src);
    if (!$info) return false;

    list($w, $h) = $info;
    $mime = $info['mime'];

    // Create image resource based on type
    switch ($mime) {
        case 'image/jpeg':
            $img = imagecreatefromjpeg($src);
            break;
        case 'image/png':
            $img = imagecreatefrompng($src);
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
            break;
        case 'image/gif':
            $img = imagecreatefromgif($src);
            break;
        default:
            return false;
    }

    $ratio = $h / $w;
    $targetHeight = intval($targetWidth * $ratio);

    $thumb = imagecreatetruecolor($targetWidth, $targetHeight);

    // Preserve PNG transparency
    if ($mime === 'image/png') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }

    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $targetWidth, $targetHeight, $w, $h);

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($thumb, $dest, 85);
            break;
        case 'image/png':
            imagepng($thumb, $dest, 6);
            break;
        case 'image/gif':
            imagegif($thumb, $dest);
            break;
    }

    imagedestroy($img);
    imagedestroy($thumb);
    return true;
}

// ------------------------------------------------------------------
// PROCESS FILES
// ------------------------------------------------------------------
foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {

    if (!is_uploaded_file($tmpName)) continue;

    $originalName = basename($_FILES['photos']['name'][$key]);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        continue;
    }

    $newFile = "veh_" . $vehicle_id . "_" . time() . "_" . rand(1000,9999) . "." . $ext;

    $mainPath  = $mainDir . $newFile;
    $thumbPath = $thumbDir . "thumb_" . $newFile;

    // Save full-size file
    move_uploaded_file($tmpName, $mainPath);

    // Create thumbnail
    make_thumb($mainPath, $thumbPath, 300);

    // Insert into DB
    $stmt = $conn->prepare("
        INSERT INTO vehicle_photos (vehicle_id, file_name, sort_order) 
        VALUES (?, ?, 0)
    ");
    $stmt->bind_param("is", $vehicle_id, $newFile);
    $stmt->execute();
    $stmt->close();
}

// ------------------------------------------------------------------
// REDIRECT BACK
// ------------------------------------------------------------------
if ($return_to === "profile") {
    header("Location: vehicle_profile.php?vehicle_id=" . $vehicle_id . "&photos_uploaded=1");
} else {
    header("Location: vehicles_list.php");
}
exit;
?>
