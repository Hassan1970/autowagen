<?php
/*********************************************************
 * VEHICLE STRIPPED ENTRY – SINGLE FILE (DEBUG + INSERT)
 *********************************************************/

// ---------- FORCE ERROR DISPLAY ----------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ---------- CONFIRM FILE EXECUTION ----------
echo "<h3>vehicle_stripped_entry.php IS RUNNING</h3>";

// ---------- SESSION ----------
require_once __DIR__ . "/_session.php";
echo "<p>Session loaded</p>";

// ---------- DATABASE ----------
require_once __DIR__ . "/config/db.php";

if (!isset($conn)) {
    die("<p style='color:red'>DB ERROR: \$conn not found</p>");
}

echo "<p>Database connected</p>";

// ---------- HANDLE SUBMIT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    echo "<p>Form submitted</p>";

    $vehicle_id     = (int)($_POST['vehicle_id'] ?? 0);
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $type_id        = (int)($_POST['type_id'] ?? 0);
    $component_id   = (int)($_POST['component_id'] ?? 0);

    $part_name      = trim($_POST['part_name'] ?? '');
    $part_number    = trim($_POST['part_number'] ?? '');
    $side           = trim($_POST['side'] ?? '');
    $part_condition = trim($_POST['part_condition'] ?? '');
    $price          = (float)($_POST['price'] ?? 0);
    $notes          = trim($_POST['notes'] ?? '');
    $description    = trim($_POST['description'] ?? '');

    if ($vehicle_id === 0 || $part_name === '') {
        die("<p style='color:red'>Missing required fields</p>");
    }

    $sql = "
        INSERT INTO stripped_inventory (
            vehicle_id,
            category_id,
            subcategory_id,
            type_id,
            component_id,
            part_name,
            part_number,
            side,
            part_condition,
            price,
            notes,
            description,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("<p style='color:red'>Prepare failed: " . $conn->error . "</p>");
    }

    $stmt->bind_param(
        "iiiiisssssds",
        $vehicle_id,
        $category_id,
        $subcategory_id,
        $type_id,
        $component_id,
        $part_name,
        $part_number,
        $side,
        $part_condition,
        $price,
        $notes,
        $description
    );

    if (!$stmt->execute()) {
        die("<p style='color:red'>Execute failed: " . $stmt->error . "</p>");
    }

    echo "<p style='color:green'>INSERT SUCCESSFUL</p>";
    $stmt->close();
}
?>

<hr>

<h3>Add Stripped Part</h3>

<form method="post">
    <label>Vehicle ID</label><br>
    <input type="number" name="vehicle_id" required><br><br>

    <label>Category ID</label><br>
    <input type="number" name="category_id"><br><br>

    <label>Subcategory ID</label><br>
    <input type="number" name="subcategory_id"><br><br>

    <label>Type ID</label><br>
    <input type="number" name="type_id"><br><br>

    <label>Component ID</label><br>
    <input type="number" name="component_id"><br><br>

    <label>Part Name</label><br>
    <input type="text" name="part_name" required><br><br>

    <label>Part Number</label><br>
    <input type="text" name="part_number"><br><br>

    <label>Side</label><br>
    <input type="text" name="side"><br><br>

    <label>Condition</label><br>
    <input type="text" name="part_condition"><br><br>

    <label>Price</label><br>
    <input type="number" step="0.01" name="price"><br><br>

    <label>Notes</label><br>
    <textarea name="notes"></textarea><br><br>

    <label>Description</label><br>
    <textarea name="description"></textarea><br><br>

    <button type="submit">Add Part</button>
</form>
