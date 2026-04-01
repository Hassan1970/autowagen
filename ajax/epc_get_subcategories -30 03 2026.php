<?php
require_once __DIR__ . "/../config/config.php";

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$sql = "
    SELECT id, name
    FROM subcategories
    WHERE category_id = ?
    ORDER BY name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();

$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
exit;
