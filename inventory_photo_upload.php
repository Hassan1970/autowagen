<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$inv_id = isset($_POST['inv_id']) ? (int)$_POST['inv_id'] : 0;
if ($inv_id <= 0) {
    die("Invalid inventory ID.");
}

// Check inventory exists
$stmt = $conn->prepare("SELECT id FROM stripped_inventory WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $inv_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    die("Inventory item not found.");
}

// Prepare folder
$folder = __DIR__ . "/uploads/inventory/" . $inv_id;
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// Handle upload
if (!empty($_FILES['photos']['name'][0])) {
    $count = count($_FILES['photos']['name']);

    for ($i = 0; $i < $count; $i++) {
        $name     = $_FILES['photos']['name'][$i];
        $tmp_name = $_FILES['photos']['tmp_name'][$i];
        $error    = $_FILES['photos']['error'][$i];

        if ($error !== UPLOAD_ERR_OK) {
            continue;
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            continue;
        }

        $newName = uniqid("inv_{$inv_id}_") . "." . $ext;
        $dest = $folder . "/" . $newName;
        move_uploaded_file($tmp_name, $dest);
    }
}

header("Location: inventory_photos.php?id=" . $inv_id);
exit;
