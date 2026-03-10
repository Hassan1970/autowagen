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

// Load stripped part info
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

// If vehicle_id was not passed, use from part
if ($vehicle_id <= 0 && !empty($part['vehicle_id'])) {
    $vehicle_id = (int)$part['vehicle_id'];
}

// Helper
if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Build photo paths
$baseDir   = __DIR__ . "/uploads/stripped_parts/" . $part_id;
$mainDir   = $baseDir . "/main";
$thumbDir  = $baseDir . "/thumbs";
$mainUrl   = "uploads/stripped_parts/" . $part_id . "/main/";
$thumbUrl  = "uploads/stripped_parts/" . $part_id . "/thumbs/";

// Read images if folder exists
$photos = [];
if (is_dir($mainDir)) {
    $files = scandir($mainDir);
    foreach ($files as $f) {
        if ($f === "." || $f === "..") continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) continue;
        $photos[] = $f;
    }
    sort($photos);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stripped Part Photos</title>
    <style>
        body {
            background:#000;
            color:#fff;
            font-family:Arial,sans-serif;
            margin:0;
            padding:0;
        }
        .wrap {
            width:90%;
            margin:25px auto 50px;
        }
        .card {
            background:#111;
            border:2px solid #b00000;
            border-radius:10px;
            padding:15px 20px;
            margin-bottom:20px;
        }
        h2.card-title {
            text-align:center;
            color:#ff3333;
            margin:0 0 10px 0;
        }
        .back-link {
            color:#ff3333;
            text-decoration:none;
            display:inline-block;
            margin-bottom:10px;
        }
        .back-link:hover { text-decoration:underline; }

        .photo-grid {
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            margin-top:10px;
        }
        .photo-thumb {
            width:150px;
            height:110px;
            border:1px solid #b00000;
            border-radius:6px;
            overflow:hidden;
            position:relative;
            background:#000;
        }
        .photo-thumb img {
            width:100%;
            height:100%;
            object-fit:cover;
        }
        .photo-thumb .delete-btn {
            position:absolute;
            top:2px;
            right:2px;
        }
        .btn-small {
            display:inline-block;
            padding:4px 8px;
            font-size:11px;
            border-radius:5px;
            text-decoration:none;
            border:none;
            cursor:pointer;
        }
        .btn-main {
            background:#b00000;
            color:#fff;
        }
        .btn-danger {
            background:#b00000;
            color:#fff;
        }
        .btn-secondary {
            background:#222;
            color:#fff;
            border:1px solid #555;
        }
        .label-small {
            font-size:12px;
            color:#ccc;
        }
        input[type="file"] {
            font-size:12px;
            color:#fff;
        }
        form.inline-form {
            display:flex;
            align-items:center;
            gap:6px;
            flex-wrap:wrap;
        }
        table.info-table {
            width:100%;
            border-collapse:collapse;
            font-size:13px;
            margin-bottom:10px;
        }
        table.info-table th,
        table.info-table td {
            border:1px solid #b00000;
            padding:6px 8px;
        }
        table.info-table th {
            width:140px;
            background:#1b1b1b;
            color:#ff3333;
        }
    </style>
</head>
<body>

<div class="wrap">

    <a href="vehicle_profile.php?vehicle_id=<?= $vehicle_id ?>" class="back-link">&laquo; Back to Vehicle Profile</a>

    <div class="card">
        <h2 class="card-title">Stripped Part Photos</h2>

        <table class="info-table">
            <tr><th>Part ID</th><td><?= (int)$part['id'] ?></td></tr>
            <tr><th>Stock Code</th><td><?= h($part['stock_code'] ?? '') ?></td></tr>
            <tr><th>Vehicle</th><td><?= h(($part['make'] ?? '') . " " . ($part['model'] ?? '')) ?></td></tr>
            <tr><th>Part Name</th><td><?= h($part['part_name']) ?></td></tr>
            <tr><th>Position</th><td><?= h($part['position_code'] ?? '') ?></td></tr>
            <tr><th>Location</th><td><?= h($part['location'] ?? '') ?></td></tr>
        </table>

        <h3 style="margin-top:10px;">Existing Photos</h3>

        <?php if (!$photos): ?>
            <p class="label-small">No photos uploaded for this part yet.</p>
        <?php else: ?>
            <div class="photo-grid">
                <?php foreach ($photos as $file): ?>
                    <?php
                    $thumbPathFs = $thumbDir . "/" . $file;
                    $thumbPathUrl = $thumbUrl . $file;
                    $mainPathUrl  = $mainUrl . $file;

                    if (!is_file($thumbPathFs)) {
                        $thumbPathUrl = $mainPathUrl;
                    }
                    ?>
                    <div class="photo-thumb">
                        <a href="<?= h($mainPathUrl) ?>" target="_blank">
                            <img src="<?= h($thumbPathUrl) ?>" alt="">
                        </a>
                        <div class="delete-btn">
                            <a class="btn-small btn-danger"
                               href="stripped_part_photo_delete.php?part_id=<?= (int)$part_id ?>&vehicle_id=<?= $vehicle_id ?>&file=<?= urlencode($file) ?>"
                               onclick="return confirm('Delete this photo?');">
                                ✕
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr style="margin:15px 0; border-color:#333;">

        <h3>Upload New Photos</h3>
        <form action="stripped_part_photo_upload.php"
              method="post"
              enctype="multipart/form-data"
              class="inline-form">

            <input type="hidden" name="part_id" value="<?= (int)$part_id ?>">
            <input type="hidden" name="vehicle_id" value="<?= (int)$vehicle_id ?>">

            <input type="file" name="photos[]" accept="image/*" multiple>
            <button type="submit" class="btn-small btn-main">Upload</button>
        </form>
        <p class="label-small">
            You can select multiple images. They will be stored under
            <code>uploads/stripped_parts/<?= (int)$part_id ?>/main/</code>
        </p>

    </div>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
