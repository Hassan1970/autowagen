<?php
require_once __DIR__ . "/../config/config.php";

$subcategory_id = isset($_GET['subcategory_id']) ? (int)$_GET['subcategory_id'] : 0;

$sql = "
    SELECT id, name
    FROM types
    WHERE subcategory_id = ?
    ORDER BY name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();

$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
exit;
