<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

// Pre-selected invoice (from View screen)
$pre_invoice_id = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

// Load invoices for dropdown
$invSql = "SELECT i.id, i.invoice_number, i.invoice_date, s.supplier_name
           FROM supplier_oem_invoices i
           LEFT JOIN suppliers s ON i.supplier_id = s.id
           ORDER BY i.id DESC";
$invoices = $conn->query($invSql);

// categories
$cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<style>
.form-wrapper {
    width: 80%;
    margin: 25px auto;
    background: #111;
    border: 2px solid #b00000;
    padding: 25px 30px 30px;
    border-radius: 8px;
    color: #fff;
}
.form-wrapper h2 {
    text-align: center;
    color: #ff3333;
    margin-bottom: 20px;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(260px,1fr));
    gap: 15px 30px;
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group label {
    font-weight: bold;
    margin-bottom: 3px;
    color: #ff3333;
}
.form-group input,
.form-group select {
    background: #222;
    color: #fff;
    border: 1px solid #444;
    border-radius: 4px;
    padding: 7px 9px;
    font-size: 13px;
}
.btn {
    display: inline-block;
    background: #b00000;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 7px 16px;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
}
.btn:hover {
    background: #ff3333;
}
.btn-row {
    margin-top: 18px;
    display: flex;
    gap: 10px;
}
.note-small {
    font-size: 11px;
    color: #aaa;
}
</style>

<div class="form-wrapper">
    <h2>Add OEM Part</h2>

    <form action="oem_parts_add_save.php" method="post">
        <div class="form-grid">
            <div class="form-group">
                <label for="supplier_oem_invoice_id">Linked OEM Supplier Invoice</label>
                <select name="supplier_oem_invoice_id" id="supplier_oem_invoice_id">
                    <option value="">-- No Invoice / Not Linked --</option>
                    <?php while ($i = $invoices->fetch_assoc()): ?>
                        <?php
                        $optVal = (int)$i['id'];
                        $label = sprintf(
                            "#%d - %s (%s) - %s",
                            $i['id'],
                            $i['invoice_number'],
                            $i['invoice_date'],
                            $i['supplier_name']
                        );
                        ?>
                        <option value="<?php echo $optVal; ?>"
                            <?php echo ($pre_invoice_id && $pre_invoice_id == $optVal) ? 'selected' : ''; ?>>
                            <?php echo h($label); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <span class="note-small">
                    If you came from an invoice, it will already be selected.
                </span>
            </div>

            <div class="form-group">
                <label for="oem_number">OEM Number</label>
                <input type="text" name="oem_number" id="oem_number">
            </div>

            <div class="form-group">
                <label for="part_name">Part Name</label>
                <input type="text" name="part_name" id="part_name" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select name="category_id" id="category_id">
                    <option value="">-- Select Category --</option>
                    <?php while ($c = $cats->fetch_assoc()): ?>
                        <option value="<?php echo (int)$c['id']; ?>">
                            <?php echo h($c['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subcategory_id">Subcategory</label>
                <select name="subcategory_id" id="subcategory_id">
                    <option value="">-- Select Subcategory --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="type_id">Type</label>
                <select name="type_id" id="type_id">
                    <option value="">-- Select Type --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="component_id">Component</label>
                <select name="component_id" id="component_id">
                    <option value="">-- Select Component --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="stock_qty">Stock Qty</label>
                <input type="number" name="stock_qty" id="stock_qty" min="0" value="0">
            </div>

            <div class="form-group">
                <label for="cost_price">Cost Price</label>
                <input type="number" step="0.01" min="0" name="cost_price" id="cost_price" value="0">
            </div>

            <div class="form-group">
                <label for="selling_price">Selling Price</label>
                <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" value="0">
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn">Save OEM Part</button>
            <?php if ($pre_invoice_id > 0): ?>
                <a href="oem_purchase_view.php?id=<?php echo $pre_invoice_id; ?>" class="btn">Back to Invoice</a>
            <?php else: ?>
                <a href="oem_parts_list.php" class="btn">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
// Cascading EPC dropdowns using existing AJAX endpoints
document.addEventListener('DOMContentLoaded', function () {
    const cat    = document.getElementById('category_id');
    const subcat = document.getElementById('subcategory_id');
    const type   = document.getElementById('type_id');
    const comp   = document.getElementById('component_id');

    function fetchAndFill(url, sourceId, targetSelect, placeholder) {
        const val = sourceId.value;
        targetSelect.innerHTML = "<option value=''>" + placeholder + "</option>";
        if (!val) return;

        fetch(url + '?id=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(data => {
                targetSelect.innerHTML = "<option value=''>" + placeholder + "</option>";
                if (Array.isArray(data)) {
                    data.forEach(function (row) {
                        const opt = document.createElement('option');
                        opt.value = row.id;
                        opt.textContent = row.name;
                        targetSelect.appendChild(opt);
                    });
                }
            })
            .catch(() => {});
    }

    cat.addEventListener('change', function () {
        subcat.innerHTML = "<option value=''>-- Select Subcategory --</option>";
        type.innerHTML   = "<option value=''>-- Select Type --</option>";
        comp.innerHTML   = "<option value=''>-- Select Component --</option>";

        fetchAndFill('ajax/epc_get_subcategories.php', cat, subcat, '-- Select Subcategory --');
    });

    subcat.addEventListener('change', function () {
        type.innerHTML = "<option value=''>-- Select Type --</option>";
        comp.innerHTML = "<option value=''>-- Select Component --</option>";

        fetchAndFill('ajax/epc_get_types.php', subcat, type, '-- Select Type --');
    });

    type.addEventListener('change', function () {
        comp.innerHTML = "<option value=''>-- Select Component --</option>";
        fetchAndFill('ajax/epc_get_components.php', type, comp, '-- Select Component --');
    });
});
</script>
