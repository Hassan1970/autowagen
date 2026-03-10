<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($invoice_id <= 0) {
    die("Invalid invoice ID.");
}

// Get invoice
$sql = "SELECT * FROM supplier_oem_invoices WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$res = $stmt->get_result();
$invoice = $res->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("Invoice not found.");
}

// Get suppliers
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
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
.form-group select,
.form-group textarea {
    background: #222;
    color: #fff;
    border: 1px solid #444;
    border-radius: 4px;
    padding: 7px 9px;
    font-size: 13px;
}
.form-group textarea {
    min-height: 80px;
    resize: vertical;
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
</style>

<div class="form-wrapper">
    <h2>Edit OEM Supplier Invoice</h2>

    <form action="oem_purchase_edit_save.php" method="post">
        <input type="hidden" name="id" value="<?php echo (int)$invoice['id']; ?>">

        <div class="form-grid">
            <div class="form-group">
                <label for="supplier_id">Supplier</label>
                <select name="supplier_id" id="supplier_id" required>
                    <option value="">-- Select Supplier --</option>
                    <?php while ($s = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo (int)$s['id']; ?>"
                            <?php echo ((int)$s['id'] === (int)$invoice['supplier_id']) ? 'selected' : ''; ?>>
                            <?php echo h($s['supplier_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="invoice_date">Invoice Date</label>
                <input type="date" name="invoice_date" id="invoice_date"
                       value="<?php echo h($invoice['invoice_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="invoice_number">Invoice Number</label>
                <input type="text" name="invoice_number" id="invoice_number"
                       value="<?php echo h($invoice['invoice_number']); ?>" required>
            </div>

            <div class="form-group">
                <label for="total_amount">Total Price Paid</label>
                <input type="number" step="0.01" min="0" name="total_amount" id="total_amount"
                       value="<?php echo h($invoice['total_amount']); ?>" required>
            </div>

            <div class="form-group">
                <label for="account_type">Account Type</label>
                <select name="account_type" id="account_type" required>
                    <?php
                    $options = ['Cash','30 Days','60 Days','90 Days'];
                    foreach ($options as $opt):
                    ?>
                        <option value="<?php echo $opt; ?>"
                            <?php echo ($invoice['account_type'] === $opt ? 'selected' : ''); ?>>
                            <?php echo $opt; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="reference">Reference (optional)</label>
                <input type="text" name="reference" id="reference"
                       value="<?php echo h($invoice['reference']); ?>">
            </div>
        </div>

        <div class="form-group" style="margin-top:15px;">
            <label for="notes">Notes (optional)</label>
            <textarea name="notes" id="notes"><?php echo h($invoice['notes']); ?></textarea>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn">Save Changes</button>
            <a href="oem_purchase_view.php?id=<?php echo (int)$invoice['id']; ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>
