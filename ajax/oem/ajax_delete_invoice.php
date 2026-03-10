<?php
require_once "../../config/config.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);

$conn->query("DELETE FROM supplier_oem_invoices WHERE id = $id");

echo json_encode([
    "success" => true,
    "message" => "Invoice deleted"
]);
