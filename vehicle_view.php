<?php
require_once __DIR__ . "/config/config.php";

function h($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$vehicle_id = (int)($_GET['id'] ?? 0);
if ($vehicle_id <= 0) die("Vehicle not selected.");

/* ================= VEHICLE ================= */
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehicle) die("Vehicle not found.");

/* ================= VIN FORMAT ================= */
$vin = $vehicle['vin_number'] ?? '';
$vinFormatted = '';
if (!empty($vin)) {
    $vinFormatted =
        substr($vin, 0, 5) . ' ' .
        substr($vin, 5, 5) . ' ' .
        substr($vin, 10);
}

/* ================= VEHICLE IMAGES ================= */
$vehicle_images = [];

$stmt = $conn->prepare("SHOW TABLES LIKE 'vehicle_images'");
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare("SELECT file_name FROM vehicle_images WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $vehicle_images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

/* ================= CUSTOMER ================= */
$stmt = $conn->prepare("
    SELECT c.*
    FROM vehicle_purchases vp
    JOIN customers c ON c.id = vp.customer_id
    WHERE vp.vehicle_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ================= PARTS ================= */
$stmt = $conn->prepare("
    SELECT *
    FROM vehicle_stripped_parts
    WHERE vehicle_id = ?
");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$parts = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Vehicle Profile — <?= h($vehicle['stock_code']) ?></title>

<style>
body { background:#000; color:#fff; font-family:Arial; }

h1 {
    color:#ff3333;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.card {
    border:2px solid #b00000;
    background:#111;
    margin:20px;
    padding:15px;
    border-radius:6px;
}

.vehicle-flex {
    display:flex;
    gap:20px;
    align-items:flex-start;
}

/* IMAGE */
.vehicle-img img {
    width:250px;
    border-radius:6px;
    cursor:pointer;
}

.gallery img {
    width:80px;
    margin:5px;
    cursor:pointer;
    border-radius:4px;
}

/* INFO BOXES */
.info-box {
    border:2px solid #b00000;
    padding:10px;
    margin-bottom:8px;
    background:#000;
    display:flex;
    justify-content:space-between;
    font-size:16px;
    letter-spacing:1.5px;
    font-weight:bold;
}

.label { color:#ff3333; }
.value { color:#fff; }

/* VIN */
.vin-box {
    border:2px solid #b00000;
    padding:10px;
    font-size:16px;
    letter-spacing:2px;
    font-weight:bold;
    background:#000;
    margin-top:10px;
}

/* TABLE */
table {
    width:100%;
    border-collapse:collapse;
}

th, td {
    border:1px solid #b00000;
    padding:10px;
}

th { color:#ff3333; }

/* BUTTON */
.btn {
    background:#b00000;
    color:#fff;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    display:inline-block;
    margin:2px;
}

.btn:hover { background:#ff3333; }

/* POPUP */
#popup {
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.9);
    justify-content:center;
    align-items:center;
}

#popup img {
    max-width:90%;
    max-height:90%;
}
</style>
</head>

<body>

<h1>
    Vehicle Profile — <?= h($vehicle['stock_code']) ?>
    <a class="btn" href="vehicle_stripping_entry.php?vehicle_id=<?= $vehicle_id ?>">
        STRIP PART
    </a>
</h1>

<!-- VEHICLE -->
<div class="card">
<h2>Vehicle</h2>

<div class="vehicle-flex">

<!-- LEFT IMAGE -->
<div class="vehicle-img">

<?php if (!empty($vehicle_images)): ?>
    <img src="uploads/vehicles/<?= h($vehicle_images[0]['file_name']) ?>" onclick="zoom(this)">
<?php elseif (!empty($vehicle['photo_main'])): ?>
    <img src="uploads/vehicles/<?= h($vehicle['photo_main']) ?>" onclick="zoom(this)">
<?php else: ?>
    <div>No Image</div>
<?php endif; ?>

<div class="gallery">
<?php foreach ($vehicle_images as $img): ?>
    <img src="uploads/vehicles/<?= h($img['file_name']) ?>" onclick="zoom(this)">
<?php endforeach; ?>
</div>

<form method="post" enctype="multipart/form-data" action="vehicle_images_upload.php?vehicle_id=<?= $vehicle_id ?>">
<input type="file" name="images[]" multiple required>
<button class="btn">Upload</button>
</form>

</div>

<!-- RIGHT INFO -->
<div>

<div class="info-box">
<span class="label">MAKE</span>
<span class="value"><?= h($vehicle['make']) ?></span>
</div>

<div class="info-box">
<span class="label">MODEL</span>
<span class="value"><?= h($vehicle['model']) ?></span>
</div>

<div class="info-box">
<span class="label">YEAR</span>
<span class="value"><?= h($vehicle['year'] ?? '') ?></span>
</div>

<div class="info-box">
<span class="label">FUEL</span>
<span class="value"><?= h($vehicle['fuel_type'] ?? '') ?></span>
</div>

<div class="info-box">
<span class="label">TRANSMISSION</span>
<span class="value"><?= h($vehicle['transmission'] ?? '') ?></span>
</div>

<div class="info-box">
<span class="label">MILEAGE</span>
<span class="value"><?= h($vehicle['mileage'] ?? '') ?></span>
</div>

<p><b>VIN / CHASSIS</b></p>
<div class="vin-box">
<?= h($vinFormatted) ?>
</div>

</div>

</div>
</div>

<!-- CUSTOMER -->
<div class="card">
<h2>Customer</h2>
<p><b>Name:</b> <?= h($customer['name'] ?? '') ?></p>
<p><b>Address:</b> <?= h($customer['address'] ?? '') ?></p>
</div>

<!-- PARTS -->
<div class="card">
<h2>Stripped Parts</h2>

<table>
<tr>
<th>Part</th>
<th>Qty</th>
<th>Condition</th>
<th>Location</th>
<th>Photos</th>
<th>Action</th>
</tr>

<?php while ($part = $parts->fetch_assoc()): ?>

<?php
$stmtImg = $conn->prepare("SELECT file_name FROM stripped_part_images WHERE part_id = ?");
$stmtImg->bind_param("i", $part['id']);
$stmtImg->execute();
$images = $stmtImg->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtImg->close();
?>

<tr>
<td><?= h($part['part_name']) ?></td>
<td><?= h($part['qty']) ?></td>
<td><?= h($part['part_condition']) ?></td>
<td><?= h($part['location']) ?></td>

<td>
<?php if ($images): ?>
    <?php foreach ($images as $img): ?>
        <img src="uploads/stripped_parts/<?= $part['id'] ?>/<?= h($img['file_name']) ?>"
             width="60" onclick="zoom(this)">
    <?php endforeach; ?>
<?php else: ?>
    No photo
<?php endif; ?>

<br>

<a class="btn"
href="stripped_part_add_images.php?part_id=<?= $part['id'] ?>&vehicle_id=<?= $vehicle_id ?>">
Add Images
</a>
</td>

<td>
<a class="btn"
href="stripped_part_edit.php?id=<?= $part['id'] ?>">
EDIT
</a>

<a class="btn"
href="stripped_part_delete.php?id=<?= $part['id'] ?>&vehicle_id=<?= $vehicle_id ?>">
DELETE
</a>
</td>
</tr>

<?php endwhile; ?>

</table>
</div>

<!-- POPUP -->
<div id="popup" onclick="this.style.display='none'">
    <img id="popup-img">
</div>

<script>
function zoom(img){
    document.getElementById('popup').style.display='flex';
    document.getElementById('popup-img').src = img.src;
}
</script>

</body>
</html>