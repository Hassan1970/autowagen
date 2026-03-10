<?php
require_once __DIR__ . "/config/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: oem_parts_list.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    die("Invalid part ID.");
}

// Fetch OLD quantity
$oldQ = $conn->query("SELECT stock_qty FROM oem_parts WHERE id = $id");
if (!$oldQ || $oldQ->num_rows === 0) {
    die("Part not found.");
}
$old_qty = (int)$oldQ->fetch_assoc()['stock_qty'];

// NEW values
$oem_number  = trim($_POST['oem_number'] ?? '');
$part_name   = trim($_POST['part_name'] ?? '');
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$subcategory_id = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
$type_id     = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : null;
$component_id= !empty($_POST['component_id']) ? (int)$_POST['component_id'] : null;

$stock_qty   = isset($_POST['stock_qty']) ? (int)$_POST['stock_qty'] : 0;
$cost_price  = isset($_POST['cost_price']) ? (float)$_POST['cost_price'] : 0;
$selling_price = isset($_POST['selling_price']) ? (float)$_POST['selling_price'] : 0;

$invoice_id = !empty($_POST['supplier_oem_invoice_id'])
    ? (int)$_POST['supplier_oem_invoice_id']
    : null;

/* ---------------------------------------------
   UPDATE PART
---------------------------------------------- */
$sql = "
    UPDATE oem_parts
    SET oem_number=?, part_name=?, category_id=?, subcategory_id=?, type_id=?,
        component_id=?, stock_qty=?, cost_price=?, selling_price=?,
        supplier_oem_invoice_id=?
    WHERE id=?
";

$stm = $conn->prepare($sql);
$stm->bind_param(
    "ssiiiiiddii",
    $oem_number,
    $part_name,
    $category_id,
    $subcategory_id,
    $type_id,
    $component_id,
    $stock_qty,
    $cost_price,
    $selling_price,
    $invoice_id,
    $id
);

if (!$stm->execute()) {
    die("Error saving part: " . $stm->error);
}
$stm->close();

/* ---------------------------------------------
   STOCK ADJUSTMENT MOVEMENT
---------------------------------------------- */
if ($stock_qty != $old_qty) {

    $diff = $stock_qty - $old_qty;

    if ($diff > 0) {
        $type = "ADJUST_PLUS";
        $qty = $diff;
    } else {
        $type = "ADJUST_MINUS";
        $qty = abs($diff);
    }

    $mov = $conn->prepare("
        INSERT INTO oem_stock_movements
        (part_id, movement_type, qty, reference, invoice_id)
        VALUES (?, ?, ?, 'Manual edit adjustment', ?)
    ");
    $mov->bind_param("isii", $id, $type, $qty, $invoice_id);
    $mov->execute();
    $mov->close();
}

/* ---------------------------------------------
   REDIRECT
---------------------------------------------- */
header("Location: oem_parts_list.php");
exit;
