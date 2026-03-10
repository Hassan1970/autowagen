<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$vehicle_id    = $_POST['vehicle_id'] ?? null;
$part_name     = $_POST['part_name'] ?? null;
$qty           = $_POST['qty'] ?? 1;
$condition     = $_POST['part_condition'] ?? 'Used';
$location      = $_POST['location'] ?? null;
$notes         = $_POST['notes'] ?? null;

if (!$vehicle_id || !$part_name) {
    die("Vehicle or Part missing");
}

/* get stock code from vehicles table */
$stmt = $conn->prepare("SELECT stock_code FROM vehicles WHERE id=?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$res = $stmt->get_result();
$v = $res->fetch_assoc();
$stmt->close();

if (!$v) {
    die("Vehicle not found");
}

$stock_code = $v['stock_code'];

/* insert stripped part */
$stmt = $conn->prepare("
INSERT INTO vehicle_stripped_parts
(vehicle_id, stock_code, part_name, qty, part_condition, location, notes)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ississs",
    $vehicle_id,
    $stock_code,
    $part_name,
    $qty,
    $condition,
    $location,
    $notes
);

$stmt->execute();
$stmt->close();

/* redirect back */
header("Location: stripped_inventory.php?success=1");
exit;
