<?php
require_once __DIR__ . "/config/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: oem_purchase_list.php");
    exit;
}

$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$supplier_id   = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
$invoice_date  = $_POST['invoice_date'] ?? '';
$invoice_number= trim($_POST['invoice_number'] ?? '');
$total_amount  = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
$account_type  = $_POST['account_type'] ?? 'Cash';
$reference     = trim($_POST['reference'] ?? '');
$notes         = trim($_POST['notes'] ?? '');

if ($id <= 0 || $supplier_id <= 0 || $invoice_date === '' || $invoice_number === '') {
    die("Missing required fields.");
}

$sql = "
    UPDATE supplier_oem_invoices
    SET supplier_id = ?,
        invoice_number = ?,
        invoice_date = ?,
        total_amount = ?,
        account_type = ?,
        reference = ?,
        notes = ?
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "issdsssi",
    $supplier_id,
    $invoice_number,
    $invoice_date,
    $total_amount,
    $account_type,
    $reference,
    $notes,
    $id
);

if (!$stmt->execute()) {
    die("Error updating invoice: " . $stmt->error);
}
$stmt->close();

// go back to view
header("Location: oem_purchase_view.php?id=" . $id);
exit;
