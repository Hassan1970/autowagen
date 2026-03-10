<?php
require_once __DIR__ . "/config/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: oem_parts_list.php");
    exit;
}

$oem_number  = trim($_POST['oem_number'] ?? '');
$part_name   = trim($_POST['part_name'] ?? '');
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$subcategory_id = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
$type_id     = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : null;
$component_id= !empty($_POST['component_id']) ? (int)$_POST['component_id'] : null;
$stock_qty   = isset($_POST['stock_qty']) ? (int)$_POST['stock_qty'] : 0;
$cost_price  = isset($_POST['cost_price']) ? (float)$_POST['cost_price'] : 0;
$selling_price = isset($_POST['selling_price']) ? (float)$_POST['selling_price'] : 0;
$supplier_oem_invoice_id = !empty($_POST['supplier_oem_invoice_id']) ? (int)$_POST['supplier_oem_invoice_id'] : null;

if ($part_name === '') {
    die("Part name is required.");
}

$sql = "
    INSERT INTO oem_parts
        (oem_number, part_name, category_id, subcategory_id, type_id, component_id,
         stock_qty, cost_price, selling_price, supplier_oem_invoice_id)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssiiiiidd" . "i",
    $oem_number,
    $part_name,
    $category_id,
    $subcategory_id,
    $type_id,
    $component_id,
    $stock_qty,
    $cost_price,
    $selling_price,
    $supplier_oem_invoice_id
);

// Because supplier_oem_invoice_id can be NULL, adjust binding if needed:
if ($supplier_oem_invoice_id === null) {
    // quick workaround: set as NULL via separate query
    $stmt->bind_param(
        "ssiiiiidd" . "i",
        $oem_number,
        $part_name,
        $category_id,
        $subcategory_id,
        $type_id,
        $component_id,
        $stock_qty,
        $cost_price,
        $selling_price,
        $supplier_oem_invoice_id
    );
}

if (!$stmt->execute()) {
    die("Error saving OEM part: " . $stmt->error);
}
$stmt->close();

// If linked to an invoice, go back to that invoice view
if ($supplier_oem_invoice_id) {
    header("Location: oem_purchase_view.php?id=" . $supplier_oem_invoice_id);
} else {
    header("Location: oem_parts_list.php");
}
exit;
