<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* -------------------------------------------------
   1. Get vehicle ID
------------------------------------------------- */
$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($vehicle_id <= 0) {
    die("Invalid vehicle ID");
}

/* -------------------------------------------------
   2. Load vehicle (NO legacy columns)
------------------------------------------------- */
$stmt = $conn->prepare("
    SELECT
        id,
        stock_code,
        make,
        model,
        variant,
        year,
        colour,
        mileage,
        purchase_use
    FROM vehicles
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    die("Vehicle not found");
}

/* -------------------------------------------------
   3. Save changes
------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stock_code   = trim($_POST['stock_code'] ?? '');
    $make         = trim($_POST['make'] ?? '');
    $model        = trim($_POST['model'] ?? '');
    $variant      = trim($_POST['variant'] ?? '');
    $year         = (int)($_POST['year'] ?? 0);
    $colour       = trim($_POST['colour'] ?? '');
    $mileage      = (int)($_POST['mileage'] ?? 0);
    $purchase_use = trim($_POST['purchase_use'] ?? '');

    $stmt = $conn->prepare("
        UPDATE vehicles SET
            stock_code = ?,
            make = ?,
            model = ?,
            variant = ?,
            year = ?,
            colour = ?,
            mileage = ?,
            purchase_use = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssisssi",
        $stock_code,
        $make,
        $model,
        $variant,
        $year,
        $colour,
        $mileage,
        $purchase_use,
        $vehicle_id
    );

    $stmt->execute();
    $stmt->close();

    header("Location: vehicles_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Edit Vehicle</title>
<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, sans-serif;
    margin:0;
}

.card {
    width:420px;
    margin:60px auto;
    padding:20px;
    background:#111;
    border:2px solid red;
    border-radius:10px;
}

h2 {
    text-align:center;
    color:red;
    margin-bottom:20px;
}

label {
    display:block;
    margin-top:10px;
    font-size:13px;
}

input, select {
    width:100%;
    padding:8px;
    margin-top:4px;
    background:#222;
    color:#fff;
    border:1px solid #444;
    border-radius:4px;
}

button {
    width:100%;
    margin-top:20px;
    padding:10px;
    background:red;
    border:none;
    color:#fff;
    font-weight:bold;
    border-radius:5px;
    cursor:pointer;
}

button:hover {
    background:#cc0000;
}

.back {
    display:block;
    margin-top:15px;
    text-align:center;
    color:#aaa;
    text-decoration:none;
}
.back:hover { color:#fff; }
</style>
</head>

<body>

<div class="card">
    <h2>Edit Vehicle</h2>

    <form method="post">

        <label>Stock Code</label>
        <input type="text" name="stock_code" value="<?= htmlspecialchars($vehicle['stock_code']) ?>" required>

        <label>Make</label>
        <input type="text" name="make" value="<?= htmlspecialchars($vehicle['make']) ?>" required>

        <label>Model</label>
        <input type="text" name="model" value="<?= htmlspecialchars($vehicle['model']) ?>" required>

        <label>Variant</label>
        <input type="text" name="variant" value="<?= htmlspecialchars($vehicle['variant']) ?>">

        <label>Year</label>
        <input type="number" name="year" value="<?= (int)$vehicle['year'] ?>">

        <label>Colour</label>
        <input type="text" name="colour" value="<?= htmlspecialchars($vehicle['colour']) ?>">

        <label>Mileage</label>
        <input type="number" name="mileage" value="<?= (int)$vehicle['mileage'] ?>">

        <label>Purchase Use</label>
        <select name="purchase_use">
            <option value="Selling"  <?= $vehicle['purchase_use'] === 'Selling' ? 'selected' : '' ?>>Selling</option>
            <option value="Stripping"<?= $vehicle['purchase_use'] === 'Stripping' ? 'selected' : '' ?>>Stripping</option>
            <option value="Other"    <?= $vehicle['purchase_use'] === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>

        <button type="submit">Save Changes</button>
    </form>

    <a href="vehicles_list.php" class="back">← Back to Vehicles</a>
</div>

</body>
</html>
