<?php
require_once "config/config.php";
include "includes/header.php";

if (!isset($_GET['part_id']) || !isset($_GET['vehicle_id'])) {
    die("<h2 style='color:red;'>Invalid request.</h2>");
}

$part_id    = (int)$_GET['part_id'];
$vehicle_id = (int)$_GET['vehicle_id'];

// Load stripped part
$sql = "
    SELECT * FROM vehicle_stripped_parts 
    WHERE id = ? LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $part_id);
$stmt->execute();
$part = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$part) {
    die("<h2 style='color:red;'>Part not found.</h2>");
}

// Load dropdown data
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

$subcategories = $conn->query("
    SELECT id, name 
    FROM subcategories 
    WHERE category_id = {$part['category_id']}
    ORDER BY name ASC
");

$types = $conn->query("
    SELECT id, name 
    FROM types 
    WHERE subcategory_id = {$part['subcategory_id']}
    ORDER BY name ASC
");

$components = $conn->query("
    SELECT id, name 
    FROM components 
    WHERE type_id = {$part['type_id']}
    ORDER BY name ASC
");
?>

<style>
.page-wrap {
    width: 85%;
    margin: 25px auto;
    color: #fff;
}

.card {
    background: #111;
    padding: 20px;
    border: 2px solid #b00000;
    border-radius: 10px;
}

h2 {
    text-align: center;
    color: #ff3333;
}

form label {
    font-weight: bold;
    display: block;
    margin-top: 12px;
}

input, select, textarea {
    background: #222;
    color: #fff;
    border: 1px solid #444;
    padding: 8px;
    width: 100%;
    border-radius: 6px;
}

.btn-main {
    background: #b00000;
    color: #fff;
    padding: 10px;
    width: 100%;
    border-radius: 6px;
    font-weight: bold;
    border: none;
    margin-top: 20px;
    cursor: pointer;
}
.btn-main:hover {
    background: #ff1a1a;
}

.back-link {
    display: inline-block;
    color: #ff3333;
    margin-bottom: 10px;
    text-decoration: none;
}
</style>

<div class="page-wrap">

    <a class="back-link" href="vehicle_profile.php?vehicle_id=<?= $vehicle_id ?>">← Back to Vehicle Profile</a>

    <div class="card">
        <h2>Edit Stripped Part</h2>

        <form action="vehicle_stripping_edit_save.php" method="POST">

            <input type="hidden" name="part_id" value="<?= $part_id ?>">
            <input type="hidden" name="vehicle_id" value="<?= $vehicle_id ?>">

            <label>Category</label>
            <select name="category_id" id="category" required>
                <option value="">-- Select Category --</option>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" 
                        <?= $c['id'] == $part['category_id'] ? 'selected' : '' ?>>
                        <?= $c['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Subcategory</label>
            <select name="subcategory_id" id="subcategory" required>
                <?php while ($sc = $subcategories->fetch_assoc()): ?>
                    <option value="<?= $sc['id'] ?>" 
                        <?= $sc['id'] == $part['subcategory_id'] ? 'selected' : '' ?>>
                        <?= $sc['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Type</label>
            <select name="type_id" id="type" required>
                <?php while ($t = $types->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>"
                        <?= $t['id'] == $part['type_id'] ? 'selected' : '' ?>>
                        <?= $t['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Component</label>
            <select name="component_id" id="component" required>
                <?php while ($co = $components->fetch_assoc()): ?>
                    <option value="<?= $co['id'] ?>"
                        <?= $co['id'] == $part['component_id'] ? 'selected' : '' ?>>
                        <?= $co['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Part Condition</label>
            <select name="part_condition">
                <option value="Good" <?= $part['part_condition']=='Good'?'selected':'' ?>>Good</option>
                <option value="Fair" <?= $part['part_condition']=='Fair'?'selected':'' ?>>Fair</option>
                <option value="Damaged / For Spares" <?= $part['part_condition']=='Damaged / For Spares'?'selected':'' ?>>Damaged / For Spares</option>
            </select>

            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($part['location']) ?>">

            <label>Quantity</label>
            <input type="number" name="qty" min="1" value="<?= (int)$part['qty'] ?>">

            <label>Notes</label>
            <textarea name="notes" rows="3"><?= htmlspecialchars($part['notes']) ?></textarea>

            <button type="submit" class="btn-main">Save Changes</button>

        </form>
    </div>
</div>

<!-- AJAX FOR CASCADING EPC DROPDOWNS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$("#category").change(function() {
    var id = $(this).val();
    $("#subcategory").html('<option>Loading...</option>');
    $("#type").html('<option></option>');
    $("#component").html('<option></option>');

    $.get("ajax/ajax_epc_get_subcategories.php", { category_id:id }, function(data){
        let html = '';
        data.forEach(row => html += `<option value="${row.id}">${row.name}</option>`);
        $("#subcategory").html(html);
    }, "json");
});

$("#subcategory").change(function() {
    var id = $(this).val();
    $("#type").html('<option>Loading...</option>');
    $("#component").html('<option></option>');

    $.get("ajax/ajax_epc_get_types.php", { subcategory_id:id }, function(data){
        let html = '';
        data.forEach(row => html += `<option value="${row.id}">${row.name}</option>`);
        $("#type").html(html);
    }, "json");
});

$("#type").change(function() {
    var id = $(this).val();
    $("#component").html('<option>Loading...</option>');

    $.get("ajax/ajax_epc_get_components.php", { type_id:id }, function(data){
        let html = '';
        data.forEach(row => html += `<option value="${row.id}">${row.name}</option>`);
        $("#component").html(html);
    }, "json");
});
</script>

<?php include "includes/footer.php"; ?>
