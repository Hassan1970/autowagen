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

$folderWeb = "uploads/inventory/" . $inv_id;
$folderFs  = __DIR__ . "/uploads/inventory/" . $inv_id;

$files = [];
if (is_dir($folderFs)) {
    foreach (glob($folderFs . "/*.*") as $path) {
        if (is_file($path)) {
            $files[] = basename($path);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory Photos</title>
    <style>
        body { background:#000; color:#fff; font-family:Arial; margin:0; padding:0; }
        .wrap { width:90%; margin:20px auto; }

        .card {
            background:#111;
            border:2px solid #b00000;
            border-radius:10px;
            padding:15px 18px;
            margin-bottom:20px;
        }

        h2 { margin-top:0; color:#ff3333; }

        .back-link {
            color:#ff3333;
            text-decoration:none;
            font-size:13px;
        }
        .back-link:hover { text-decoration:underline; }

        .info {
            font-size:13px;
            color:#ccc;
            margin-bottom:10px;
        }

        .photo-grid {
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            margin-top:10px;
        }

        .photo-item {
            width:150px;
            border-radius:8px;
            overflow:hidden;
            border:1px solid #b00000;
            background:#000;
            position:relative;
        }

        .photo-item img {
            width:100%;
            height:120px;
            object-fit:cover;
            display:block;
        }

        .photo-actions {
            padding:5px;
            text-align:center;
            font-size:11px;
            background:#111;
        }

        .btn-main,
        .btn-secondary,
        .btn-danger {
            display:inline-block;
            padding:6px 10px;
            border-radius:6px;
            border:none;
            cursor:pointer;
            font-size:12px;
            font-weight:bold;
            text-decoration:none;
        }

        .btn-main {
            background:#b00000;
            color:#fff;
        }
        .btn-main:hover { background:#ff1a1a; }

        .btn-secondary {
            background:#222;
            color:#fff;
            border:1px solid #555;
        }
        .btn-secondary:hover { background:#333; }

        .btn-danger {
            background:#b00000;
            color:#fff;
        }

        input[type="file"] {
            background:#000;
            color:#fff;
            border:1px solid #444;
            border-radius:6px;
            padding:6px;
            font-size:12px;
        }
    </style>
</head>
<body>

<div class="wrap">

    <a href="stripped_inventory_list.php" class="back-link">&laquo; Back to Inventory</a>

    <div class="card">
        <h2>Inventory Photos — #<?= $inv_id ?></h2>
        <div class="info">
            <strong>Vehicle:</strong>
            <?= h($inv['stock_code']) ?> —
            <?= h(trim(($inv['make'] ?? '') . ' ' . ($inv['model'] ?? '') . ' ' . ($inv['year'] ?? ''))) ?><br>
            <strong>Part:</strong> <?= h($inv['part_name']) ?>
        </div>

        <form action="inventory_photo_upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="inv_id" value="<?= $inv_id ?>">
            <div style="margin-bottom:8px;">
                <input type="file" name="photos[]" multiple accept="image/*">
            </div>
            <button type="submit" class="btn-main">Upload Photos</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-top:0; color:#ff3333;">Existing Photos</h3>

        <?php if (!$files): ?>
            <p style="font-size:13px; color:#ccc;">No photos uploaded yet for this part.</p>
        <?php else: ?>
            <div class="photo-grid">
                <?php foreach ($files as $f): ?>
                    <div class="photo-item">
                        <a href="<?= h($folderWeb . "/" . $f) ?>" target="_blank">
                            <img src="<?= h($folderWeb . "/" . $f) ?>" alt="">
                        </a>
                        <div class="photo-actions">
                            <a href="inventory_photo_delete.php?inv_id=<?= $inv_id ?>&file=<?= urlencode($f) ?>"
                               class="btn-danger"
                               onclick="return confirm('Delete this photo?');">
                                Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
