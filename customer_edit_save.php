<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    die('Invalid customer');
}

$name      = trim($_POST['name'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$id_number = trim($_POST['id_number'] ?? '');
$address   = trim($_POST['address'] ?? '');

if ($name === '' || $phone === '') {
    die('Name and phone required');
}

$uploadDir = __DIR__ . '/uploads/customers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$idDocPath = null;
$proofPath = null;

if (!empty($_FILES['id_document']['name'])) {
    $idDocPath = 'uploads/customers/' . time() . '_id_' . basename($_FILES['id_document']['name']);
    move_uploaded_file($_FILES['id_document']['tmp_name'], __DIR__ . '/' . $idDocPath);
}

if (!empty($_FILES['proof_residence']['name'])) {
    $proofPath = 'uploads/customers/' . time() . '_proof_' . basename($_FILES['proof_residence']['name']);
    move_uploaded_file($_FILES['proof_residence']['tmp_name'], __DIR__ . '/' . $proofPath);
}

$sql = "
    UPDATE customers SET
        name = ?,
        phone = ?,
        id_number = ?,
        address = ?
";

$params = [$name, $phone, $id_number, $address];
$types  = "ssss";

if ($idDocPath !== null) {
    $sql .= ", id_document_file = ?";
    $params[] = $idDocPath;
    $types .= "s";
}

if ($proofPath !== null) {
    $sql .= ", proof_residence_file = ?";
    $params[] = $proofPath;
    $types .= "s";
}

$sql .= " WHERE id = ?";
$params[] = $id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

header("Location: customer_list.php");
exit;
