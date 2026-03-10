<?php
require_once __DIR__ . '/config/config.php';

$supplier_id    = $_POST['supplier_id'] ?? null;
$invoice_number = $_POST['invoice_number'] ?? null;
$invoice_date   = $_POST['invoice_date'] ?? null;
$total_amount   = $_POST['total_amount'] ?? 0;
$notes          = $_POST['notes'] ?? null;

$stmt = $conn->prepare("
    INSERT INTO suppliers_invoices
    (supplier_id, invoice_number, invoice_date, total_amount, notes)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("issds", $supplier_id, $invoice_number, $invoice_date, $total_amount, $notes);
$stmt->execute();

// Get new invoice ID
$new_id = $stmt->insert_id;

$stmt->close();

// Redirect to add parts
header("Location: invoice_add_items.php?invoice_id=" . $new_id);
exit;
