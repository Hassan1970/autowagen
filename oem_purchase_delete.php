<?php
require_once __DIR__ . "/config/config.php";

$invoice_id = (int)$_GET['invoice_id'];
if ($invoice_id <= 0) die("Invalid invoice");

// Reverse stock first
$items = $conn->query("
    SELECT part_id, qty
    FROM supplier_oem_invoice_items
    WHERE invoice_id = $invoice_id
");

while ($i = $items->fetch_assoc()) {
    $conn->query("
        UPDATE oem_parts
        SET stock_qty = stock_qty - {$i['qty']}
        WHERE id = {$i['part_id']}
    ");
}

// Delete items
$conn->query("DELETE FROM supplier_oem_invoice_items WHERE invoice_id=$invoice_id");

// Delete invoice
$conn->query("DELETE FROM supplier_oem_invoices WHERE id=$invoice_id");

echo "<script>alert('Invoice deleted & stock reversed');window.location='oem_purchase_items_list.php';</script>";
