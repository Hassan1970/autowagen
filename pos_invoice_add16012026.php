<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php';

// Wizard step handling
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($step < 1 || $step > 4) {
    $step = 1;
}
?>

<div class="container">
    <h2>POS Invoice – Add</h2>

    <!-- ========================= -->
    <!-- STEP 1: CUSTOMER DETAILS -->
    <!-- ========================= -->
    <?php if ($step === 1): ?>
        <div class="card">
            <h3>1. Customer Details</h3>

            <p style="color:#aaa;">
                Customer details will be finalized in Phase 6.
            </p>

            <div style="text-align:right;">
                <a href="pos_invoice_add.php?step=2" class="btn btn-danger">
                    Next →
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ========================= -->
    <!-- STEP 2: INVOICE DETAILS -->
    <!-- ========================= -->
    <?php if ($step === 2): ?>
        <div class="card">
            <h3>2. Invoice Details</h3>

            <label>Invoice Date</label>
            <input type="date" value="<?= date('Y-m-d') ?>" />

            <label>Payment Method</label>
            <select>
                <option>Cash</option>
                <option>Card</option>
                <option>EFT</option>
            </select>

            <div class="actions">
                <a href="pos_invoice_add.php?step=1" class="btn">
                    ← Back
                </a>
                <a href="pos_invoice_add.php?step=3" class="btn btn-danger">
                    Next →
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ========================= -->
    <!-- STEP 3: ITEMS (LOCKED) -->
    <!-- ========================= -->
    <?php if ($step === 3): ?>
        <div class="card">
            <h3>3. Items</h3>

            <!-- Items Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:140px;">Type</th>
                        <th>Part Name</th>
                        <th style="width:80px;">Qty</th>
                        <th style="width:120px;">Price</th>
                        <th style="width:120px;">Subtotal</th>
                        <th style="width:90px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="item_type[]">
                                <option value="STRIPPED">STRIPPED</option>
                                <option value="OEM">OEM</option>
                                <option value="MANUAL">MANUAL</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="item_name[]" placeholder="Part name" />
                        </td>
                        <td>
                            <input type="number" name="item_qty[]" value="1" min="1" />
                        </td>
                        <td>
                            <input type="number" name="item_price[]" value="0" step="0.01" />
                        </td>
                        <td>
                            <input type="number" name="item_subtotal[]" value="0.00" step="0.01" readonly />
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm">
                                Remove
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="button" class="btn btn-secondary">
                + Add Item
            </button>

            <div class="actions">
                <a href="pos_invoice_add.php?step=2" class="btn">
                    ← Back
                </a>
                <a href="pos_invoice_add.php?step=4" class="btn btn-danger">
                    Next →
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ========================= -->
    <!-- STEP 4: TOTALS -->
    <!-- ========================= -->
    <?php if ($step === 4): ?>
        <div class="card">
            <h3>4. Totals</h3>

            <label>Subtotal</label>
            <input type="number" value="0.00" readonly />

            <label>VAT</label>
            <input type="number" value="0.00" readonly />

            <label>Total Amount</label>
            <input type="number" value="0.00" readonly />

            <div class="actions">
                <a href="pos_invoice_add.php?step=3" class="btn">
                    ← Back
                </a>
                <button class="btn btn-danger">
                    Save Invoice
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
