<?php
require_once "config/config.php";
include "includes/header.php";

// -----------------------------
// Get OEM part ID from query
// -----------------------------
$part_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($part_id <= 0) {
    die("Invalid OEM part ID.");
}

// -----------------------------
// Load current part data
// -----------------------------
$stmt = $conn->prepare("SELECT * FROM oem_parts WHERE id = ?");
$stmt->bind_param("i", $part_id);
$stmt->execute();
$result = $stmt->get_result();
$part = $result->fetch_assoc();
$stmt->close();

if (!$part) {
    die("OEM part not found.");
}

// -----------------------------
// Load suppliers
// -----------------------------
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");

// -----------------------------
// Load categories
// -----------------------------
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

// Helper for safe output
function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<style>
.page {
    width: 90%;
    margin: 20px auto;
    background: #111;
    padding: 20px;
    border: 2px solid #b00000;
    color: white;
    border-radius: 8px;
}
h2 {
    color: #ff3333;
    text-align: center;
    margin-bottom: 20px;
}
label {
    color: #ff3333;
    font-weight: bold;
    margin-top: 10px;
    display: block;
}
input, select, textarea {
    width: 100%;
    padding: 8px;
    background: #222;
    border: 1px solid #444;
    color: white;
    border-radius: 4px;
}
.btn {
    background: #b00000;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    border: none;
    text-decoration: none;
    cursor: pointer;
}
.btn:hover {
    background: #ff3333;
}
.btn-secondary {
    background: #444;
}
.btn-secondary:hover {
    background: #666;
}
.form-row {
    display: flex;
    gap: 15px;
}
.form-row > div {
    flex: 1;
}
</style>

<div class="page">
    <h2>Edit OEM Part</h2>

    <form action="oem_parts_edit_save.php" method="POST">
        <!-- Hidden ID -->
        <input type="hidden" name="id" value="<?php echo (int)$part['id']; ?>">

        <div class="form-row">
            <div>
                <label for="oem_number">OEM Number</label>
                <input type="text" id="oem_number" name="oem_number"
                       value="<?php echo e($part['oem_number'] ?? ''); ?>" required>
            </div>

            <div>
                <label for="part_name">Part Name</label>
                <input type="text" id="part_name" name="part_name"
                       value="<?php echo e($part['part_name'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo (int)$cat['id']; ?>"
                            <?php if (!empty($part['category_id']) && $part['category_id'] == $cat['id']) echo 'selected'; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label for="supplier_id">Default Supplier (optional)</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="">-- Select Supplier --</option>
                    <?php
                    // we assume a column like supplier_id in oem_parts
                    $currentSupplierId = $part['supplier_id'] ?? null;
                    while ($sup = $suppliers->fetch_assoc()):
                    ?>
                        <option value="<?php echo (int)$sup['id']; ?>"
                            <?php if ($currentSupplierId == $sup['id']) echo 'selected'; ?>>
                            <?php echo e($sup['supplier_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="stock_qty">Stock Quantity</label>
                <input type="number" id="stock_qty" name="stock_qty" step="1" min="0"
                       value="<?php echo e($part['stock_qty'] ?? '0'); ?>">
            </div>

            <div>
                <label for="cost_price">Cost Price</label>
                <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                       value="<?php echo e($part['cost_price'] ?? '0.00'); ?>">
            </div>

            <div>
                <label for="selling_price">Selling Price</label>
                <input type="number" id="selling_price" name="selling_price" step="0.01" min="0"
                       value="<?php echo e($part['selling_price'] ?? '0.00'); ?>">
            </div>
        </div>

        <label for="notes">Notes (optional)</label>
        <textarea id="notes" name="notes" rows="3"><?php echo e($part['notes'] ?? ''); ?></textarea>

        <br><br>

        <button type="submit" class="btn">Save Changes</button>
        <a href="oem_parts_list.php" class="btn btn-secondary">Back to OEM Parts List</a>
    </form>
</div>

<?php
include "includes/footer.php";
?>
