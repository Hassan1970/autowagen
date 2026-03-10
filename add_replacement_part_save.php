<?php
require_once __DIR__ . "/config/config.php";

/**
 * SAFETY: Only allow POST
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

/**
 * REQUIRED FIELDS (component_id is OPTIONAL)
 */
$required = [
    'part_number',
    'part_name',
    'category_id',
    'subcategory_id',
    'type_id'
];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die("Error: Missing required fields.");
    }
}

/**
 * SANITIZE INPUTS
 */
$part_number   = trim($_POST['part_number']);
$part_name     = trim($_POST['part_name']);
$category_id   = (int) $_POST['category_id'];
$subcategory_id= (int) $_POST['subcategory_id'];
$type_id       = (int) $_POST['type_id'];

/**
 * COMPONENT IS OPTIONAL
 * If empty → store NULL
 */
$component_id = !empty($_POST['component_id'])
    ? (int) $_POST['component_id']
    : null;

/**
 * PRICES (OPTIONAL)
 */
$cost_price    = isset($_POST['cost_price']) && $_POST['cost_price'] !== ''
    ? (float) $_POST['cost_price']
    : 0.00;

$selling_price = isset($_POST['selling_price']) && $_POST['selling_price'] !== ''
    ? (float) $_POST['selling_price']
    : 0.00;

/**
 * STOCK (MAP opening_stock → stock_qty)
 */
$stock_qty = isset($_POST['opening_stock']) && $_POST['opening_stock'] !== ''
    ? (int) $_POST['opening_stock']
    : 0;

$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

/**
 * INSERT QUERY (MATCHES replacement_parts TABLE EXACTLY)
 */
$sql = "
INSERT INTO replacement_parts (
    part_number,
    part_name,
    cost_price,
    selling_price,
    stock_qty,
    notes,
    category_id,
    subcategory_id,
    type_id,
    component_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

/**
 * BIND PARAMS
 * component_id can be NULL → bind as NULL
 */
$stmt->bind_param(
    "ssddisiiii",
    $part_number,
    $part_name,
    $cost_price,
    $selling_price,
    $stock_qty,
    $notes,
    $category_id,
    $subcategory_id,
    $type_id,
    $component_id
);

/**
 * EXECUTE
 */
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$stmt->close();

/**
 * REDIRECT BACK WITH SUCCESS
 */
header("Location: add_replacement_part.php?success=1");
exit;
