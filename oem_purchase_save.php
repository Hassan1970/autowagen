<?php
require_once __DIR__ . "/config/config.php";

if (
    !isset($_POST['supplier_id']) ||
    !isset($_POST['invoice_number']) ||
    !isset($_POST['invoice_date']) ||
    !isset($_POST['oem_number'])
) {
    die("Missing required fields.");
}

$supplier_id    = (int)$_POST['supplier_id'];
$invoice_number = trim($_POST['invoice_number']);
$invoice_date   = trim($_POST['invoice_date']);

if ($supplier_id <= 0 || $invoice_number === "" || $invoice_date === "") {
    die("Invalid values.");
}

// 1. INSERT MAIN INVOICE
$sql = "
INSERT INTO supplier_oem_invoices (supplier_id, invoice_number, invoice_date, total_amount)
VALUES (?, ?, ?, 0)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $supplier_id, $invoice_number, $invoice_date);
$stmt->execute();

$invoice_id = $stmt->insert_id;


// 2. PREPARE ITEM INSERT
$item_sql = "
INSERT INTO supplier_oem_invoice_items
(invoice_id, oem_part_id, oem_number, part_name, category_id, subcategory_id, type_id, component_id,
 cost_price, selling_price, qty, line_total)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
";

$item_stmt = $conn->prepare($item_sql);

$total_invoice = 0;

// MULTIPLE ROWS
$rows = count($_POST['oem_number']);

for ($i = 0; $i < $rows; $i++) {

    $oem_number     = trim($_POST['oem_number'][$i]);
    $part_name      = trim($_POST['part_name'][$i]);

    $category_id     = (int)$_POST['category_id'][$i];
    $subcategory_id  = (int)$_POST['subcategory_id'][$i];
    $type_id         = (int)$_POST['type_id'][$i];
    $component_id    = (int)$_POST['component_id'][$i];

    $cost_price      = (float)$_POST['cost_price'][$i];
    $selling_price   = (float)$_POST['selling_price'][$i];
    $qty             = (int)$_POST['qty'][$i];

    if ($oem_number === "" || $part_name === "" || $qty <= 0) continue;

    // 3. CHECK IF OEM PART EXISTS
    $check = $conn->prepare("SELECT id FROM oem_parts WHERE oem_number = ?");
    $check->bind_param("s", $oem_number);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $oem_part_id = $res->fetch_assoc()['id'];

        // UPDATE STOCK
        $conn->query("UPDATE oem_parts SET stock_qty = stock_qty + $qty WHERE id = $oem_part_id");
    }
    else {
        // AUTO-CREATE NEW OEM PART
        $create_sql = "
        INSERT INTO oem_parts 
        (oem_number, part_name, category_id, subcategory_id, type_id, component_id, stock_qty, cost_price, selling_price)
        VALUES (?,?,?,?,?,?,?,?,?)
        ";
        $create_stmt = $conn->prepare($create_sql);
        $create_stmt->bind_param(
            "ssiiiiidd",
            $oem_number, $part_name, $category_id, $subcategory_id, $type_id, $component_id,
            $qty, $cost_price, $selling_price
        );
        $create_stmt->execute();

        $oem_part_id = $create_stmt->insert_id;
    }

    // LINE TOTAL
    $line_total = $cost_price * $qty;
    $total_invoice += $line_total;

    // 4. INSERT INTO supplier_oem_invoice_items
    $item_stmt->bind_param(
        "iisssiiiiddi",
        $invoice_id,
        $oem_part_id,
        $oem_number,
        $part_name,
        $category_id,
        $subcategory_id,
        $type_id,
        $component_id,
        $cost_price,
        $selling_price,
        $qty,
        $line_total
    );
    $item_stmt->execute();
}

// 5. UPDATE INVOICE TOTAL
$u = $conn->prepare("UPDATE supplier_oem_invoices SET total_amount = ? WHERE id = ?");
$u->bind_param("di", $total_invoice, $invoice_id);
$u->execute();

// 6. REDIRECT TO INVOICE VIEW PAGE
header("Location: oem_purchase_view.php?invoice_id=" . $invoice_id);
exit;

?>
