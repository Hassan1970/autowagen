<?php
require_once __DIR__ . '/config/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Invalid vehicle ID");

$res = $conn->query("SELECT * FROM vehicles WHERE id = $id LIMIT 1");
$vehicle = $res ? $res->fetch_assoc() : null;
if (!$vehicle) die("Vehicle not found");

$page_title = "Edit Vehicle – " . $vehicle['stock_code'];
include __DIR__ . '/includes/header.php';
?>

<style>
.form-wrap { width:900px; max-width:95%; margin:30px auto; background:#111; border:2px solid #b00000; padding:25px; border-radius:10px; }
label { display:block; margin-top:15px; color:#ff4444; }
input, select { width:100%; padding:8px; margin-top:5px; background:#000; color:#fff; border:1px solid #444; }
.btn { margin-top:20px; background:#b00000; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; }
</style>

<div class="form-wrap">
<h2>Edit Vehicle – <?= h($vehicle['stock_code']) ?></h2>

<form method="post" action="vehicle_edit_save.php">
<input type="hidden" name="id" value="<?= $vehicle['id'] ?>">

<label>Stock Code</label>
<input name="stock_code" value="<?= h($vehicle['stock_code']) ?>">

<label>Make</label>
<input name="make" value="<?= h($vehicle['make']) ?>">

<label>Model</label>
<input name="model" value="<?= h($vehicle['model']) ?>">

<label>Variant</label>
<input name="variant" value="<?= h($vehicle['variant']) ?>">

<label>Year</label>
<input name="year" value="<?= h($vehicle['year']) ?>">

<label>Colour</label>
<input name="colour" value="<?= h($vehicle['colour']) ?>">

<label>Mileage</label>
<input name="mileage" value="<?= h($vehicle['mileage']) ?>">

<label>VIN</label>
<input name="vin_number" value="<?= h($vehicle['vin_number']) ?>">

<label>Engine</label>
<input name="engine" value="<?= h($vehicle['engine']) ?>">

<label>Use</label>
<select name="purchase_use">
    <option <?= $vehicle['purchase_use']=="Selling"?"selected":"" ?>>Selling</option>
    <option <?= $vehicle['purchase_use']=="Stripping"?"selected":"" ?>>Stripping</option>
    <option <?= $vehicle['purchase_use']=="Other"?"selected":"" ?>>Other</option>
</select>

<button class="btn">Save Changes</button>
</form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
