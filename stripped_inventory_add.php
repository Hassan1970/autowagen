<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$part_id    = isset($_GET['part_id']) ? (int)$_GET['part_id'] : 0;
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

if ($part_id <= 0) {
    die("No stripped part selected.");
}

// Load stripped part + vehicle info
$sql = "
    SELECT sp.*,
           v.stock_code,
           v.make,
           v.model
    FROM vehicle_stripped_parts sp
    LEFT JOIN vehicles v ON sp.vehicle_id = v.id
    WHERE sp.id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $part_id);
$stmt->execute();
$part = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$part) {
    die("Stripped part not found.");
}

// If vehicle_id not given, use from stripped part
if ($vehicle_id <= 0 && !empty($part['vehicle_id'])) {
    $vehicle_id = (int)$part['vehicle_id'];
}

// Check if already in inventory
$stmtChk = $conn->prepare("
    SELECT id 
    FROM stripped_inventory 
    WHERE stripped_part_id = ? 
    LIMIT 1
");
$stmtChk->bind_param("i", $part_id);
$stmtChk->execute();
$stmtChk->store_result();

if ($stmtChk->num_rows > 0) {
    // Already exists
    $stmtChk->close();
    header("Location: vehicle_profile.php?vehicle_id={$vehicle_id}&inv_exists=1");
    exit;
}
$stmtChk->close();

// Prepare insert
$stock_code     = $part['stock_code'] ?? null;
$part_name      = $part['part_name'] ?? '';
$category_id    = (int)($part['category_id'] ?? 0);
$subcategory_id = (int)($part['subcategory_id'] ?? 0);
$type_id        = (int)($part['type_id'] ?? 0);
$component_id   = (int)($part['component_id'] ?? 0);
$position_code  = $part['position_code'] ?? null;
$location       = $part['location'] ?? null;
$condition      = $part['part_condition'] ?? 'GOOD';
$qty            = (int)($part['qty'] ?? 1);
$notes          = $part['notes'] ?? '';

// Default prices 0 for now (can be edited later)
$cost_price     = 0.00;
$selling_price  = 0.00;

$insSql = "
    INSERT INTO stripped_inventory
    (
        stripped_part_id, vehicle_id, stock_code,
        part_name, category_id, subcategory_id, type_id, component_id,
        position_code, location, part_condition,
        qty, cost_price, selling_price, notes
    )
    VALUES
    (
        ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?
    )
";

$stmtIns = $conn->prepare($insSql);
if (!$stmtIns) {
    die("Prepare failed: " . $conn->error);
}

$stmtIns->bind_param(
    "iissiiiisssidds",
    $part_id,
    $vehicle_id,
    $stock_code,
    $part_name,
    $category_id,
    $subcategory_id,
    $type_id,
    $component_id,
    $position_code,
    $location,
    $condition,
    $qty,
    $cost_price,
    $selling_price,
    $notes
);

$stmtIns->execute();
$stmtIns->close();

header("Location: vehicle_profile.php?vehicle_id={$vehicle_id}&inv_added=1");
exit;
