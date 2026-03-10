<?php
require_once __DIR__ . "/config/config.php";
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode([]);
    exit;
}

$search = "%$q%";
$results = [];

function pushRow(&$results, $row, $priority)
{
    $row['priority'] = $priority;

    // Build clean vehicle_name
    $vehicleParts = array_filter([
        $row['make'] ?? '',
        $row['model'] ?? '',
        $row['year'] ?? ''
    ]);

    $row['vehicle_name'] = implode(' ', $vehicleParts);

    // Ensure stock code exists
    if (!isset($row['vehicle_stock_code'])) {
        $row['vehicle_stock_code'] = $row['code'] ?? '';
    }

    $results[] = $row;
}

/* ================= STRIPPED ================= */
$stmt = $conn->prepare("
SELECT
vsp.id,
vsp.part_name AS name,
vsp.stock_code AS vehicle_stock_code,
vsp.stock_code AS code,
IFNULL(vsp.qty,1) AS qty,
0 AS price,
v.make,
v.model,
v.year,
v.variant,
v.colour,
'STRIP' AS source
FROM vehicle_stripped_parts vsp
LEFT JOIN vehicles v ON v.id = vsp.vehicle_id
WHERE
vsp.part_name LIKE ?
OR vsp.stock_code LIKE ?
OR v.make LIKE ?
OR v.model LIKE ?
LIMIT 25
");

$stmt->bind_param("ssss", $search, $search, $search, $search);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    pushRow($results, $r, 1);
}
$stmt->close();

/* ================= THIRD PARTY ================= */
$stmt = $conn->prepare("
SELECT
id,
description AS name,
CONCAT('TP-',id) AS code,
'' AS vehicle_stock_code,
1 AS qty,
selling_price AS price,
'' AS make,
'' AS model,
'' AS year,
'' AS variant,
'' AS colour,
'TP' AS source
FROM third_party_parts
WHERE stock_status='IN_STOCK'
AND (description LIKE ? OR CONCAT('TP-',id) LIKE ?)
LIMIT 25
");

$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    pushRow($results, $r, 2);
}
$stmt->close();

/* ================= OEM ================= */
$stmt = $conn->prepare("
SELECT
id,
part_name AS name,
CONCAT('OEM-',id) AS code,
'' AS vehicle_stock_code,
stock_qty AS qty,
selling_price AS price,
'' AS make,
'' AS model,
'' AS year,
'' AS variant,
'' AS colour,
'OEM' AS source
FROM oem_parts
WHERE stock_qty > 0
AND (part_name LIKE ? OR CONCAT('OEM-',id) LIKE ?)
LIMIT 25
");

$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    pushRow($results, $r, 3);
}
$stmt->close();

/* ================= NEW ================= */
$stmt = $conn->prepare("
SELECT
id,
part_name AS name,
CONCAT('NEW-',id) AS code,
'' AS vehicle_stock_code,
stock_qty AS qty,
selling_price AS price,
'' AS make,
'' AS model,
'' AS year,
'' AS variant,
'' AS colour,
'NEW' AS source
FROM replacement_parts
WHERE stock_qty > 0
AND (part_name LIKE ? OR CONCAT('NEW-',id) LIKE ?)
LIMIT 25
");

$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    pushRow($results, $r, 4);
}
$stmt->close();

/* ================= SORT ================= */
usort($results, function ($a, $b) {
    if ($a['priority'] != $b['priority']) {
        return $a['priority'] <=> $b['priority'];
    }
    return $b['qty'] <=> $a['qty'];
});

/* ================= OUTPUT ================= */
echo json_encode(array_slice($results, 0, 40));