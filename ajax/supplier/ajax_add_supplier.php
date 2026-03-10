<?php
require_once "../../config/config.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

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

if ($name === '') {
    echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
    exit;
}

$sql = "INSERT INTO suppliers
        (supplier_name, contact_person, phone, email, address, vat_number,
         company_reg, id_document, proof_address, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param(
    "ssssssssss",
    $name, $contact, $phone, $email, $address,
    $vat, $company_reg, $id_document, $proof, $notes
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
