<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

if (!isset($_GET['invoice_id']) || !is_numeric($_GET['invoice_id'])) {
    die("Invalid invoice ID.");
}

$invoice_id = (int)$_GET['invoice_id'];

// Load Invoice Header
$sql = "SELECT spi.*, s.supplier_name 
        FROM supplier_oem_invoices spi
        LEFT JOIN suppliers s ON s.id = spi.supplier_id
        WHERE spi.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invRes = $stmt->get_result();
$invoice = $invRes->fetch_assoc();

if (!$invoice) {
    die("Invoice not found.");
}

// Load existing OEM parts to populate dropdown
$parts = $conn->query("SELECT id, part_name FROM oem_parts ORDER BY part_name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add OEM Purchase Item</title>

<style>
body { 
    background:#000; 
    color:#fff; 
    font-family:Arial;
}
.wrap {
    width: 60%;
    margin: 30px auto;
    background: #111;
    border: 2px solid #b00000;
    padding: 25px;
    border-radius: 10px;
}
h2 {
    text-align:center;
    color:#ff3333;
}

/* Card */
.info-box {
    background:#000;
    padding:15px;
    border:1px solid #b00000;
    border-radius:5px;
    margin-bottom:20px;
}

label {
    display:block;
    margin-top:10px;
    font-size:14px;
    color:#ccc;
}

input, select, textarea {
    width:100%;
    padding:10px;
    border:1px solid #333;
    background:#222;
    color:#fff;
    border-radius:5px;
    margin-top:3px;
}

.btn-red {
    background:#b00000;
    border:none;
    color:#fff;
    padding:12px;
    width:100%;
    border-radius:6px;
    font-size:16px;
    cursor:pointer;
    margin-top:20px;
}
.btn-red:hover {
    background:#ff0000;
}

.flex {
    display:flex;
    gap:20px;
}
.flex div {
    flex:1;
}

.line-total-box {
    font-size:18px;
    padding:10px;
    margin-top:10px;
    border:1px solid #b00000;
    color:#ffcc00;
    text-align:center;
    background:#000;
}
</style>

<script>
function updateLineTotal() {
    let qty = parseFloat(document.getElementById("qty").value) || 0;
    let cost = parseFloat(document.getElementById("unit_cost").value) || 0;

    let total = qty * cost;

    document.getElementById("line_total_box").innerHTML = "Line Total: R " + total.toFixed(2);
}
</script>

</head>
<body>

<div class="wrap">

    <h2>Add OEM Purchase Item</h2>

    <!-- Invoice Summary -->
    <div class="info-box">
        <b>Supplier:</b> <?= htmlspecialchars($invoice['supplier_name']) ?><br>
        <b>Invoice #:</b> <?= htmlspecialchars($invoice['invoice_number']) ?><br>
        <b>Date:</b> <?= htmlspecialchars($invoice['invoice_date']) ?><br>
        <b>Current Total:</b> R <?= number_format($invoice['total_amount'], 2) ?>
    </div>

    <!-- ADD ITEM FORM -->
    <form action="oem_purchase_items_save.php" method="post">

        <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">

        <label>Part Name  
            <small style="color:#888">Dropdown or type new part</small>
        </label>
        <input list="parts_list" name="part_name" id="part_name" required>
        
        <datalist id="parts_list">
            <?php while($p = $parts->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($p['part_name']) ?>"></option>
            <?php endwhile; ?>
        </datalist>

        <div class="flex">
            <div>
                <label>Qty</label>
                <input type="number" name="qty" id="qty" min="1" value="1" oninput="updateLineTotal()" required>
            </div>

            <div>
                <label>Unit Cost</label>
                <input type="number" step="0.01" name="unit_cost" id="unit_cost" value="0" oninput="updateLineTotal()" required>
            </div>
        </div>

        <div id="line_total_box" class="line-total-box">
            Line Total: R 0.00
        </div>

        <label>Supplier Part Number (optional)</label>
        <input type="text" name="supplier_part_no">

        <label>Notes (optional)</label>
        <textarea name="notes" rows="2"></textarea>

        <button class="btn-red" type="submit">Save Item</button>

    </form>

</div>

</body>
</html>
