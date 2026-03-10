<?php
require_once __DIR__ . "/config/config.php";

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);
if ($vehicle_id <= 0) die("Invalid vehicle");

/* Load vehicle */
$stmt = $conn->prepare("SELECT stock_code, make, model FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$vehicle) die("Vehicle not found");

/* Load categories */
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Vehicle Stripping</title>

<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, Helvetica, sans-serif;
}

.card {
    width:420px;
    margin:40px auto;
    background:#111;
    border:2px solid #b00000;
    border-radius:8px;
    padding:20px;
}

.header {
    background:#b00000;
    color:#fff;
    padding:12px;
    text-align:center;
    font-weight:bold;
    border-radius:6px;
    margin-bottom:15px;
}

label {
    display:block;
    margin-top:12px;
    font-size:14px;
}

input, select {
    width:100%;
    padding:8px;
    margin-top:5px;
    background:#222;
    color:#fff;
    border:1px solid #444;
    border-radius:4px;
}

button {
    width:100%;
    padding:12px;
    margin-top:15px;
    font-weight:bold;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.btn-save {
    background:#b00000;
    color:#fff;
}

.btn-back {
    background:#333;
    color:#fff;
}
</style>
</head>

<body>

<div class="card">

<div class="header">
STRIPPING 🚗 <?= h($vehicle['make']) ?> <?= h($vehicle['model']) ?> (<?= h($vehicle['stock_code']) ?>)
</div>

<form method="post" action="vehicle_stripping_save.php" enctype="multipart/form-data">

<input type="hidden" name="vehicle_id" value="<?= $vehicle_id ?>">

<label>Part Name *</label>
<input type="text" name="part_name" required>

<label>Category *</label>
<select name="category_id" id="category" required>
    <option value="">Select</option>
    <?php while ($c = $categories->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
    <?php endwhile; ?>
</select>

<label>Subcategory *</label>
<select name="subcategory_id" id="subcategory" required>
    <option value="">Select</option>
</select>

<label>Type *</label>
<select name="type_id" id="type" required>
    <option value="">Select</option>
</select>

<label>Component</label>
<select name="component_id" id="component">
    <option value="">Optional</option>
</select>

<label>Condition *</label>
<select name="condition" required>
    <option value="NEW">NEW</option>
    <option value="GOOD">GOOD</option>
    <option value="FAIR">FAIR</option>
</select>

<label>Bin Location (optional)</label>
<input type="text" name="location" placeholder="e.g. RACK A3 / BIN 12">

<label>Photos (optional)</label>
<input type="file" name="photos[]" multiple accept="image/*">

<button type="submit" class="btn-save">SAVE PART</button>

</form>

<form method="get" action="vehicle_view.php">
    <input type="hidden" name="id" value="<?= $vehicle_id ?>">
    <button type="submit" class="btn-back">ALL PARTS ENTERED</button>
</form>

</div>

<script>
const category = document.getElementById('category');
const subcategory = document.getElementById('subcategory');
const typeSel = document.getElementById('type');
const component = document.getElementById('component');

function reset(sel, text) {
    sel.innerHTML = `<option value="">${text}</option>`;
}

category.addEventListener('change', () => {
    reset(subcategory, 'Select');
    reset(typeSel, 'Select');
    reset(component, 'Optional');

    if (!category.value) return;

    fetch(`ajax/epc_get_subcategories.php?category_id=${category.value}`)
        .then(r => r.json())
        .then(d => d.forEach(x => {
            subcategory.innerHTML += `<option value="${x.id}">${x.name}</option>`;
        }));
});

subcategory.addEventListener('change', () => {
    reset(typeSel, 'Select');
    reset(component, 'Optional');

    if (!subcategory.value) return;

    fetch(`ajax/epc_get_types.php?subcategory_id=${subcategory.value}`)
        .then(r => r.json())
        .then(d => d.forEach(x => {
            typeSel.innerHTML += `<option value="${x.id}">${x.name}</option>`;
        }));
});

typeSel.addEventListener('change', () => {
    reset(component, 'Optional');

    if (!typeSel.value) return;

    fetch(`ajax/epc_get_components.php?type_id=${typeSel.value}`)
        .then(r => r.json())
        .then(d => d.forEach(x => {
            component.innerHTML += `<option value="${x.id}">${x.name}</option>`;
        }));
});
</script>

</body>
</html>
