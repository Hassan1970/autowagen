<?php
require_once __DIR__ . '/config/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: supplier_invoice_items_list.php");
    exit;
}

// Delete photos from disk + DB
$photoRes = $conn->query("SELECT file_name FROM invoice_item_photos WHERE item_id = $id");
$uploadDir = __DIR__ . '/uploads/invoice_items/';

while ($p = $photoRes->fetch_assoc()) {
    $file = $uploadDir . $p['file_name'];
    if (is_file($file)) {
        @unlink($file);
    }
}
$conn->query("DELETE FROM invoice_item_photos WHERE item_id = $id");

// Delete the item
$stmt = $conn->prepare("DELETE FROM supplier_invoice_items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: supplier_invoice_items_list.php");
exit;
