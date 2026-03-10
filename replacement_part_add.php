<?php
$pageTitle = "Add Replacement Part";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/config/config.php";

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<style>
.form-box {
    width: 70%;
    margin: 20px auto;
    background: #111;
    border: 2px solid #b00000;
    padding: 25px;
    border-radius: 8px;
    color: white;
}
.form-box label { font-weight: bold; margin-top: 10px; display: block; }
.form-box input, select, textarea {
    width: 100%;
    background: #222;
    border: 1px solid #444;
    padding: 8px;
    border-radius: 6px;
    color: white;
}
.btn-red { 
    background:#b00000; padding:10px; border:none; 
    color:white; border-radius:6px; font-weight:bold; cursor:pointer; width:100%;
}
.btn-red:hover { background:#e00000; }
</style>

<div class="form-box">
    <h2 style="text-align:center;color:red;">Add Replacement Part</h2>

    <form action="add_replacement_part_save.php" method="POST">

        <label>Part Number</label>
        <input type="text" name="part_number" required>

        <label>Part Name</label>
        <input type="text" name="part_name" required>

        <label>Category</label>
        <select id="category" name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php while($c = $categories->fetch_assoc()): ?>
                <option value="<?= $c['id']; ?>"><?= h($c['name']); ?></option>
            <?php endwhile; ?>
        </select>

        <label>Subcategory</label>
        <select id="subcategory" name="subcategory_id" required>
            <option value="">-- Select Subcategory --</option>
        </select>

        <label>Type</label>
        <select id="type" name="type_id" required>
            <option value="">-- Select Type --</option>
        </select>

        <label>Component</label>
        <select id="component" name="component_id" required>
            <option value="">-- Select Component --</option>
        </select>

        <label>Cost Price</label>
        <input type="number" step="0.01" name="cost_price">

        <label>Selling Price</label>
        <input type="number" step="0.01" name="selling_price">

        <label>Notes</label>
        <textarea name="notes"></textarea>

        <button class="btn-red">Save Replacement Part</button>

    </form>
</div>

<script src="assets/js/jquery.js"></script>

<script>
$("#category").change(function() {
    const id = $(this).val();
    $("#subcategory").html('<option>Loading...</option>');
    $("#type").html('<option>-- Select Type --</option>');
    $("#component").html('<option>-- Select Component --</option>');

    $.getJSON("ajax/epc_get_subcategories.php?category_id=" + id, function(data) {
        let opt = '<option value="">-- Select Subcategory --</option>';
        data.forEach(row => opt += `<option value="${row.id}">${row.name}</option>`);
        $("#subcategory").html(opt);
    });
});

$("#subcategory").change(function() {
    const id = $(this).val();
    $("#type").html('<option>Loading...</option>');
    $("#component").html('<option>-- Select Component --</option>');

    $.getJSON("ajax/epc_get_types.php?subcategory_id=" + id, function(data) {
        let opt = '<option value="">-- Select Type --</option>';
        data.forEach(row => opt += `<option value="${row.id}">${row.name}</option>`);
        $("#type").html(opt);
    });
});

$("#type").change(function() {
    const id = $(this).val();
    $("#component").html('<option>Loading...</option>');

    $.getJSON("ajax/epc_get_components.php?type_id=" + id, function(data) {
        let opt = '<option value="">-- Select Component --</option>';
        data.forEach(row => opt += `<option value="${row.id}">${row.name}</option>`);
        $("#component").html(opt);
    });
});
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>

