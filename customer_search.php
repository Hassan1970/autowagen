<?php
require_once __DIR__ . "/config/config.php";

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode([]);
    exit;
}

$search = "%$q%";

/* ================================
   SEARCH CUSTOMERS
================================ */

$stmt = $conn->prepare("
SELECT 
    id,
    full_name AS name,
    phone,
    address
FROM customers
WHERE
      full_name LIKE ?
   OR phone LIKE ?
ORDER BY full_name
LIMIT 20
");

$stmt->bind_param("ss",$search,$search);
$stmt->execute();

$res = $stmt->get_result();
$data = [];

while($row = $res->fetch_assoc()){
    $data[] = [
        "id"      => $row["id"],
        "name"    => $row["name"],
        "phone"   => $row["phone"],
        "address" => $row["address"]
    ];
}

$stmt->close();

echo json_encode($data);