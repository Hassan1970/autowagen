<?php
require_once "config/config.php";

// Get POST fields
$supplier_id    = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
$invoice_date   = $_POST['invoice_date']   ?? '';
$invoice_number = $_POST['invoice_number'] ?? '';
$total_amount   = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
$account_type   = $_POST['account_type']   ?? 'Cash';
$reference      = $_POST['reference']      ?? null;
$notes          = $_POST['notes']          ?? null;

// Basic validation
if ($supplier_id <= 0 || !$invoice_date || !$invoice_number) {
    die("Missing required fields.");
}

$sql = "INSERT INTO supplier_oem_invoices
        (supplier_id, invoice_number, invoice_date, reference, notes, total_amount, account_type)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "issssds",
    $supplier_id,
    $invoice_number,
    $invoice_date,
    $reference,
    $notes,
    $total_amount,
    $account_type
);

if ($stmt->execute()) {
    // After saving, go back to list page (or you can create a view page later)
    header("Location: oem_purchase_list.php");
    exit;
} else {
    die("Insert failed: " . $stmt->error);
}
