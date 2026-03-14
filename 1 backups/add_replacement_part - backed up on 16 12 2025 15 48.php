<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";
?>

<style>
.form-wrapper {
    width: 65%;
    margin: 30px auto;
    background: #111;
    border: 2px solid red;
    padding: 30px;
    border-radius: 10px;
    color: white;
}
.form-wrapper label {
    font-weight: bold;
    margin-top: 12px;
}
.form-wrapper input,
.form-wrapper select,
.form-wrapper textarea {
    background: #222;
    color: white;
    border: 1px solid #444;
    width: 100%;
    padding: 10px;
    border-radius: 6px;
}
.form-wrapper h2 {
    text-align: center;
    color: red;
    margin-bottom: 25px;
}
</style>

<div class="form-wrapper">
    <h2>Add Replacement Part</h2>

    <form action="add_replacement_part_save.php" method="POST">

        <label>Part Number</label>
        <input type="text" name="part_number" required>

        <label>Part Name</label>
        <input type="text" name="part_name" required>

        <!-- CATEGORY -->
        <label>Category</label>
        <select name="category_id" id="category_id" required>
            <option value="">-- Select Category --</option>
            <?php
            $res = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <!-- SUBCATEGORY -->
        <label>Subcategory</label>
        <select name="subcategory_id" id="subcategory_id" required>
            <option value="">-- Select Subcategory --</option>
        </select>

        <!-- TYPE -->
        <label>Type</label>
        <select name="type_id" id="type_id" required>
            <option value="">-- Select Type --</option>
        </select>

        <!-- COMPONENT -->
        <label>Component</label>
        <select name="component_id" id="component_id" required>
            <option value="">-- Select Component --</option>
        </select>

        <label>Cost Price (BLACKWHITE)</label>
        <input type="number" name="cost_price" step="0.01">

        <label>Selling Price</label>
        <input type="number" name="selling_price" step="0.01">

        <label>Opening Stock Qty</label>
        <input type="number" name="opening_stock" value="0">

        <label>Notes</label>
        <textarea name="notes"></textarea>

        <br><br>
        <button type="submit" class="btn">Save Replacement Part</button>

    </form>
</div>


<!-- ⭐⭐⭐ JAVASCRIPT MUST BE BELOW THE HTML ⭐⭐⭐ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {

    // ----------------------------------------
    // LOAD SUBCATEGORIES
    // ----------------------------------------
    $("#category_id").change(function () {

        let catID = $(this).val();

        $("#subcategory_id").html('<option>Loading...</option>');
        $("#type_id").html('<option>-- Select Type --</option>');
        $("#component_id").html('<option>-- Select Component --</option>');

        $.getJSON(
            "/autowagen_master_clean/ajax/epc_get_subcategories.php",
            { category_id: catID },
            function (data) {
                let html = '<option value="">-- Select Subcategory --</option>';
                data.forEach(row => {
                    html += `<option value="${row.id}">${row.name}</option>`;
                });
                $("#subcategory_id").html(html);
            }
        );
    });

    // ----------------------------------------
    // LOAD TYPES
    // ----------------------------------------
    $("#subcategory_id").change(function () {

        let subID = $(this).val();

        $("#type_id").html('<option>Loading...</option>');
        $("#component_id").html('<option>-- Select Component --</option>');

        $.getJSON(
            "/autowagen_master_clean/ajax/epc_get_types.php",
            { subcategory_id: subID },
            function (data) {
                let html = '<option value="">-- Select Type --</option>';
                data.forEach(row => {
                    html += `<option value="${row.id}">${row.name}</option>`;
                });
                $("#type_id").html(html);
            }
        );
    });

    // ----------------------------------------
    // LOAD COMPONENTS
    // ----------------------------------------
    $("#type_id").change(function () {

        let typeID = $(this).val();

        $("#component_id").html('<option>Loading...</option>');

        $.getJSON(
            "/autowagen_master_clean/ajax/epc_get_components.php",
            { type_id: typeID },
            function (data) {
                let html = '<option value="">-- Select Component --</option>';
                data.forEach(row => {
                    html += `<option value="${row.id}">${row.name}</option>`;
                });
                $("#component_id").html(html);
            }
        );
    });

});
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
