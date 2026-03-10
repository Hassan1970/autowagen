<?php
require_once __DIR__ . '/config/config.php';

$vehicle_id = (int)$_POST['vehicle_id'];
$customer_id = (int)$_POST['customer_id'];

$conn->query("DELETE FROM vehicle_purchases WHERE vehicle_id = $vehicle_id");

$stmt = $conn->prepare("
INSERT INTO vehicle_purchases (vehicle_id, customer_id)
VALUES (?, ?)
");
$stmt->bind_param("ii", $vehicle_id, $customer_id);
$stmt->execute();

header("Location: vehicle_view.php?id=$vehicle_id");
exit;
