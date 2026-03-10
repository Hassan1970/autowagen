<?php
require_once __DIR__ . "/../config/db.php";

if (!isset($_GET['subcategory_id'])) {
    echo json_encode([]);
    exit;
}

$subcategory_id = (int)$_GET['subcategory_id'];

$stmt = $conn->prepare("
    SELECT id, name
    FROM types
    WHERE subcategory_id = ?
      AND is_sellable = 1
    ORDER BY name
");
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();

$result = $stmt->get_result();
$types = [];

while ($row = $result->fetch_assoc()) {
    $types[] = $row;
}

header("Content-Type: application/json");
echo json_encode($types);
