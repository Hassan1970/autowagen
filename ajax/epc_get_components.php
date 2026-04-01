<?php
require_once __DIR__ . "/../config/config.php";

if (!isset($_GET['type_id'])) {
    echo json_encode(['results' => []]); // ✅ FIX
    exit;
}

$type_id = (int)$_GET['type_id'];

$stmt = $conn->prepare("
    SELECT id, name
    FROM components
    WHERE type_id = ?
    ORDER BY name ASC
");

if (!$stmt) {
    echo json_encode(['results' => []]);
    exit;
}

$stmt->bind_param("i", $type_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();

header("Content-Type: application/json");
echo json_encode(['results' => $data]); // ✅ FIX

exit;