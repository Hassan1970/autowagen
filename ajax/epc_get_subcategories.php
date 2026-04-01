<?php
require_once __DIR__ . "/../config/config.php";

// Get category ID safely
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Prepare SQL
$sql = "
    SELECT id, name
    FROM subcategories
    WHERE category_id = ?
    ORDER BY name
";

$stmt = $conn->prepare($sql);

// Safety check (optional but good)
if (!$stmt) {
    echo json_encode([
        'results' => [],
        'error' => 'SQL prepare failed'
    ]);
    exit;
}

$stmt->bind_param("i", $category_id);
$stmt->execute();

$res = $stmt->get_result();

// Build result array
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

// Return JSON in expected format
header('Content-Type: application/json');
echo json_encode([
    'results' => $data
]);

exit;