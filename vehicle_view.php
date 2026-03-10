<?php
require_once __DIR__ . "/config/config.php";

function h($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$vehicle_id = (int)($_GET['id'] ?? 0);
if ($vehicle_id <= 0) {
    die("Vehicle not selected.");
}

/* ================= VEHICLE ================= */
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    die("Vehicle not found.");
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

/* ================= STRIPPED PARTS ================= */
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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vehicle Profile — <?= h($vehicle['stock_code']) ?></title>

<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, sans-serif;
}
h1 {
    color:#ff3333;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.card {
    border:2px solid #b00000;
    background:#111;
    margin:20px;
    padding:15px;
    border-radius:6px;
}
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
    border:1px solid #b00000;
    padding:10px;
}
th { color:#ff3333; }

.btn {
    display:inline-block;
    background:#b00000;
    color:#fff;
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
    font-weight:bold;
}
.btn:hover { background:#ff3333; }

img.thumb {
    max-width:80px;
    border-radius:4px;
    cursor:pointer;
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

<div class="card">
<h2>Vehicle</h2>
<table>
<tr><th>Make</th><td><?= h($vehicle['make']) ?></td></tr>
<tr><th>Model</th><td><?= h($vehicle['model']) ?></td></tr>
<tr><th>VIN</th><td><?= h($vehicle['vin_number']) ?></td></tr>
</table>
</div>

<div class="card">
<h2>Customer</h2>
<table>
<tr><th>Name</th><td><?= h($customer['name'] ?? '') ?></td></tr>
<tr><th>Address</th><td><?= h($customer['address'] ?? '') ?></td></tr>
</table>
</div>

<div class="card">
<h2>Stripped Parts</h2>

<table>
<thead>
<tr>
<th>Part</th>
<th>Qty</th>
<th>Condition</th>
<th>Location</th>
<th>Photos</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php while ($part = $parts->fetch_assoc()): ?>

<?php
$stmtImg = $conn->prepare("
    SELECT file_name
    FROM stripped_part_images
    WHERE part_id = ?
    LIMIT 1
");
$stmtImg->bind_param("i", $part['id']);
$stmtImg->execute();
$image = $stmtImg->get_result()->fetch_assoc();
$stmtImg->close();

$hasPhoto = (bool)$image;

$condition =
    $part['part_condition']
    ?? $part['item_condition']
    ?? $part['condition']
    ?? '';
?>

<tr>
<td><?= h($part['part_name']) ?></td>
<td><?= h($part['qty']) ?></td>
<td><?= h($condition) ?></td>
<td><?= h($part['location'] ?? '') ?></td>

<td>
<?php if ($hasPhoto): ?>
    <img class="thumb"
         src="/uploads/stripped_parts/<?= $part['id'] ?>/<?= h($image['file_name']) ?>">
<?php else: ?>
    <em>No photo</em>
<?php endif; ?>
<br>
<a class="btn"
   href="vehicle_stripped_photo_replace.php?part_id=<?= $part['id'] ?>&vehicle_id=<?= $vehicle_id ?>">
   <?= $hasPhoto ? 'Replace photo' : 'Add photo' ?>
</a>
</td>

<td>
<a class="btn"
   href="stripped_part_delete.php?id=<?= $part['id'] ?>&vehicle_id=<?= $vehicle_id ?>">
   DELETE
</a>
</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>

</body>
</html>
