<?php
require_once "config/config.php";
include "includes/header.php";

// Fetch suppliers for filter dropdown
$supplierQuery = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
?>
<style>
.page {
    width: 95%;
    margin: 30px auto;
    background: #111;
    padding: 25px;
    border: 2px solid #b00000;
    color: white;
    border-radius: 8px;
}
h2 {
    color: #ff3333;
    text-align: center;
    margin-bottom: 20px;
}
.filter-row {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}
.filter-row select, .filter-row input {
    background: #222;
    color: white;
    border: 1px solid #444;
    padding: 8px;
    border-radius: 5px;
}
.table-container {
    margin-top: 15px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table thead {
    background: #b00000;
}
table thead th {
    padding: 10px;
    text-align: left;
    color: white;
}
table tbody tr {
    border-bottom: 1px solid #333;
}
table tbody td {
    padding: 8px;
}
.btn-small {
    padding: 4px 10px;
    border: none;
    color: white;
    border-radius: 4px;
    cursor: pointer;
}
.btn-view { background: #007bff; }
.btn-edit { background: #ff9900; }
.btn-delete { background: #cc0000; }
.btn-add {
    background: #28a745;
    padding: 6px 12px;
    margin-bottom: 15px;
    display: inline-block;
}
</style>

<div class="page">
    <h2>OEM Supplier Invoices</h2>

    <a href="oem_purchase_add.php" class="btn-add">+ Add New OEM Invoice</a>

    <!-- FILTERS -->
    <div class="filter-row">
        <select id="filter_supplier">
            <option value="">All Suppliers</option>
            <?php while ($s = $supplierQuery->fetch_assoc()) { ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
            <?php } ?>
        </select>

        <input type="date" id="filter_date_from">
        <input type="date" id="filter_date_to">

        <select id="filter_account">
            <option value="">Account Type</option>
            <option value="Cash">Cash</option>
            <option value="30 Days">30 Days</option>
            <option value="60 Days">60 Days</option>
            <option value="90 Days">90 Days</option>
        </select>

        <input type="text" id="search_invoice" placeholder="Search Invoice #">
    </div>

    <!-- TABLE -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Total Amount</th>
                    <th>Account Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="invoiceTable"></tbody>
        </table>
    </div>
</div>

<script>
// Load invoices on page load
document.addEventListener("DOMContentLoaded", loadInvoices);

// Reload when filters change
document.querySelectorAll("#filter_supplier, #filter_date_from, #filter_date_to, #filter_account, #search_invoice")
    .forEach(el => el.addEventListener("change", loadInvoices));

function loadInvoices() {
    let filters = {
        supplier_id: document.getElementById("filter_supplier").value,
        date_from: document.getElementById("filter_date_from").value,
        date_to: document.getElementById("filter_date_to").value,
        account_type: document.getElementById("filter_account").value,
        search_invoice: document.getElementById("search_invoice").value
    };

    fetch("ajax/oem/ajax_load_invoices.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(filters)
    })
    .then(r => r.json())
    .then(data => {
        let tbody = document.getElementById("invoiceTable");
        tbody.innerHTML = "";

        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan='7'>No invoices found</td></tr>`;
            return;
        }

        data.invoices.forEach(inv => {
            tbody.innerHTML += `
                <tr>
                    <td>${inv.id}</td>
                    <td>${inv.invoice_number}</td>
                    <td>${inv.invoice_date}</td>
                    <td>${inv.supplier_name}</td>
                    <td>${inv.total_amount}</td>
                    <td>${inv.account_type}</td>
                    <td>
                        <a href="oem_purchase_view.php?id=${inv.id}" class="btn-small btn-view">View</a>
                        <a href="oem_purchase_edit.php?id=${inv.id}" class="btn-small btn-edit">Edit</a>
                        <button class="btn-small btn-delete" onclick="deleteInvoice(${inv.id})">Delete</button>
                    </td>
                </tr>
            `;
        });
    });
}

function deleteInvoice(id) {
    if (!confirm("Delete this invoice?")) return;

    fetch("ajax/oem/ajax_delete_invoice.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        loadInvoices();
    });
}
</script>

<?php include "includes/footer.php"; ?>
