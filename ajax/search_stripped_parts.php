<?php
require_once __DIR__ . '/../config/config.php';

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT 
        si.id,
        si.part_name,
        si.selling_price
    FROM stripped_inventory si
    WHERE 
        si.part_name LIKE CONCAT('%', ?, '%')
        OR si.id = ?
    LIMIT 10
";

$stmt = $conn->prepare($sql);
$id = is_numeric($q) ? (int)$q : 0;
$stmt->bind_param("si", $q, $id);
$stmt->execute();

$res = $stmt->get_result();
$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
