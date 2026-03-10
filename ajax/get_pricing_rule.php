<?php
require_once __DIR__ . '/../config/config.php';

$category_id = (int)($_GET['category_id'] ?? 0);
$condition   = $_GET['condition'] ?? '';

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
$res = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'price' => $res['suggested_price'] ?? null
]);
