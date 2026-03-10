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
// GET INVENTORY ID
// --------------------------------------------------
$inv_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($inv_id <= 0) {
    die("<h2 style='color:red; text-align:center; margin-top:40px;'>Invalid inventory ID.</h2>");
}

// --------------------------------------------------
// LOAD INVENTORY + VEHICLE
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
    die("<h2 style='color:red; text-align:center; margin-top:40px;'>Inventory item not found.</h2>");
}

// Already sold?
if (strtoupper($inv['sold_status'] ?? 'AVAILABLE') === 'SOLD') {
    die("<h2 style='color:orange; text-align:center; margin-top:40px;'>This part is already marked as SOLD.</h2>");
}

// --------------------------------------------------
// HANDLE POST (SAVE SOLD INFO)
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sold_date  = $_POST['sold_date'] ?: date('Y-m-d');
    $sold_notes = $_POST['sold_notes'] ?? '';

    // 1) Update stripped_inventory flag
    $up = $conn->prepare("
        UPDATE stripped_inventory
        SET sold_status = 'SOLD',
            sold_date   = ?,
            sold_notes  = ?
        WHERE id = ?
        LIMIT 1
    ");
    $up->bind_param("ssi", $sold_date, $sold_notes, $inv_id);
    $up->execute();
    $up->close();

    // 2) Insert record into sold_inventory log
    $ins = $conn->prepare("
        INSERT INTO sold_inventory
        (
            inventory_id,
            stripped_part_id,
            vehicle_id,
            stock_code,
            part_name,
            qty,
            part_condition,
            position_code,
            location,
            sold_date,
            sold_notes
        )
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ");

    $inventory_id    = $inv['id'];
    $stripped_part_id = $inv['stripped_part_id'];
    $vehicle_id      = $inv['vehicle_id'];
    $stock_code      = $inv['stock_code'];
    $part_name       = $inv['part_name'];
    $qty             = (int)$inv['qty'];
    $part_condition  = $inv['part_condition'];
    $position_code   = $inv['position_code'];
    $location        = $inv['location'];

    $ins->bind_param(
        "iiississsss",
        $inventory_id,
        $stripped_part_id,
        $vehicle_id,
        $stock_code,
        $part_name,
        $qty,
        $part_condition,
        $position_code,
        $location,
        $sold_date,
        $sold_notes
    );
    $ins->execute();
    $ins->close();

    header("Location: stripped_inventory_list.php?sold=1");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Part as SOLD</title>
    <style>
        body { background:#000; color:#fff; font-family:Arial; margin:0; padding:0; }
        .wrap { width:90%; margin:20px auto; }
        .card {
            background:#111;
            border:2px solid #b00000;
            border-radius:10px;
            padding:15px 18px;
        }
        h2 { color:#ff3333; margin-top:0; }
        .back-link { color:#ff3333; text-decoration:none; font-size:13px; }
        .back-link:hover { text-decoration:underline; }
        label { display:block; font-size:13px; margin-top:10px; }
        input[type=text], input[type=date], textarea {
            width:100%;
            background:#000;
            color:#fff;
            border:1px solid #444;
            border-radius:6px;
            padding:7px;
            margin-top:3px;
        }
        .btn-main {
            margin-top:15px;
            background:#b00000;
            color:#fff;
            border:none;
            padding:8px 14px;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
        }
        .btn-main:hover { background:#ff1a1a; }
        .summary { font-size:13px; color:#ccc; margin-bottom:10px; }
    </style>
</head>
<body>

<div class="wrap">

    <a href="stripped_inventory_list.php" class="back-link">&laquo; Back to Inventory</a>

    <div class="card">
        <h2>Mark Part as SOLD — #<?= $inv_id ?></h2>

        <div class="summary">
            <strong>Vehicle:</strong>
            <?= h($inv['stock_code']) ?> —
            <?= h(trim(($inv['make'] ?? '') . ' ' . ($inv['model'] ?? '') . ' ' . ($inv['year'] ?? ''))) ?><br>
            <strong>Part:</strong> <?= h($inv['part_name']) ?><br>
            <strong>Qty:</strong> <?= (int)$inv['qty'] ?><br>
            <strong>Condition:</strong> <?= h($inv['part_condition']) ?><br>
            <strong>Location:</strong> <?= h($inv['location']) ?>
        </div>

        <form method="post">
            <label>Sold Date</label>
            <input type="date" name="sold_date" value="<?= date('Y-m-d') ?>">

            <label>Sold Notes (customer, invoice, etc.)</label>
            <textarea name="sold_notes" rows="4"></textarea>

            <button type="submit" class="btn-main"
                    onclick="return confirm('Mark this part as SOLD?');">
                Confirm SOLD
            </button>
        </form>
    </div>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
