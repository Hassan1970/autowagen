<?php
require_once "../../config/config.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id           = isset($data['id']) ? (int)$data['id'] : 0;
$name         = trim($data['supplier_name']  ?? '');
$contact      = trim($data['contact_person'] ?? '');
$phone        = trim($data['phone']          ?? '');
$email        = trim($data['email']          ?? '');
$address      = trim($data['address']        ?? '');
$vat          = trim($data['vat_number']     ?? '');
$company_reg  = trim($data['company_reg']    ?? '');
$id_document  = trim($data['id_document']    ?? '');
$proof        = trim($data['proof_address']  ?? '');
$notes        = trim($data['notes']          ?? '');

if ($id <= 0 || $name === '') {
    echo json_encode(['success' => false, 'message' => 'Missing supplier ID or name']);
    exit;
}

$sql = "UPDATE suppliers SET
        supplier_name=?, contact_person=?, phone=?, email=?, address=?,
        vat_number=?, company_reg=?, id_document=?, proof_address=?, notes=?
        WHERE id=?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param(
    "ssssssssssi",
    $name, $contact, $phone, $email, $address,
    $vat, $company_reg, $id_document, $proof, $notes,
    $id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
