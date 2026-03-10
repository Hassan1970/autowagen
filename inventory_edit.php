<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// --------------------------------------------------
// GET ID
// --------------------------------------------------
$inv_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($inv_id <= 0) {
    die("<h2 style='color:red;text-align:center;'>Invalid Inventory ID</h2>");
}

// --------------------------------------------------
// LOAD INVENTORY RECORD
// --------------------------------------------------
$stmt = $conn->prepare("
    SELECT si.*, v.stock_code, v.make, v.model, v.year
    FROM stripped_inventory si
    LEFT JOIN vehicles v ON si.vehicle_id = v.id
    WHERE si.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $inv_id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$inv) {
    die("<h2 style='color:red;text-align:center;'>Inventory item not found.</h2>");
}

// --------------------------------------------------
// SAVE CHANGES (POST)
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $part_name     = $_POST['part_name'] ?? '';
    $qty           = (int)($_POST['qty'] ?? 1);
    $condition     = $_POST['part_condition'] ?? 'GOOD';
    $position      = $_POST['position_code'] ?? '';
    $location      = $_POST['location'] ?? '';
    $notes         = $_POST['notes'] ?? '';

    $sql = "
        UPDATE stripped_inventory
        SET
            part_name = ?,
            qty = ?,
            part_condition = ?,
            position_code = ?,
            location = ?,
            notes = ?,
            updated_at = NOW()
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sissssi",
        $part_name,
        $qty,
        $condition,
        $position,
        $location,
        $notes,
        $inv_id
    );
    $stmt->execute();
    $stmt->close();

    header("Location: stripped_inventory_list.php?updated=1");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Inventory Item</title>
    <style>
        body { background:#000; color:#fff; font-family:Arial; margin:0; padding:0; }
        .wrap { width:90%; margin:20px auto; }

        .card {
            background:#111;
            padding:20px;
            border:2px solid #b00000;
            border-radius:10px;
        }

        h2 { color:#ff3333; }

        label { font-size:13px; display:block; margin-top:10px; }

        input[type=text], input[type=number], textarea, select {
            width:100%;
            padding:8px;
            background:#000;
            border:1px solid #444;
            color:#fff;
            border-radius:6px;
            margin-top:4px;
        }

        .btn-main {
            padding:8px 16px;
            background:#b00000;
            color:#fff;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
            margin-top:20px;
        }
        .btn-main:hover { background:#ff1a1a; }

        .btn-back {
            color:#ff3333;
            text-decoration:none;
            font-size:13px;
        }
        .btn-back:hover { text-decoration:underline; }
    </style>
</head>
<body>

<div class="wrap">

    <a href="stripped_inventory_list.php" class="btn-back">&laquo; Back to Inventory</a>

    <div class="card">
        <h2>Edit Inventory Item — #<?= $inv_id ?></h2>

        <p>
            <strong>Vehicle:</strong>
            <?= h($inv['stock_code']) ?> —
            <?= h($inv['make'] . " " . $inv['model'] . " " . $inv['year']) ?>
        </p>

        <form method="post">

            <label>Part Name</label>
            <input type="text" name="part_name" value="<?= h($inv['part_name']) ?>" required>

            <label>Quantity</label>
            <input type="number" name="qty" value="<?= (int)$inv['qty'] ?>" min="1">

            <label>Condition</label>
            <select name="part_condition">
                <?php
                $conds = ["NEW","GOOD","AVERAGE","POOR","SCRAP","UNKNOWN"];
                foreach ($conds as $c):
                ?>
                    <option value="<?= $c ?>" <?= $inv['part_condition']==$c?'selected':'' ?>>
                        <?= $c ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Position (LH / RH / Front / Rear etc.)</label>
            <input type="text" name="position_code" value="<?= h($inv['position_code']) ?>">

            <label>Storage Location</label>
            <input type="text" name="location" value="<?= h($inv['location']) ?>">

            <label>Notes</label>
            <textarea name="notes" rows="4"><?= h($inv['notes']) ?></textarea>

            <button class="btn-main" type="submit">Save Changes</button>

        </form>

    </div>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
