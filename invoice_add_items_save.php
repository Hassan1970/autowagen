<?php
require_once __DIR__ . '/config/config.php';

$invoice_id = $_POST['invoice_id'];
$part_type  = $_POST['part_type'];
$part_name  = $_POST['part_name'];
$cost_price = $_POST['cost_price'];
$encoded    = encode_cost_secure($cost_price);
$qty        = $_POST['qty'] ?? 1;
$notes      = $_POST['notes'] ?? null;

// Stripped vehicle (optional)
$related_vehicle_id = $_POST['related_vehicle_id'] ?? null;

// Category etc.
$category_id = $_POST['category_id'] ?? null;
$subcategory_id = $_POST['subcategory_id'] ?? null;
$type_id = $_POST['type_id'] ?? null;
$component_id = $_POST['component_id'] ?? null;

// Insert item
$stmt = $conn->prepare("
    INSERT INTO invoice_items 
    (invoice_id, part_type, part_name, category_id, subcategory_id, type_id, component_id,
     related_vehicle_id, cost_price, encoded_cost, qty, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "issiiiiisdss",
    $invoice_id, $part_type, $part_name, $category_id, $subcategory_id,
    $type_id, $component_id, $related_vehicle_id,
    $cost_price, $encoded, $qty, $notes
);
$stmt->execute();

$item_id = $stmt->insert_id;

// Handle photos
if (!empty($_FILES['photos']['name'][0])) {
    $folder = "uploads/invoice_items/";
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    foreach ($_FILES['photos']['name'] as $i => $name) {
        $tmp = $_FILES['photos']['tmp_name'][$i];
        $new = time() . "_" . rand(1000,9999) . "_" . $name;
        move_uploaded_file($tmp, $folder . $new);

        $conn->query("INSERT INTO invoice_item_photos (item_id, file_name) VALUES ($item_id, '$new')");
    }
}

header("Location: invoice_add_items.php?invoice_id=$invoice_id");
exit;
