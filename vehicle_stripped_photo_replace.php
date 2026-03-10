<?php
require_once __DIR__ . "/config/config.php";

$part_id    = (int)($_GET['part_id'] ?? 0);
$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);

if ($part_id <= 0 || $vehicle_id <= 0) {
    die("Invalid request.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ================= BASIC CHECK ================= */
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        die("No photo uploaded.");
    }

    $file = $_FILES['photo'];

    /* ================= VALIDATION RULES ================= */
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedExt = ['jpg', 'jpeg', 'png'];
    $allowedMime = ['image/jpeg', 'image/png'];

    /* ================= SIZE CHECK ================= */
    if ($file['size'] > $maxSize) {
        die("Image too large. Maximum allowed size is 5MB.");
    }

    /* ================= EXTENSION CHECK ================= */
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        die("Invalid file type. Only JPG and PNG images are allowed.");
    }

    /* ================= MIME CHECK (REAL FILE TYPE) ================= */
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowedMime, true)) {
        die("Invalid image file.");
    }

    /* ================= UPLOAD DIR ================= */
    $uploadDir = __DIR__ . "/uploads/stripped_parts/" . $part_id . "/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    /* ================= CHECK EXISTING IMAGE ================= */
    $stmt = $conn->prepare("
        SELECT id, file_name
        FROM stripped_part_images
        WHERE part_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $part_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    /* ================= SAVE FILE ================= */
    $fileName = "photo_" . time() . "." . $ext;
    $target = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        die("Failed to save uploaded image.");
    }

    /* ================= REPLACE OR INSERT ================= */
    if ($existing) {

        // delete old file
        $oldFile = $uploadDir . $existing['file_name'];
        if (!empty($existing['file_name']) && file_exists($oldFile)) {
            unlink($oldFile);
        }

        // update row
        $stmt = $conn->prepare("
            UPDATE stripped_part_images
            SET file_name = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $fileName, $existing['id']);
        $stmt->execute();
        $stmt->close();

    } else {

        // first image
        $stmt = $conn->prepare("
            INSERT INTO stripped_part_images (part_id, file_name)
            VALUES (?, ?)
        ");
        $stmt->bind_param("is", $part_id, $fileName);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: vehicle_view.php?id=" . $vehicle_id);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add / Replace Photo</title>
<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial;
}
.card {
    width:400px;
    margin:80px auto;
    background:#111;
    border:2px solid #b00000;
    padding:20px;
    border-radius:6px;
}
.btn-red {
    background:#b00000;
    color:#fff;
    padding:10px;
    border:none;
    border-radius:6px;
    width:100%;
    font-weight:bold;
    cursor:pointer;
}
input[type=file] {
    width:100%;
}
</style>
</head>

<body>
<div class="card">
<h2>Add / Replace Photo</h2>

<form method="post" enctype="multipart/form-data">
<input type="file" name="photo" accept=".jpg,.jpeg,.png" required><br><br>
<button class="btn-red">UPLOAD PHOTO</button>
</form>
</div>
</body>
</html>
