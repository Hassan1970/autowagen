<?php
require_once __DIR__ . "/../config/config.php";

$type_id = isset($_GET['type_id']) ? (int)$_GET['type_id'] : 0;

$sql = "
    SELECT 
        c.id,
        CONCAT(
            CASE 
                WHEN c.name LIKE '%Assembly%' THEN '[Assembly] '
                ELSE '[Component] '
            END,
            c.name
        ) AS label
    FROM components c
    WHERE c.type_id = ?
      AND c.is_sellable = 1
    ORDER BY c.name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $type_id);
$stmt->execute();

$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
exit;
