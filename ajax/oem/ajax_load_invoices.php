<?php
require_once "../../config/config.php";

$data = json_decode(file_get_contents("php://input"), true);

$where = "1=1";
$params = [];
$types = "";

// Supplier filter
if (!empty($data['supplier_id'])) {
    $where .= " AND s.id = ?";
    $types .= "i";
    $params[] = $data['supplier_id'];
}

// Date range
if (!empty($data['date_from'])) {
    $where .= " AND i.invoice_date >= ?";
    $types .= "s";
    $params[] = $data['date_from'];
}
if (!empty($data['date_to'])) {
    $where .= " AND i.invoice_date <= ?";
    $types .= "s";
    $params[] = $data['date_to'];
}

// Account Type
if (!empty($data['account_type'])) {
    $where .= " AND i.account_type = ?";
    $types .= "s";
    $params[] = $data['account_type'];
}

// Invoice number search
if (!empty($data['search_invoice'])) {
    $where .= " AND i.invoice_number LIKE ?";
    $types .= "s";
    $params[] = "%" . $data['search_invoice'] . "%";
}

$sql = "
    SELECT i.id, i.invoice_number, i.invoice_date, i.total_amount,
           i.account_type, s.supplier_name
    FROM supplier_oem_invoices i
    LEFT JOIN suppliers s ON s.id = i.supplier_id
    WHERE $where
    ORDER BY i.id DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode([
    "success" => true,
    "invoices" => $rows
]);
