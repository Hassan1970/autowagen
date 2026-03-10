<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// -------------------------------------------------
// 1. Get vehicle_id
// -------------------------------------------------
$vehicle_id = isset($_GET['vehicle_id'])
    ? (int)$_GET['vehicle_id']
    : (int)($_POST['vehicle_id'] ?? 0);

if ($vehicle_id <= 0) {
    die("No vehicle selected.");
}

// -------------------------------------------------
// 2. Load vehicle (we use VIN, make, model, year if needed)
// -------------------------------------------------
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    die("Vehicle not found.");
}

// -------------------------------------------------
// 3. Find existing EPC components already inserted
// -------------------------------------------------
$existingCompIds = [];

$stmtEx = $conn->prepare("
    SELECT component_id
    FROM vehicle_stripped_parts
    WHERE vehicle_id = ?
");
$stmtEx->bind_param("i", $vehicle_id);
$stmtEx->execute();
$resEx = $stmtEx->get_result();

while ($row = $resEx->fetch_assoc()) {
    if (!empty($row['component_id'])) {
        $existingCompIds[(int)$row['component_id']] = true;
    }
}
$stmtEx->close();

// -------------------------------------------------
// 4. Load ALL EPC components (full structure)
//    category → subcategory → type → component
// -------------------------------------------------
$sqlEpc = "
    SELECT 
        c.id AS category_id,
        sc.id AS subcategory_id,
        t.id AS type_id,
        comp.id AS component_id,
        comp.name AS component_name
    FROM components comp
    INNER JOIN types t ON comp.type_id = t.id
    INNER JOIN subcategories sc ON t.subcategory_id = sc.id
    INNER JOIN categories c ON sc.category_id = c.id
    ORDER BY c.name, sc.name, t.name, comp.name
";

$resEpc = $conn->query($sqlEpc);
if (!$resEpc) {
    die("EPC query failed: " . $conn->error);
}

// -------------------------------------------------
// 5. Insert missing EPC components into stripped parts
// -------------------------------------------------
$conn->begin_transaction();

$insertedCount = 0;

$insertSql = "
    INSERT INTO vehicle_stripped_parts
        (vehicle_id, category_id, subcategory_id, type_id, component_id,
         part_name, qty, part_condition, location, notes, date_stripped)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmtIns = $conn->prepare($insertSql);
if (!$stmtIns) {
    $conn->rollback();
    die("Prepare failed: " . $conn->error);
}

$today = date('Y-m-d');

while ($row = $resEpc->fetch_assoc()) {

    $catId   = (int)$row['category_id'];
    $subId   = (int)$row['subcategory_id'];
    $typeId  = (int)$row['type_id'];
    $compId  = (int)$row['component_id'];
    $compName= $row['component_name'];

    // Skip already inserted components
    if (isset($existingCompIds[$compId])) {
        continue;
    }

    // Default values
    $qty          = 1;
    $condition    = "UNKNOWN";
    $location     = "";
    $notes        = "";
    $dateStripped = $today;

    $stmtIns->bind_param(
        "iiiississss",
        $vehicle_id,
        $catId,
        $subId,
        $typeId,
        $compId,
        $compName,
        $qty,
        $condition,
        $location,
        $notes,
        $dateStripped
    );

    $stmtIns->execute();
    $insertedCount++;
}

$stmtIns->close();
$conn->commit();

// -------------------------------------------------
// 6. Redirect back with message
// -------------------------------------------------

header("Location: vehicle_stripping_entry.php?vehicle_id={$vehicle_id}&auto_epc=1&inserted={$insertedCount}");
exit;

?>
