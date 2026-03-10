<?php
require_once __DIR__ . '/config/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: third_party_list.php");
    exit;
}

// Delete photo file (if exists)
$uploadDir = __DIR__ . '/uploads/third_party_parts/';
$res = $conn->query("SELECT photo FROM third_party_parts WHERE id = $id");
$row = $res ? $res->fetch_assoc() : null;
if ($row && $row['photo']) {
    $file = $uploadDir . $row['photo'];
    if (is_file($file)) {
        @unlink($file);
    }
}

// Delete DB row
$stmt = $conn->prepare("DELETE FROM third_party_parts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: third_party_list.php");
exit;
