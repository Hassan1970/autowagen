<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json');

/* Allow only POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

/* Read JSON input */
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

/* Validate required fields */
$id = isset($data['id']) ? (int)$data['id'] : 0;
$name = trim($data['name'] ?? '');
$phone = trim($data['phone'] ?? '');
$id_number = trim($data['id_number'] ?? '');
$address = trim($data['address'] ?? '');

if ($id <= 0 || $name === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

/* Update customer */
$stmt = $conn->prepare("
    UPDATE customers
    SET
        name = ?,
        phone = ?,
        id_number = ?,
        address = ?
    WHERE id = ?
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param(
    "ssssi",
    $name,
    $phone,
    $id_number,
    $address,
    $id
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Update failed']);
    exit;
}

echo json_encode(['success' => true]);
exit;
