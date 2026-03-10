<?php
require_once __DIR__ . '/../config/config.php';

$q = trim($_GET['q'] ?? '');
$data = [];

if ($q !== '') {
    $like = '%' . $q . '%';

    $stmt = $conn->prepare("
        SELECT id, stock_code, make, model, year
        FROM vehicles
        WHERE stock_code LIKE ?
           OR make LIKE ?
           OR model LIKE ?
           OR year LIKE ?
        ORDER BY id DESC
        LIMIT 10
    ");
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($data);
