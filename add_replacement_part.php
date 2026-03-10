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
    display: block;
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

        <label>Category</label>
        <select name="category_id" id="category_id" required>
            <option value="">-- Select Category --</option>
            <?php
            $res = $conn->query("SELECT id, name FROM categories ORDER BY name");
            while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label>Subcategory</label>
        <select name="subcategory_id" id="subcategory_id" required>
            <option value="">-- Select Subcategory --</option>
        </select>

        <label>Type</label>
        <select name="type_id" id="type_id" required>
            <option value="">-- Select Type --</option>
        </select>

        <label>Item to Sell</label>
        <select name="component_id" id="component_id" required>
            <option value="">-- Select Item to Sell --</option>
        </select>

        <button type="submit" class="btn">Save Replacement Part</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(function () {

    $("#category_id").on("change", function () {
        $.getJSON(
            "ajax/epc_get_subcategories.php",
            { category_id: this.value },
            function (data) {
                let html = '<option value="">-- Select Subcategory --</option>';
                data.forEach(r => html += `<option value="${r.id}">${r.name}</option>`);
                $("#subcategory_id").html(html);
                $("#type_id").html('<option value="">-- Select Type --</option>');
                $("#component_id").html('<option value="">-- Select Item to Sell --</option>');
            }
        );
    });

    $("#subcategory_id").on("change", function () {
        $.getJSON(
            "ajax/epc_get_types.php",
            { subcategory_id: this.value },
            function (data) {
                let html = '<option value="">-- Select Type --</option>';
                data.forEach(r => html += `<option value="${r.id}">${r.name}</option>`);
                $("#type_id").html(html);
            }
        );
    });

    $("#type_id").on("change", function () {
        $.getJSON(
            "ajax/epc_get_sellable_components.php",
            { type_id: this.value },
            function (data) {
                let html = '<option value="">-- Select Item to Sell --</option>';
                data.forEach(row => {
                    html += `<option value="${row.id}">${row.label}</option>`;
                });
                $("#component_id").html(html);
            }
        );
    });

});
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
