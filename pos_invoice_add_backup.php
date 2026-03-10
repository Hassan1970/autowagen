<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

if (!function_exists('h')) {
    function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>POS Invoice - Add</title>
    <style>
        body { background:#000; color:#fff; font-family:Arial, sans-serif; }

        .page-wrap {
            width: 92%;
            margin: 25px auto 40px;
            background:#111;
            border:2px solid #b00000;
            border-radius:10px;
            padding:20px 25px 30px;
        }
        h2 {
            margin-top:0;
            color:#ff3333;
        }
        .section-title {
            margin:18px 0 8px;
            font-size:16px;
            font-weight:bold;
            color:#ff6666;
            border-bottom:1px solid #b00000;
            padding-bottom:4px;
        }
        .form-grid {
            display:grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap:10px 20px;
        }
        .form-group {
            margin-bottom:8px;
        }
        .form-group label {
            font-size:13px;
            color:#ddd;
            display:block;
            margin-bottom:3px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width:100%;
            padding:7px 9px;
            background:#222;
            border:1px solid #444;
            border-radius:6px;
            color:#fff;
            font-size:13px;
            box-sizing:border-box;
        }
        .form-group textarea { min-height:60px; }

        /* Customer search */
        #customer-results {
            margin-top:5px;
            background:#111;
            border:1px solid #444;
            border-radius:6px;
            max-height:160px;
            overflow-y:auto;
            display:none;
        }
        .customer-row {
            padding:6px 9px;
            border-bottom:1px solid #222;
            cursor:pointer;
            font-size:13px;
        }
        .customer-row:hover {
            background:#2a2a2a;
        }
        .small-note {
            font-size:11px;
            color:#aaa;
        }

        /* Items table */
        .items-table {
            width:100%;
            border-collapse:collapse;
            margin-top:8px;
            font-size:12px;
        }
        .items-table th,
        .items-table td {
            border:1px solid #b00000;
            padding:4px 6px;
            vertical-align:top;
        }
        .items-table th {
            background:#1b1b1b;
            color:#ff3333;
            text-align:left;
        }
        .items-table input,
        .items-table select,
        .items-table textarea {
            width:100%;
            box-sizing:border-box;
            background:#222;
            border:1px solid #444;
            border-radius:4px;
            color:#fff;
            font-size:12px;
            padding:4px 6px;
        }
        .items-table textarea { min-height:40px; }

        .item-actions-btn {
            padding:4px 8px;
            font-size:11px;
            border-radius:4px;
            border:1px solid #555;
            background:#222;
            color:#fff;
            cursor:pointer;
        }
        .item-actions-btn:hover {
            background:#333;
        }

        /* Item search */
        #item-search-box {
            margin-top:8px;
            padding:8px;
            background:#151515;
            border-radius:8px;
            border:1px solid #333;
        }
        #item-results {
            margin-top:6px;
            max-height:180px;
            overflow-y:auto;
            border:1px solid #333;
            border-radius:6px;
            display:none;
        }
        .item-row {
            padding:5px 8px;
            border-bottom:1px solid #222;
            font-size:12px;
            cursor:pointer;
        }
        .item-row:hover {
            background:#262626;
        }
        .item-row span.stock {
            color:#ffcc66;
        }
        .item-row span.vehicle {
            color:#8fd;
        }

        /* Totals */
        .totals-grid {
            display:grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap:8px 20px;
            margin-top:10px;
        }
        .totals-grid .form-group input {
            text-align:right;
        }

        /* Buttons */
        .btn-main {
            padding:8px 18px;
            border-radius:6px;
            border:1px solid #ff5555;
            background:#b00000;
            color:#fff;
            font-size:13px;
            font-weight:bold;
            cursor:pointer;
            margin-top:15px;
        }
        .btn-main:hover { background:#ff2222; }

        .btn-secondary {
            padding:7px 14px;
            border-radius:6px;
            border:1px solid #555;
            background:#222;
            color:#fff;
            font-size:13px;
            cursor:pointer;
            margin-top:15px;
            margin-right:8px;
            text-decoration:none;
            display:inline-block;
        }
        .btn-secondary:hover { background:#333; }

        @media (max-width:900px) {
            .form-grid, .totals-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="page-wrap">
    <h2>POS Invoice – Add</h2>

    <form action="pos_invoice_add_save.php" method="post" enctype="multipart/form-data" id="invoice-form">

        <!-- ========== SECTION 1: CUSTOMER ========== -->
        <div class="section-title">1. Customer Details</div>

        <div class="form-group">
            <label>Search Customer (Name / Address)</label>
            <div style="display:flex; gap:8px;">
                <input type="text" id="customer_search" placeholder="Type to search existing customers...">
                <button type="button" class="btn-secondary" id="btn_customer_search">Search</button>
                <button type="button" class="btn-secondary" id="btn_new_customer">New Customer</button>
            </div>
            <div id="customer-results"></div>
            <input type="hidden" name="customer_id" id="customer_id">
            <div class="small-note">
                Click a result to load details or use <b>New Customer</b> to capture a new one.
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="cust_full_name" id="cust_full_name" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="cust_phone" id="cust_phone">
            </div>

            <div class="form-group" style="grid-column:1 / span 2;">
                <label>Address *</label>
                <textarea name="cust_address" id="cust_address" required></textarea>
            </div>

            <div class="form-group">
                <label>SA ID / Passport Number</label>
                <input type="text" name="cust_id_number" id="cust_id_number">
            </div>

            <div class="form-group">
                <label>ID Document Scan (JPEG/PNG/PDF)</label>
                <input type="file" name="id_doc_file" accept=".jpg,.jpeg,.png,.pdf">
                <div class="small-note">For second-hand parts: keep on file for SAPS / Secondhand Goods Act.</div>
            </div>

            <div class="form-group">
                <label>Proof of Residence (JPEG/PNG/PDF)</label>
                <input type="file" name="proof_res_file" accept=".jpg,.jpeg,.png,.pdf">
            </div>
        </div>

        <!-- ========== SECTION 2: INVOICE HEADER ========== -->
        <div class="section-title">2. Invoice Details</div>

        <div class="form-grid">
            <div class="form-group">
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" placeholder="Leave blank to auto-generate">
            </div>
            <div class="form-group">
                <label>Invoice Date *</label>
                <input type="date" name="invoice_date" required value="<?= date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label>Payment Method *</label>
                <select name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="EFT">EFT</option>
                    <option value="Mixed">Mixed (Cash + Card + EFT)</option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="vat_enabled" id="vat_enabled" value="1" checked>
                    Charge VAT (15%)
                </label>
            </div>

            <div class="form-group" style="grid-column:1 / span 2;">
                <label>Invoice Notes</label>
                <textarea name="invoice_notes" placeholder="Any extra notes about this sale..."></textarea>
            </div>
        </div>

        <!-- ========== SECTION 3: ITEMS ========== -->
        <div class="section-title">3. Items</div>

        <!-- Part search area -->
        <div id="item-search-box">
            <label style="font-size:13px;">Search Stripped Parts (Part / Stock / Vehicle)</label>
            <div style="display:flex; gap:8px; margin-top:3px;">
                <input type="text" id="item_search" placeholder="e.g. bumper / AW1 / Corolla">
                <button type="button" class="btn-secondary" id="btn_item_search">Search Part</button>
            </div>
            <div id="item-results"></div>
            <div class="small-note">
                Click a result to add it as a line item. You can still edit price, qty, etc.
            </div>
        </div>

        <table class="items-table" id="items-table">
            <thead>
                <tr>
                    <th style="width:80px;">Type</th>
                    <th>Description / Part Name</th>
                    <th style="width:60px;">Qty</th>
                    <th style="width:90px;">Unit Price</th>
                    <th style="width:90px;">Line Total</th>
                    <th style="width:70px;">Action</th>
                </tr>
            </thead>
            <tbody id="items-body">
                <tr>
                    <td>
                        <select name="item_type[]">
                            <option value="Stripped">Stripped</option>
                            <option value="OEM">OEM</option>
                            <option value="Replacement">Replacement</option>
                            <option value="Other">Other</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="item_desc[]" placeholder="Part name / description..."></textarea>
                    </td>
                    <td>
                        <input type="number" name="item_qty[]" min="1" step="1" value="1" class="qty-input">
                    </td>
                    <td>
                        <input type="number" name="item_price[]" min="0" step="0.01" value="0.00" class="price-input">
                    </td>
                    <td>
                        <input type="text" name="item_total[]" value="0.00" class="line-total" readonly>
                    </td>
                    <td>
                        <button type="button" class="item-actions-btn btn-remove-row">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="item-actions-btn" id="btn_add_row" style="margin-top:6px;">
            + Add Manual Item
        </button>

        <!-- ========== SECTION 4: TOTALS ========== -->
        <div class="section-title">4. Totals</div>

        <div class="totals-grid">
            <div class="form-group">
                <label>Subtotal</label>
                <input type="text" name="subtotal" id="subtotal" value="0.00" readonly>
            </div>
            <div class="form-group">
                <label>VAT (15%)</label>
                <input type="text" name="vat_amount" id="vat_amount" value="0.00" readonly>
            </div>
            <div class="form-group">
                <label>Total Amount</label>
                <input type="text" name="total_amount" id="total_amount" value="0.00" readonly>
            </div>
            <div class="form-group">
                <label>Amount Paid</label>
                <input type="number" name="amount_paid" id="amount_paid" min="0" step="0.01" value="0.00">
            </div>
            <div class="form-group">
                <label>Balance</label>
                <input type="text" name="balance" id="balance" value="0.00" readonly>
            </div>
        </div>

        <div style="margin-top:18px;">
            <button type="submit" class="btn-main">Save Invoice</button>
            <a href="sold_parts_list.php" class="btn-secondary">Back</a>
        </div>

    </form>
</div>

<script>
// ---------------- CUSTOMER SEARCH ----------------
function renderCustomerResults(list) {
    const box = document.getElementById('customer-results');
    box.innerHTML = '';
    if (!list || list.length === 0) {
        box.style.display = 'none';
        return;
    }
    list.forEach(function(row) {
        const div = document.createElement('div');
        div.className = 'customer-row';
        div.textContent = row.full_name + (row.address ? ' — ' + row.address : '');
        div.addEventListener('click', function() {
            document.getElementById('customer_id').value    = row.id;
            document.getElementById('cust_full_name').value = row.full_name || '';
            document.getElementById('cust_address').value   = row.address || '';
            document.getElementById('cust_phone').value     = row.phone || '';
            document.getElementById('cust_id_number').value = row.id_number || '';
            box.style.display = 'none';
        });
        box.appendChild(div);
    });
    box.style.display = 'block';
}

document.getElementById('btn_customer_search').addEventListener('click', function () {
    const q = document.getElementById('customer_search').value.trim();
    if (q === '') return;
    fetch('pos_customer_search.php?q=' + encodeURIComponent(q))
        .then(resp => resp.json())
        .then(data => renderCustomerResults(data))
        .catch(err => console.error(err));
});

document.getElementById('btn_new_customer').addEventListener('click', function () {
    document.getElementById('customer_id').value = '';
    document.getElementById('cust_full_name').value = '';
    document.getElementById('cust_address').value = '';
    document.getElementById('cust_phone').value = '';
    document.getElementById('cust_id_number').value = '';
    document.getElementById('customer-results').style.display = 'none';
});

// ---------------- ITEM SEARCH ----------------
function renderItemResults(list) {
    const box = document.getElementById('item-results');
    box.innerHTML = '';
    if (!list || list.length === 0) {
        box.style.display = 'none';
        return;
    }
    list.forEach(function(row) {
        const div = document.createElement('div');
        div.className = 'item-row';
        let label = '';
        if (row.stock_code) label += '[' + row.stock_code + '] ';
        label += row.part_name || '';
        if (row.vehicle) label += ' — ' + row.vehicle;
        if (row.price)   label += ' (R ' + row.price + ')';

        div.innerHTML = '<span class="stock">' + (row.stock_code || '') +
                        '</span> ' + h(label);

        div.addEventListener('click', function() {
            addItemFromSearch(row);
            box.style.display = 'none';
        });
        box.appendChild(div);
    });
    box.style.display = 'block';
}

// Simple HTML escape
function h(s) {
    return String(s || '').replace(/[&<>"']/g, function(c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
}

document.getElementById('btn_item_search').addEventListener('click', function () {
    const q = document.getElementById('item_search').value.trim();
    if (q === '') return;
    fetch('pos_item_search.php?q=' + encodeURIComponent(q))
        .then(resp => resp.json())
        .then(data => renderItemResults(data))
        .catch(err => console.error(err));
});

function addItemFromSearch(row) {
    const tbody = document.getElementById('items-body');
    const tr = document.createElement('tr');
    const price = row.price ? parseFloat(row.price) : 0;
    tr.innerHTML = `
        <td>
            <select name="item_type[]">
                <option value="Stripped" selected>Stripped</option>
                <option value="OEM">OEM</option>
                <option value="Replacement">Replacement</option>
                <option value="Other">Other</option>
            </select>
        </td>
        <td>
            <textarea name="item_desc[]">${h(row.part_name || '')}</textarea>
        </td>
        <td>
            <input type="number" name="item_qty[]" min="1" step="1" value="1" class="qty-input">
        </td>
        <td>
            <input type="number" name="item_price[]" min="0" step="0.01" value="${price.toFixed(2)}" class="price-input">
        </td>
        <td>
            <input type="text" name="item_total[]" value="${price.toFixed(2)}" class="line-total" readonly>
        </td>
        <td>
            <button type="button" class="item-actions-btn btn-remove-row">Remove</button>
        </td>
    `;
    tbody.appendChild(tr);
    recalcTotals();
}

// ---------------- ITEMS & TOTALS ----------------
function recalcTotals() {
    const tbody = document.getElementById('items-body');
    let subtotal = 0;

    tbody.querySelectorAll('tr').forEach(function (row) {
        const qtyInput   = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const lineTotal  = row.querySelector('.line-total');

        const qty   = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = qty * price;

        lineTotal.value = total.toFixed(2);
        subtotal += total;
    });

    document.getElementById('subtotal').value = subtotal.toFixed(2);

    const vatEnabled = document.getElementById('vat_enabled').checked;
    const vatAmount  = vatEnabled ? subtotal * 0.15 : 0;
    document.getElementById('vat_amount').value = vatAmount.toFixed(2);

    const totalAmount = subtotal + vatAmount;
    document.getElementById('total_amount').value = totalAmount.toFixed(2);

    const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
    const balance    = totalAmount - amountPaid;
    document.getElementById('balance').value = balance.toFixed(2);
}

document.getElementById('items-body').addEventListener('input', function(e) {
    if (e.target.classList.contains('qty-input') ||
        e.target.classList.contains('price-input')) {
        recalcTotals();
    }
});

document.getElementById('vat_enabled').addEventListener('change', recalcTotals);
document.getElementById('amount_paid').addEventListener('input', recalcTotals);

document.getElementById('btn_add_row').addEventListener('click', function () {
    const tbody = document.getElementById('items-body');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="item_type[]">
                <option value="Stripped">Stripped</option>
                <option value="OEM">OEM</option>
                <option value="Replacement">Replacement</option>
                <option value="Other">Other</option>
            </select>
        </td>
        <td>
            <textarea name="item_desc[]" placeholder="Part name / description..."></textarea>
        </td>
        <td>
            <input type="number" name="item_qty[]" min="1" step="1" value="1" class="qty-input">
        </td>
        <td>
            <input type="number" name="item_price[]" min="0" step="0.01" value="0.00" class="price-input">
        </td>
        <td>
            <input type="text" name="item_total[]" value="0.00" class="line-total" readonly>
        </td>
        <td>
            <button type="button" class="item-actions-btn btn-remove-row">Remove</button>
        </td>
    `;
    tbody.appendChild(tr);
});

document.getElementById('items-body').addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-remove-row')) {
        const tr = e.target.closest('tr');
        const tbody = document.getElementById('items-body');
        if (tbody.querySelectorAll('tr').length > 1) {
            tr.remove();
            recalcTotals();
        }
    }
});

// initial totals
recalcTotals();
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
