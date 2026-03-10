<?php
/*********************************************************
 * MOBILE – VEHICLE STRIPPING (DRIVER PAGE)
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- VEHICLE CONTEXT ---------------- */

$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);
if ($vehicle_id <= 0) {
    die("Vehicle not selected.");
}

/* ---------------- LOAD CATEGORIES ---------------- */

$categories = [];
$res = $conn->query("SELECT id, name FROM categories ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}

/* ---------------- HANDLE SAVE ---------------- */

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category_id    = (int)($_POST['category_id'] ?? 0);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $type_id        = (int)($_POST['type_id'] ?? 0);
    $component_id   = (int)($_POST['component_id'] ?? 0);

    $part_name = trim($_POST['part_name'] ?? '');
    $condition = trim($_POST['condition'] ?? '');
    $price     = (float)($_POST['price'] ?? 0);

    if (
        $category_id <= 0 ||
        $subcategory_id <= 0 ||
        $type_id <= 0 ||
        $component_id <= 0 ||
        $part_name === '' ||
        $condition === ''
    ) {
        $message = "Please complete all required fields.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO stripped_inventory
            (vehicle_id, category_id, subcategory_id, type_id, component_id,
             part_name, part_condition, selling_price, qty, sold_status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'AVAILABLE', NOW())
        ");

        $stmt->bind_param(
            "iiiiissd",
            $vehicle_id,
            $category_id,
            $subcategory_id,
            $type_id,
            $component_id,
            $part_name,
            $condition,
            $price
        );

        if ($stmt->execute()) {
            $message = "✅ Part saved successfully";
        } else {
            $message = "❌ Error saving part";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Mobile Vehicle Stripping</title>

<style>
    body { font-family: Arial, sans-serif; background:#f9f9f9; margin:10px; }
    h2 { text-align:center; }
    label { font-weight:bold; display:block; margin-top:10px; }
    input, select, button {
        width:100%;
        padding:12px;
        font-size:16px;
        margin-top:5px;
    }
    button {
        background:#007bff;
        color:#fff;
        border:none;
        margin-top:15px;
        font-size:18px;
    }
    .msg { text-align:center; font-weight:bold; margin:10px 0; }
</style>
</head>

<body>

<h2>Strip Vehicle</h2>
<p style="text-align:center;">Vehicle ID: <?= $vehicle_id ?></p>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post">

    <label>Part Name *</label>
    <input type="text" name="part_name" required>

    <label>Category *</label>
    <select name="category_id" id="category" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Subcategory *</label>
    <select name="subcategory_id" id="subcategory" required></select>

    <label>Type *</label>
    <select name="type_id" id="type" required></select>

    <label>Component *</label>
    <select name="component_id" id="component" required></select>

    <label>Condition *</label>
    <select name="condition" id="condition" required>
        <option value="">Select Condition</option>
        <option>New</option>
        <option>Good</option>
        <option>Fair</option>
    </select>

    <label>Price</label>
    <input type="number" step="0.01" name="price" id="price">

    <button type="submit">Save Part</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const category = document.getElementById('category');
    const sub = document.getElementById('subcategory');
    const type = document.getElementById('type');
    const comp = document.getElementById('component');
    const cond = document.getElementById('condition');
    const price = document.getElementById('price');

    let edited = false;
    price.addEventListener('input', () => edited = true);

    category.addEventListener('change', () => {
        fetch('/autowagen_master_clean/ajax/get_subcategories.php?category_id='+category.value)
        .then(r=>r.json()).then(d=>{
            sub.innerHTML='<option value="">Select Subcategory</option>';
            type.innerHTML=''; comp.innerHTML='';
            d.forEach(x=>sub.innerHTML+=`<option value="${x.id}">${x.name}</option>`);
        });
    });

    sub.addEventListener('change', () => {
        fetch('/autowagen_master_clean/ajax/get_types.php?subcategory_id='+sub.value)
        .then(r=>r.json()).then(d=>{
            type.innerHTML='<option value="">Select Type</option>';
            comp.innerHTML='';
            d.forEach(x=>type.innerHTML+=`<option value="${x.id}">${x.name}</option>`);
        });
    });

    type.addEventListener('change', () => {
        fetch('/autowagen_master_clean/ajax/get_components.php?type_id='+type.value)
        .then(r=>r.json()).then(d=>{
            comp.innerHTML='<option value="">Select Component</option>';
            d.forEach(x=>comp.innerHTML+=`<option value="${x.id}">${x.name}</option>`);
        });
    });

    function suggest() {
        if (!category.value || !cond.value) return;
        if (edited && price.value !== '') return;

        fetch(`/autowagen_master_clean/ajax/get_pricing_suggestion.php?category_id=${category.value}&condition=${cond.value}`)
        .then(r=>r.json()).then(d=>{
            if (d.price !== null && price.value==='') price.value=d.price;
        });
    }

    category.addEventListener('change', suggest);
    cond.addEventListener('change', suggest);

});
</script>

</body>
</html>
