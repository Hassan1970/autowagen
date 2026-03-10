<?php
/*********************************************************
 * AJAX: GET PRICING SUGGESTION
 * Phase 3.3 – FINAL LOCKED VERSION
 *********************************************************/

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';

/* ---------------- INPUT ---------------- */

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$condition   = isset($_GET['condition']) ? trim($_GET['condition']) : '';

/* ---------------- VALIDATION ---------------- */

if ($category_id <= 0 || $condition === '') {
    echo json_encode([
        'price' => null
    ]);
    exit;
}

/* ---------------- QUERY ---------------- */

$sql = "
    SELECT suggested_price
    FROM pricing_rules
    WHERE category_id = ?
      AND part_condition = ?
      AND is_active = 1
    ORDER BY id DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    // Fail silently – pricing must never block UI
    echo json_encode([
        'price' => null
    ]);
    exit;
}

$stmt->bind_param("is", $category_id, $condition);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();

$stmt->close();

/* ---------------- RESPONSE ---------------- */

echo json_encode([
    'price' => $result['suggested_price'] ?? null
]);
