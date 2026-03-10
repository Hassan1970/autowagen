<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$category_id = (int)($_GET['category_id'] ?? 0);
$condition   = trim($_GET['condition'] ?? '');

if (!$category_id || !$condition) {
    echo json_encode(['price' => null]);
    exit;
}

$stmt = $conn->prepare("
    SELECT suggested_price
    FROM pricing_rules
    WHERE category_id = ?
      AND part_condition = ?
      AND is_active = 1
    ORDER BY id DESC
    LIMIT 1
");

$stmt->bind_param("is", $category_id, $condition);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'price' => $result['suggested_price'] ?? null
]);
