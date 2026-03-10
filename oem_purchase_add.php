<?php
require_once "config/config.php";
include "includes/header.php";

// Load suppliers for dropdown
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
?>
<style>
.page {
    width: 90%;
    margin: 20px auto;
    background: #111;
    padding: 20px;
    border: 2px solid #b00000;
    color: #fff;
    border-radius: 8px;
}
h2 {
    color:#ff3333;
    text-align:center;
    margin-bottom:20px;
}
label {
    color:#ff3333;
    font-weight:bold;
    margin-top:10px;
    display:block;
}
input, select, textarea {
    width: 100%;
    padding:8px;
    background:#222;
    border:1px solid #444;
    color:white;
    border-radius:4px;
}
.btn {
    background:#b00000;
    color:white;
    padding:8px 15px;
    border-radius:5px;
    border:none;
    cursor:pointer;
    text-decoration:none;
}
.btn:hover { background:#ff3333; }
.btn-grey {
    background:#444;
    color:#eee;
}
.btn-grey:hover { background:#666; }

.form-row {
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}
.form-row > div {
    flex:1;
    min-width:220px;
}

/* Simple modal styling */
.modal-backdrop {
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.7);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
}
.modal {
    background:#111;
    border-radius:8px;
    border:2px solid #b00000;
    padding:20px;
    width:500px;
    max-width:95%;
    color:white;
}
.modal h2 {
    margin-top:0;
    margin-bottom:15px;
}
</style>

<div class="page">
    <h2>Add OEM Supplier Invoice</h2>

    <form action="oem_purchase_add_save.php" method="POST">
        <div class="form-row">
            <div>
                <label>Supplier</label>
                <select name="supplier_id" id="supplier_id" required>
                    <option value="">-- Select Supplier --</option>
                    <?php while ($s = $suppliers->fetch_assoc()): ?>
                        <option value="<?= (int)$s['id']; ?>">
                            <?= htmlspecialchars($s['supplier_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <div style="margin-top:6px; display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" class="btn-grey" onclick="openSupplierAdd()">+ Add New Supplier</button>
                    <button type="button" class="btn-grey" onclick="openSupplierEdit()">✎ Edit Selected Supplier</button>
                </div>
            </div>

            <div>
                <label>Invoice Date</label>
                <input type="date" name="invoice_date" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" required>
            </div>

            <div>
                <label>Total Price Paid</label>
                <input type="number" name="total_amount" min="0" step="0.01" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Account Type</label>
                <select name="account_type" required>
                    <option value="Cash">Cash</option>
                    <option value="30 Days">30 Days</option>
                    <option value="60 Days">60 Days</option>
                    <option value="90 Days">90 Days</option>
                </select>
            </div>
            <div>
                <label>Reference (optional)</label>
                <input type="text" name="reference">
            </div>
        </div>

        <label>Notes (optional)</label>
        <textarea name="notes" rows="3"></textarea>

        <br>
        <button type="submit" class="btn">Save Invoice</button>
        <a href="oem_purchase_list.php" class="btn-grey">Cancel</a>
    </form>
</div>

<!-- ADD SUPPLIER MODAL -->
<div id="supplierAddBackdrop" class="modal-backdrop">
    <div class="modal">
        <h2 style="color:#ff3333;">Add New Supplier</h2>

        <label>Supplier Name</label>
        <input type="text" id="add_supplier_name">

        <label>Contact Person</label>
        <input type="text" id="add_contact_person">

        <label>Phone</label>
        <input type="text" id="add_phone">

        <label>Email</label>
        <input type="text" id="add_email">

        <label>Address</label>
        <textarea id="add_address" rows="2"></textarea>

        <label>VAT Number</label>
        <input type="text" id="add_vat_number">

        <label>Company Reg</label>
        <input type="text" id="add_company_reg">

        <label>ID Document</label>
        <input type="text" id="add_id_document">

        <label>Proof of Address</label>
        <input type="text" id="add_proof_address">

        <label>Notes</label>
        <textarea id="add_notes" rows="2"></textarea>

        <div style="margin-top:15px; text-align:right;">
            <button type="button" class="btn" onclick="saveSupplierAdd()">Save Supplier</button>
            <button type="button" class="btn-grey" onclick="closeSupplierAdd()">Cancel</button>
        </div>
    </div>
</div>

<!-- EDIT SUPPLIER MODAL -->
<div id="supplierEditBackdrop" class="modal-backdrop">
    <div class="modal" style="border-color:#ffcc33;">
        <h2 style="color:#ffcc33;">Edit Supplier</h2>

        <input type="hidden" id="edit_id">

        <label>Supplier Name</label>
        <input type="text" id="edit_supplier_name">

        <label>Contact Person</label>
        <input type="text" id="edit_contact_person">

        <label>Phone</label>
        <input type="text" id="edit_phone">

        <label>Email</label>
        <input type="text" id="edit_email">

        <label>Address</label>
        <textarea id="edit_address" rows="2"></textarea>

        <label>VAT Number</label>
        <input type="text" id="edit_vat_number">

        <label>Company Reg</label>
        <input type="text" id="edit_company_reg">

        <label>ID Document</label>
        <input type="text" id="edit_id_document">

        <label>Proof of Address</label>
        <input type="text" id="edit_proof_address">

        <label>Notes</label>
        <textarea id="edit_notes" rows="2"></textarea>

        <div style="margin-top:15px; text-align:right;">
            <button type="button" class="btn" onclick="saveSupplierEdit()">Save Changes</button>
            <button type="button" class="btn-grey" onclick="closeSupplierEdit()">Cancel</button>
        </div>
    </div>
</div>

<script>
function safeJsonParse(text) {
    try {
        return JSON.parse(text);
    } catch (e) {
        console.error("JSON parse error. Raw response:", text);
        alert("Server returned invalid JSON. Check console for details.");
        return null;
    }
}

/* ---------- ADD SUPPLIER ---------- */
function openSupplierAdd() {
    document.getElementById('supplierAddBackdrop').style.display = 'flex';
}
function closeSupplierAdd() {
    document.getElementById('supplierAddBackdrop').style.display = 'none';
}

function saveSupplierAdd() {
    const payload = {
        supplier_name:  document.getElementById('add_supplier_name').value,
        contact_person: document.getElementById('add_contact_person').value,
        phone:          document.getElementById('add_phone').value,
        email:          document.getElementById('add_email').value,
        address:        document.getElementById('add_address').value,
        vat_number:     document.getElementById('add_vat_number').value,
        company_reg:    document.getElementById('add_company_reg').value,
        id_document:    document.getElementById('add_id_document').value,
        proof_address:  document.getElementById('add_proof_address').value,
        notes:          document.getElementById('add_notes').value
    };

    if (!payload.supplier_name.trim()) {
        alert("Supplier name is required.");
        return;
    }

    fetch('ajax/supplier/ajax_add_supplier.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.text())
    .then(text => {
        const data = safeJsonParse(text);
        if (!data) return;

        if (!data.success) {
            alert("Error adding supplier: " + data.message);
            return;
        }

        const dropdown = document.getElementById('supplier_id');
        const opt = document.createElement('option');
        opt.value = data.id;
        opt.text  = payload.supplier_name;
        dropdown.appendChild(opt);
        dropdown.value = data.id;

        closeSupplierAdd();
        alert("Supplier added successfully.");
    });
}

/* ---------- EDIT SUPPLIER ---------- */
function openSupplierEdit() {
    const dropdown = document.getElementById('supplier_id');
    if (!dropdown.value) {
        alert("Please select a supplier first.");
        return;
    }
    const id = dropdown.value;

    fetch('ajax/supplier/ajax_get_supplier.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.text())
    .then(text => {
        const result = safeJsonParse(text);
        if (!result) return;

        if (!result.success) {
            alert("Error loading supplier: " + result.message);
            return;
        }

        const s = result.data;

        document.getElementById('edit_id').value             = s.id;
        document.getElementById('edit_supplier_name').value  = s.supplier_name || '';
        document.getElementById('edit_contact_person').value = s.contact_person || '';
        document.getElementById('edit_phone').value          = s.phone || '';
        document.getElementById('edit_email').value          = s.email || '';
        document.getElementById('edit_address').value        = s.address || '';
        document.getElementById('edit_vat_number').value     = s.vat_number || '';
        document.getElementById('edit_company_reg').value    = s.company_reg || '';
        document.getElementById('edit_id_document').value    = s.id_document || '';
        document.getElementById('edit_proof_address').value  = s.proof_address || '';
        document.getElementById('edit_notes').value          = s.notes || '';

        document.getElementById('supplierEditBackdrop').style.display = 'flex';
    });
}
function closeSupplierEdit() {
    document.getElementById('supplierEditBackdrop').style.display = 'none';
}

function saveSupplierEdit() {
    const payload = {
        id:             document.getElementById('edit_id').value,
        supplier_name:  document.getElementById('edit_supplier_name').value,
        contact_person: document.getElementById('edit_contact_person').value,
        phone:          document.getElementById('edit_phone').value,
        email:          document.getElementById('edit_email').value,
        address:        document.getElementById('edit_address').value,
        vat_number:     document.getElementById('edit_vat_number').value,
        company_reg:    document.getElementById('edit_company_reg').value,
        id_document:    document.getElementById('edit_id_document').value,
        proof_address:  document.getElementById('edit_proof_address').value,
        notes:          document.getElementById('edit_notes').value
    };

    if (!payload.id) {
        alert("Missing supplier ID.");
        return;
    }
    if (!payload.supplier_name.trim()) {
        alert("Supplier name is required.");
        return;
    }

    fetch('ajax/supplier/ajax_update_supplier.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.text())
    .then(text => {
        const data = safeJsonParse(text);
        if (!data) return;

        if (!data.success) {
            alert("Error updating supplier: " + data.message);
            return;
        }

        // Update dropdown text
        const dropdown = document.getElementById('supplier_id');
        const opt = dropdown.querySelector('option[value="' + payload.id + '"]');
        if (opt) opt.text = payload.supplier_name;

        closeSupplierEdit();
        alert("Supplier updated successfully.");
    });
}
</script>

<?php include "includes/footer.php"; ?>
