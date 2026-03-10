<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set("display_errors", 1);

/*
---------------------------------------------------------
  1) READ MAIN INVOICE FIELDS
---------------------------------------------------------
*/
$supplier_id    = $_POST['supplier_id'] ?? 0;
$invoice_number = $_POST['invoice_number'] ?? '';
$invoice_date   = $_POST['invoice_date'] ?? '';
$notes          = $_POST['notes'] ?? '';

if ($supplier_id == 0) {
    die("Supplier missing.");
}

/*
---------------------------------------------------------
  2) INSERT MAIN INVOICE INTO CORRECT TABLE:
     suppliers_invoices
---------------------------------------------------------
*/
$sql = "
INSERT INTO suppliers_invoices
(supplier_id, invoice_number, invoice_date, notes)
VALUES (?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $supplier_id, $invoice_number, $invoice_date, $notes);

if (!$stmt->execute()) {
    die("Failed to save invoice header: " . $stmt->error);
}

$invoice_id = $stmt->insert_id;
$stmt->close();

/*
---------------------------------------------------------
  3) READ INVOICE ITEMS
---------------------------------------------------------
*/
$part_ids          = $_POST['part_id'] ?? [];
$qtys              = $_POST['qty'] ?? [];
$prices            = $_POST['supplier_price'] ?? [];
$supp_part_numbers = $_POST['supplier_part_number'] ?? [];

if (count($part_ids) == 0) {
    die("No invoice items provided.");
}

/*
---------------------------------------------------------
  4) PREPARE INSERT FOR ITEMS
     correct table: suppliers_invoice_items
---------------------------------------------------------
*/
$sqlItem = "
INSERT INTO suppliers_invoice_items
(invoice_id, part_id, qty, supplier_price, supplier_part_number)
VALUES (?, ?, ?, ?, ?)
";

$stmtItem = $conn->prepare($sqlItem);

/*
---------------------------------------------------------
  5) LOOP THROUGH ITEMS & SAVE
---------------------------------------------------------
*/
for ($i = 0; $i < count($part_ids); $i++) {

    $pid   = (int)$part_ids[$i];
    $qty   = (int)$qtys[$i];
    $price = (float)$prices[$i];
    $spn   = $supp_part_numbers[$i];

    if ($pid <= 0 || $qty <= 0) continue;

    // Insert invoice item
    $stmtItem->bind_param("iiids", $invoice_id, $pid, $qty, $price, $spn);
    if (!$stmtItem->execute()) {
        die("Error inserting invoice item: " . $stmtItem->error);
    }

    /*
    ---------------------------------------------------------
      6) UPDATE STOCK IN replacement_parts
    ---------------------------------------------------------
    */
    $conn->query("
        UPDATE replacement_parts 
        SET stock_qty = stock_qty + $qty 
        WHERE id = $pid
    ");
}

$stmtItem->close();

/*
---------------------------------------------------------
  7) REDIRECT
---------------------------------------------------------
*/
header("Location: replacement_purchase_add.php?success=1");
exit;

?>
