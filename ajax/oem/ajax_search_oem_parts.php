<?php
require_once "../../config/config.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$q             = trim($data['q'] ?? '');
$search_field  = $data['search_field'] ?? 'both';
$category_id   = !empty($data['category_id'])    ? (int)$data['category_id']    : null;
$subcategory_id= !empty($data['subcategory_id']) ? (int)$data['subcategory_id'] : null;
$type_id       = !empty($data['type_id'])        ? (int)$data['type_id']        : null;
$component_id  = !empty($data['component_id'])   ? (int)$data['component_id']   : null;
$invoice_id    = !empty($data['invoice_id'])     ? (int)$data['invoice_id']     : null;
$supplier_id   = !empty($data['supplier_id'])    ? (int)$data['supplier_id']    : null;
$cost_min      = isset($data['cost_min']) && $data['cost_min'] !== '' ? (float)$data['cost_min'] : null;
$cost_max      = isset($data['cost_max']) && $data['cost_max'] !== '' ? (float)$data['cost_max'] : null;
$in_stock_only = !empty($data['in_stock_only']);

$where  = "1=1";
$params = [];
$types  = "";

// Search text
if ($q !== '') {
    if ($search_field === 'oem') {
        $where .= " AND p.oem_number LIKE ?";
        $types .= "s";
        $params[] = "%{$q}%";
    } elseif ($search_field === 'name') {
        $where .= " AND p.part_name LIKE ?";
        $types .= "s";
        $params[] = "%{$q}%";
    } else { // both
        $where .= " AND (p.oem_number LIKE ? OR p.part_name LIKE ?)";
        $types .= "ss";
        $like = "%{$q}%";
        $params[] = $like;
        $params[] = $like;
    }
}

// EPC filters
if ($category_id !== null) {
    $where .= " AND p.category_id = ?";
    $types .= "i";
    $params[] = $category_id;
}
if ($subcategory_id !== null) {
    $where .= " AND p.subcategory_id = ?";
    $types .= "i";
    $params[] = $subcategory_id;
}
if ($type_id !== null) {
    $where .= " AND p.type_id = ?";
    $types .= "i";
    $params[] = $type_id;
}
if ($component_id !== null) {
    $where .= " AND p.component_id = ?";
    $types .= "i";
    $params[] = $component_id;
}

// Invoice filter
if ($invoice_id !== null) {
    $where .= " AND p.supplier_oem_invoice_id = ?";
    $types .= "i";
    $params[] = $invoice_id;
}

// Supplier filter (via invoice)
if ($supplier_id !== null) {
    $where .= " AND s.id = ?";
    $types .= "i";
    $params[] = $supplier_id;
}

// Cost range
if ($cost_min !== null) {
    $where .= " AND p.cost_price >= ?";
    $types .= "d";
    $params[] = $cost_min;
}
if ($cost_max !== null) {
    $where .= " AND p.cost_price <= ?";
    $types .= "d";
    $params[] = $cost_max;
}

// In-stock filter
if ($in_stock_only) {
    $where .= " AND p.stock_qty > 0";
}

// Limit results to avoid huge payload
$limit = 200;

$sql = "
    SELECT 
        p.id,
        p.oem_number,
        p.part_name,
        p.stock_qty,
        p.cost_price,
        p.selling_price,
        c.name      AS category_name,
        sc.name     AS subcategory_name,
        t.name      AS type_name,
        comp.name   AS component_name,
        i.id        AS invoice_id,
        i.invoice_number,
        i.invoice_date,
        s.supplier_name
    FROM oem_parts p
    LEFT JOIN categories     c    ON p.category_id = c.id
    LEFT JOIN subcategories  sc   ON p.subcategory_id = sc.id
    LEFT JOIN types          t    ON p.type_id = t.id
    LEFT JOIN components     comp ON p.component_id = comp.id
    LEFT JOIN supplier_oem_invoices i ON p.supplier_oem_invoice_id = i.id
    LEFT JOIN suppliers      s    ON i.supplier_id = s.id
    WHERE $where
    ORDER BY p.id DESC
    LIMIT ?
";

$types .= "i";
$params[] = $limit;

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($row = $res->fetch_assoc()) {
    // Make sure numeric fields are properly typed
    $row['id']            = (int)$row['id'];
    $row['stock_qty']     = (int)$row['stock_qty'];
    $row['cost_price']    = (float)$row['cost_price'];
    $row['selling_price'] = (float)$row['selling_price'];
    $row['invoice_id']    = $row['invoice_id'] !== null ? (int)$row['invoice_id'] : null;
    $rows[] = $row;
}

$stmt->close();

echo json_encode([
    "success" => true,
    "parts"   => $rows
]);
