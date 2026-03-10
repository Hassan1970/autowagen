<?php
/*********************************************************
 * VEHICLE STRIPPING – ADD PART (EPC-AWARE, FINAL BASE)
 *********************************************************/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --------------------------------------------------
// DB CONNECTION
// --------------------------------------------------
require_once __DIR__ . "/config/config.php";
if (!isset($conn)) {
    die("Database connection not found.");
}

// --------------------------------------------------
// VEHICLE CONTEXT
// --------------------------------------------------
$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);
if ($vehicle_id <= 0) {
    die("Invalid vehicle.");
}

// --------------------------------------------------
// HANDLE FORM SUBMIT
// --------------------------------------------------
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // EPC references
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $type_id        = (int)($_POST['type_id'] ?? 0);
    $component_id   = (int)($_POST['component_id'] ?? 0);

    // Part data
    $part_name     = trim($_POST['part_name'] ?? '');
    $stock_code    = trim($_POST['stock_code'] ?? '');
    $side          = strtoupper(trim($_POST['side'] ?? ''));
    $condition     = trim($_POST['part_condition'] ?? '');
    $selling_price = (float)($_POST['selling_price'] ?? 0);
    $notes         = trim($_POST['notes'] ?? '');

    // ---------------- VALIDATION ----------------
    if ($category_id <= 0 || $subcategory_id <= 0 || $type_id <= 0 || $component_id <= 0) {
        $message = "Category, Subcategory, Type and Component are required.";
    } elseif ($part_name === '') {
        $message = "Part name is required.";
    } elseif ($side !== '' && !in_array($side, ['LHS','RHS'])) {
        $message = "Side must be LHS or RHS.";
    } else {

        // ---------------- INSERT ----------------
        $sql = "
            INSERT INTO stripped_inventory (
                vehicle_id,
                category_id,
                subcategory_id,
                type_id,
                component_id,
                part_name,
                stock_code,
                side,
                part_condition,
                qty,
                cost_price,
                selling_price,
                sold_status,
                notes,
                created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0.00, ?, 'AVAILABLE', ?, NOW()
            )
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "iiiiisssssds",
            $vehicle_id,
            $category_id,
            $subcategory_id,
            $type_id,
            $component_id,
            $part_name,
            $stock_code,
            $side,
            $condition,
            $selling_price,
            $notes
        );

        if ($stmt->execute()) {
            $message = "Stripped part added successfully.";
        } else {
            $message = "Insert failed: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vehicle Stripping – Add Part</title>
    <style>
        body { font-family: Arial, sans-serif; }
        label { display:block; margin-top:10px; }
        input, textarea { width:300px; }
        .msg { margin:10px 0; font-weight:bold; }
    </style>
</head>
<body>

<h2>Vehicle Stripping – Add Part</h2>

<p><strong>Vehicle ID:</strong> <?= $vehicle_id ?></p>

<?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post">

    <!-- EPC REFERENCES -->
    <label>Category ID *</label>
    <input type="number" name="category_id" required>

    <label>Subcategory ID *</label>
    <input type="number" name="subcategory_id" required>

    <label>Type ID *</label>
    <input type="number" name="type_id" required>

    <label>Component ID *</label>
    <input type="number" name="component_id" required>

    <!-- PART DATA -->
    <label>Part Name *</label>
    <input type="text" name="part_name" required>

    <label>Stock Code / Part Number</label>
    <input type="text" name="stock_code">

    <label>Side (LHS / RHS)</label>
    <input type="text" name="side" placeholder="LHS or RHS">

    <label>Condition</label>
    <input type="text" name="part_condition">

    <label>Selling Price</label>
    <input type="number" step="0.01" name="selling_price">

    <label>Notes</label>
    <textarea name="notes"></textarea>

    <br><br>
    <button type="submit">Add Stripped Part</button>

</form>

</body>
</html>
