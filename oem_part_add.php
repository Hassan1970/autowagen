<?php
require_once "config/config.php";
include "includes/header.php";

// Load categories
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Add OEM Part</title>
<style>
body { background:#000; color:#fff; font-family:Arial; }
.wrap {
    width:70%; margin:20px auto; padding:20px;
    background:#111; border:2px solid #a00; border-radius:10px;
}
label { display:block; margin-top:12px; font-weight:bold; color:#ff3333; }
select, input[type=text], input[type=number] {
    width:100%; padding:8px; border-radius:4px; margin-top:5px;
    background:#222; border:1px solid #444; color:white;
}
button {
    background:#b30000; padding:10px 20px; border:0; color:#fff;
    margin-top:20px; font-size:16px; cursor:pointer;
    border-radius:6px;
}
button:hover { background:#e00000; }
h2 { text-align:center; color:#ff3333; }
</style>
</head>

<body>

<div class="wrap">
    <h2>Add OEM Part</h2>

    <form method="POST" action="oem_parts_save.php">

        <!-- OEM number -->
        <label>OEM Number</label>
        <input type="text" name="oem_number" required>

        <!-- Part Name -->
        <label>Part Name</label>
        <input type="text" name="part_name" required>

        <!-- CATEGORY -->
        <label>Category</label>
        <select name="category_id" id="category_id" onchange="loadSubcategories(this.value)" required>
            <option value="">-- Select Category --</option>
            <?php while($row = $categories->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- SUBCATEGORY -->
        <label>Subcategory</label>
        <select name="subcategory_id" id="subcategory_id" onchange="loadTypes(this.value)">
            <option value="">-- Select Subcategory --</option>
        </select>

        <!-- TYPE -->
        <label>Type</label>
        <select name="type_id" id="type_id" onchange="loadComponents(this.value)">
            <option value="">-- Select Type --</option>
        </select>

        <!-- COMPONENT -->
        <label>Component</label>
        <select name="component_id" id="component_id">
            <option value="">-- Select Component --</option>
        </select>

        <!-- Stock Qty -->
        <label>Stock Qty</label>
        <input type="number" name="stock_qty" min="0" value="0">

        <!-- Cost Price -->
        <label>Cost Price</label>
        <input type="number" step="0.01" name="cost_price" min="0" value="0">

        <!-- Selling Price -->
        <label>Selling Price</label>
        <input type="number" step="0.01" name="selling_price" min="0" value="0">

        <button type="submit">Save Part</button>

    </form>
</div>


<!-- ⭐⭐⭐ UNIVERSAL EPC JS — READS FROM /ajax FOLDER ⭐⭐⭐ -->
<script>
function loadSubcategories(categoryId) {
    let sub = document.getElementById('subcategory_id');
    let type = document.getElementById('type_id');
    let comp = document.getElementById('component_id');

    sub.innerHTML = '<option>Loading...</option>';
    type.innerHTML = '<option value="">-- Select Type --</option>';
    comp.innerHTML = '<option value="">-- Select Component --</option>';

    if (!categoryId) return;

    fetch("ajax/epc_get_subcategories.php?category_id=" + categoryId)
        .then(r => r.json())
        .then(data => {
            sub.innerHTML = '<option value="">-- Select Subcategory --</option>';
            data.results.forEach(d => {
                sub.innerHTML += `<option value="${d.id}">${d.name}</option>`;
            });
        });
}

function loadTypes(subcategoryId) {
    let type = document.getElementById('type_id');
    let comp = document.getElementById('component_id');

    type.innerHTML = '<option>Loading...</option>';
    comp.innerHTML = '<option value="">-- Select Component --</option>';

    if (!subcategoryId) return;

    fetch("ajax/epc_get_types.php?subcategory_id=" + subcategoryId)
        .then(r => r.json())
        .then(data => {
            type.innerHTML = '<option value="">-- Select Type --</option>';
            data.results.forEach(d => {
                type.innerHTML += `<option value="${d.id}">${d.name}</option>`;
            });
        });
}

function loadComponents(typeId) {
    let comp = document.getElementById('component_id');

    comp.innerHTML = '<option>Loading...</option>';

    if (!typeId) return;

    fetch("ajax/epc_get_components.php?type_id=" + typeId)
        .then(r => r.json())
        .then(data => {
            comp.innerHTML = '<option value="">-- Select Component --</option>';
            data.results.forEach(d => {
                comp.innerHTML += `<option value="${d.id}">${d.name}</option>`;
            });
        });
}
</script>

</body>
</html>

<?php include "includes/footer.php"; ?>
