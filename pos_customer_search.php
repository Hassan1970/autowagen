<?php
require_once __DIR__ . "/config/config.php";

header('Content-Type: application/json; charset=utf-8');

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q === '') {
    echo json_encode([]);
    exit;
}

/*
MATCHES YOUR REAL customers TABLE:

id
name
phone
id_number
address
*/

$sql = "
    SELECT 
        id,
        name,
        phone,
        id_number,
        address
    FROM customers
    WHERE name LIKE ?
       OR phone LIKE ?
       OR address LIKE ?
    ORDER BY name
    LIMIT 20
";

$like = '%' . $q . '%';

$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $like, $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$out = [];

while ($row = $res->fetch_assoc()) {

    $out[] = [
        'id'        => (int)$row['id'],
        'full_name' => $row['name'], // keep frontend compatible
        'phone'     => $row['phone'] ?? '',
        'id_number' => $row['id_number'] ?? '',
        'address'   => $row['address'] ?? ''
    ];
}

$stmt->close();

echo json_encode($out);
