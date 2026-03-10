<?php
require_once "../../config/config.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id   = isset($data['id']) ? (int)$data['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid supplier ID']);
    exit;
}

$sql = "SELECT id, supplier_name, contact_person, phone, email, address,
               vat_number, company_reg, id_document, proof_address, notes
        FROM suppliers
        WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Supplier not found']);
    exit;
}

echo json_encode(['success' => true, 'data' => $row]);
