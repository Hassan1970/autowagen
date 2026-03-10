<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* -----------------------------
   Vehicle ID
------------------------------ */
$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);
if ($vehicle_id <= 0) {
    die("<h2 style='color:red;text-align:center;'>Vehicle not found</h2>");
}

/* -----------------------------
   Vehicle
------------------------------ */
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    die("<h2 style='color:red;text-align:center;'>Vehicle not found</h2>");
}

/* -----------------------------
   Purchase (ONLY SAFE COLUMNS)
------------------------------ */
$stmt = $conn->prepare("
    SELECT notes
    FROM vehicle_purchases
    WHERE vehicle_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$purchase = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* -----------------------------
   Stripped Parts
------------------------------ */
$stmt = $conn->prepare("
    SELECT sp.*, 
           c.name AS category_name,
           sc.name AS subcategory_name,
           t.name AS type_name,
           comp.name AS component_name
    FROM vehicle_stripped_parts sp
    LEFT JOIN categories c ON sp.category_id = c.id
    LEFT JOIN subcategories sc ON sp.subcategory_id = sc.id
    LEFT JOIN types t ON sp.type_id = t.id
    LEFT JOIN components comp ON sp.component_id = comp.id
    WHERE sp.vehicle_id = ?
    ORDER BY sp.id DESC
");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$parts = $stmt->get_result();
$stmt->close();

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Vehicle Profile</title>
<style>
body { background:#000; color:#fff; font-family:Arial; margin:0; }
.wrap { width:92%; margin:20px auto; }
.card {
    background:#111;
    border:2px solid #b00000;
    border-radius:10px;
    padding:15px;
    margin-bottom:20px;
}
h2 { color:#ff3333; margin-top:0; }
table { width:100%; border-collapse:collapse; }
th, td {
    border:1px solid #b00000;
    padding:6px 8px;
    font-size:13px;
}
th { background:#1b1b1b; color:#ff3333; text-align:left; }
.btn {
    padding:5px 10px;
    text-decoration:none;
    border-radius:5px;
    font-size:12px;
    font-weight:bold;
}
.btn-main { background:#b00000; color:#fff; }
.btn-sec { background:#222; color:#fff; border:1px solid #555; }
</style>
</head>

<body>
<div class="wrap">

<a href="vehicles_list.php" style="color:#ff3333;">&laquo; Back to Vehicles</a>

<div class="card">
    <h2>Vehicle Profile — <?= h($vehicle['stock_code']) ?></h2>
    <a class="btn btn-sec" href="vehicle_edit.php?id=<?= $vehicle_id ?>">Edit</a>
    <a class="btn btn-main" href="vehicle_stripping_entry.php?vehicle_id=<?= $vehicle_id ?>">Strip Vehicle</a>
</div>

<div class="card">
<h2>Vehicle Details</h2>
<table>
<tr><th>Make</th><td><?= h($vehicle['make']) ?></td></tr>
<tr><th>Model</th><td><?= h($vehicle['model']) ?></td></tr>
<tr><th>Variant</th><td><?= h($vehicle['variant']) ?></td></tr>
<tr><th>Year</th><td><?= h($vehicle['year']) ?></td></tr>
<tr><th>Colour</th><td><?= h($vehicle['colour']) ?></td></tr>
<tr><th>Mileage</th><td><?= h($vehicle['mileage']) ?></td></tr>
<tr><th>Fuel</th><td><?= h($vehicle['fuel_type']) ?></td></tr>
</table>
</div>

<div class="card">
<h2>Purchase Notes</h2>
<table>
<tr><th>Notes</th><td><?= nl2br(h($purchase['notes'] ?? '')) ?></td></tr>
</table>
</div>

<div class="card">
<h2>Stripped Parts</h2>

<?php if ($parts->num_rows === 0): ?>
    <p style="color:#ccc;">No parts stripped yet.</p>
<?php else: ?>
<table>
<tr>
<th>#</th>
<th>Category</th>
<th>Subcategory</th>
<th>Type</th>
<th>Component</th>
<th>Qty</th>
</tr>
<?php while ($p = $parts->fetch_assoc()): ?>
<tr>
<td><?= (int)$p['id'] ?></td>
<td><?= h($p['category_name']) ?></td>
<td><?= h($p['subcategory_name']) ?></td>
<td><?= h($p['type_name']) ?></td>
<td><?= h($p['component_name'] ?? '—') ?></td>
<td><?= (int)$p['qty'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>

</div>

</div>
</body>
</html>
