<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$part_id    = isset($_GET['part_id']) ? (int)$_GET['part_id'] : 0;
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

if ($part_id <= 0) {
    echo "<h2 style='color:red;text-align:center;margin-top:40px;'>No part selected.</h2>";
    include __DIR__ . "/includes/footer.php";
    exit;
}

$stmt = $conn->prepare("
    SELECT sp.*, v.stock_code, v.make, v.model
    FROM vehicle_stripped_parts sp
    LEFT JOIN vehicles v ON sp.vehicle_id = v.id
    WHERE sp.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $part_id);
$stmt->execute();
$part = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$part) {
    echo "<h2 style='color:red;text-align:center;margin-top:40px;'>Stripped part not found.</h2>";
    include __DIR__ . "/includes/footer.php";
    exit;
}

if ($vehicle_id <= 0 && !empty($part['vehicle_id'])) {
    $vehicle_id = (int)$part['vehicle_id'];
}

if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Barcode data string (what scanners will read)
$barcodeData = "SP-" . $part_id;  // you can change to $part['stock_code'] . "-" . $part_id if you like
?>
<!DOCTYPE html>
<html>
<head>
    <title>Barcode Label - Part <?= (int)$part_id ?></title>
    <style>
        body {
            background:#000;
            color:#fff;
            font-family:Arial, sans-serif;
            margin:0;
            padding:0;
        }
        .wrap {
            width:80%;
            margin:25px auto 40px;
        }
        .card {
            background:#111;
            border:2px solid #b00000;
            border-radius:10px;
            padding:15px 20px;
            margin-bottom:20px;
        }
        .back-link {
            color:#ff3333;
            text-decoration:none;
        }
        .back-link:hover { text-decoration:underline; }

        .label {
            background:#fff;
            color:#000;
            padding:15px;
            border-radius:6px;
            margin-top:10px;
        }
        .label h3 {
            margin:0 0 6px 0;
            font-size:16px;
        }
        .label p {
            margin:2px 0;
            font-size:13px;
        }
        .btn-small {
            display:inline-block;
            padding:6px 10px;
            font-size:12px;
            border-radius:6px;
            text-decoration:none;
            border:none;
            cursor:pointer;
            background:#b00000;
            color:#fff;
            margin-top:10px;
        }

        @media print {
            body { background:#fff; color:#000; }
            .wrap { width:100%; margin:0; }
            .card { border:none; background:#fff; }
            .back-link, .btn-small { display:none; }
        }
    </style>

    <!-- JsBarcode from CDN for Code128 -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

<div class="wrap">

    <a href="vehicle_profile.php?vehicle_id=<?= $vehicle_id ?>" class="back-link">&laquo; Back to Vehicle Profile</a>

    <div class="card">
        <h2 style="color:#ff3333;margin:0 0 10px 0;">Barcode Label – Part <?= (int)$part_id ?></h2>

        <div class="label" id="print-area">
            <h3><?= h($part['stock_code'] ?? '') ?> – <?= h($part['part_name']) ?></h3>
            <p><strong>Part ID:</strong> <?= (int)$part['id'] ?></p>
            <p><strong>Vehicle:</strong> <?= h(($part['make'] ?? '') . " " . ($part['model'] ?? '')) ?></p>
            <p><strong>Position:</strong> <?= h($part['position_code'] ?? '') ?></p>
            <p><strong>Location:</strong> <?= h($part['location'] ?? '') ?></p>

            <svg id="barcode"></svg>

            <p style="margin-top:4px;font-size:11px;">
                Code: <?= h($barcodeData) ?>
            </p>
        </div>

        <button class="btn-small" onclick="window.print();">Print Label</button>
    </div>

</div>

<script>
// Render Code128 barcode in the SVG
document.addEventListener("DOMContentLoaded", function() {
    JsBarcode("#barcode", "<?= h($barcodeData) ?>", {
        format: "code128",
        lineColor: "#000000",
        width: 2,
        height: 50,
        displayValue: false
    });
});
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
