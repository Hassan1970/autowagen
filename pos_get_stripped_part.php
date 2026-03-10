<?php
require_once __DIR__ . "/config/config.php";

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "
SELECT
    sp.id,
    sp.part_name
FROM vehicle_stripped_parts sp
WHERE sp.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode([]);
}