<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

// Load categories
$catSql = "SELECT id, name FROM categories ORDER BY name ASC";
$catRes = $conn->query($catSql);

// Load OEM invoices
$invSql = "
    SELECT i.id, i.invoice_number, i.invoice_date, s.supplier_name
    FROM supplier_oem_invoices i
    LEFT JOIN suppliers s ON s.id = i.supplier_id
    ORDER BY i.id DESC
";
$invRes = $conn->query($invSql);

// Load suppliers
$suppSql = "SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC";
$suppRes = $conn->query($suppSql);
?>
<style>
.page-wrap {
    width: 95%;
    margin: 25px auto;
    background: #111;
    border: 2px solid #b00000;
    border-radius: 8px;
    padding: 20px 25px 30px;
    color: #fff;
}
.page-title {
    text-align: center;
    color: #ff3333;
    margin-bottom: 15px;
}
.search-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(240px,1fr));
    gap: 10px 20px;
    margin-bottom: 10px;
}
.field-group label {
    display: block;
    font-size: 12px;
    font-weight: bold;
    color: #ff3333;
    margin-bottom: 3px;
}
.field-group input,
.field-group select {
    background: #222;
    border: 1px solid #444;
    color: #fff;
    border-radius: 4px;
    padding: 6px 8px;
    font-size: 13px;
    width: 100%;
}
.field-inline {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    margin-top: 4px;
}
.btn {
    display: inline-block;
    background: #b00000;
    color: #fff;
    border-radius: 4px;
    border: none;
    padding: 6px 14px;
    font-size: 13px;
    text-decoration: none;
    cursor: pointer;
}
.btn:hover { background: #ff3333; }
.btn-grey {
    background: #444;
    color: #eee;
}
.btn-grey:hover { background: #666; }

.results-wrap {
    margin-top: 15px;
    overflow-x: auto;
}
.results-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.results-table th,
.results-table td {
    border: 1px solid #333;
    padding: 6px 8px;
    white-space: nowrap;
}
.results-table th {
    background: #b00000;
    color: #fff;
    text-align: left;
}
.results-table tr:nth-child(even) {
    background: #181818;
}
.results-table tr:nth-child(odd) {
    background: #101010;
}
.sub-path {
    font-size: 11px;
    color: #ccc;
}
.note-small {
    font-size: 11px;
    color: #aaa;
}
#searchStatus {
    margin-top: 6px;
    font-size: 12px;
    color: #ccc;
}
</style>

<div class="page-wrap">
    <h2 class="page-title">OEM Parts Search</h2>

    <div style="margin-bottom:10px;">
        <a href="oem_parts_list.php" class="btn-grey">← OEM Parts List</a>
        <a href="oem_purchase_list.php" class="btn-grey">OEM Invoices</a>
        <a href="oem_parts_add.php" class="btn">+ Add New OEM Part</a>
    </div>

    <!-- SEARCH / FILTER FORM (client-side, AJAX) -->
    <div class="search-grid">
        <div class="field-group">
            <label>Search Text</label>
            <input type="text" id="q" placeholder="OEM Number or Part Name">
        </div>

        <div class="field-group">
            <label>Search Field</label>
            <select id="search_field">
                <option value="both">OEM # + Part Name</option>
                <option value="oem">OEM Number only</option>
                <option value="name">Part Name only</option>
            </select>
        </div>

        <div class="field-group">
            <label>Category</label>
            <select id="category_id">
                <option value="">All Categories</option>
                <?php while ($c = $catRes->fetch_assoc()): ?>
                    <option value="<?php echo (int)$c['id']; ?>">
                        <?php echo h($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="field-group">
            <label>Subcategory</label>
            <select id="subcategory_id">
                <option value="">All Subcategories</option>
            </select>
        </div>

        <div class="field-group">
            <label>Type</label>
            <select id="type_id">
                <option value="">All Types</option>
            </select>
        </div>

        <div class="field-group">
            <label>Component</label>
            <select id="component_id">
                <option value="">All Components</option>
            </select>
        </div>

        <div class="field-group">
            <label>OEM Invoice</label>
            <select id="invoice_id">
                <option value="">All Invoices</option>
                <?php while ($inv = $invRes->fetch_assoc()):
                    $label = sprintf(
                        "#%d - %s (%s) - %s",
                        $inv['id'],
                        $inv['invoice_number'],
                        $inv['invoice_date'],
                        $inv['supplier_name']
                    );
                ?>
                    <option value="<?php echo (int)$inv['id']; ?>">
                        <?php echo h($label); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="field-group">
            <label>Supplier</label>
            <select id="supplier_id">
                <option value="">All Suppliers</option>
                <?php while ($s = $suppRes->fetch_assoc()): ?>
                    <option value="<?php echo (int)$s['id']; ?>">
                        <?php echo h($s['supplier_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="field-group">
            <label>Cost Price Range</label>
            <div class="field-inline">
                <input type="number" id="cost_min" placeholder="Min" step="0.01" style="width:50%;">
                <input type="number" id="cost_max" placeholder="Max" step="0.01" style="width:50%;">
            </div>
        </div>

        <div class="field-group">
            <label>Stock / Options</label>
            <div class="field-inline">
                <input type="checkbox" id="in_stock_only">
                <span>In Stock Only</span>
            </div>
            <div class="note-small">
                Limits search to parts where stock_qty &gt; 0.
            </div>
        </div>
    </div>

    <div style="margin-top:5px; margin-bottom:10px;">
        <button class="btn" onclick="runSearch()">Search</button>
        <button class="btn-grey" onclick="resetSearch()">Reset</button>
        <span id="searchStatus"></span>
    </div>

    <div class="results-wrap">
        <table class="results-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>OEM #</th>
                    <th>Part Name</th>
                    <th>EPC Path</th>
                    <th>Stock</th>
                    <th>Cost</th>
                    <th>Selling</th>
                    <th>Invoice</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="resultsBody">
                <tr><td colspan="10">Enter criteria and click Search.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Simple helper to call EPC cascading AJAX endpoints
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
        subcat.innerHTML = "<option value=''>All Subcategories</option>";
        type.innerHTML   = "<option value=''>All Types</option>";
        comp.innerHTML   = "<option value=''>All Components</option>";

        if (!cat.value) return;
        fetchAndFill('ajax/epc_get_subcategories.php', cat, subcat, 'All Subcategories');
    });

    subcat.addEventListener('change', function () {
        type.innerHTML = "<option value=''>All Types</option>";
        comp.innerHTML = "<option value=''>All Components</option>";
        if (!subcat.value) return;
        fetchAndFill('ajax/epc_get_types.php', subcat, type, 'All Types');
    });

    type.addEventListener('change', function () {
        comp.innerHTML = "<option value=''>All Components</option>";
        if (!type.value) return;
        fetchAndFill('ajax/epc_get_components.php', type, comp, 'All Components');
    });

    // Enter triggers search
    document.getElementById('q').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            runSearch();
        }
    });
});

function runSearch() {
    const payload = {
        q:              document.getElementById('q').value,
        search_field:   document.getElementById('search_field').value,
        category_id:    document.getElementById('category_id').value,
        subcategory_id: document.getElementById('subcategory_id').value,
        type_id:        document.getElementById('type_id').value,
        component_id:   document.getElementById('component_id').value,
        invoice_id:     document.getElementById('invoice_id').value,
        supplier_id:    document.getElementById('supplier_id').value,
        cost_min:       document.getElementById('cost_min').value,
        cost_max:       document.getElementById('cost_max').value,
        in_stock_only:  document.getElementById('in_stock_only').checked ? 1 : 0
    };

    document.getElementById('searchStatus').textContent = "Searching...";
    const tbody = document.getElementById('resultsBody');
    tbody.innerHTML = "<tr><td colspan='10'>Searching...</td></tr>";

    fetch("ajax/oem/ajax_search_oem_parts.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            document.getElementById('searchStatus').textContent = data.message || "No results.";
            tbody.innerHTML = "<tr><td colspan='10'>No matching parts found.</td></tr>";
            return;
        }

        const parts = data.parts || [];
        document.getElementById('searchStatus').textContent =
            "Found " + parts.length + " part(s).";

        if (parts.length === 0) {
            tbody.innerHTML = "<tr><td colspan='10'>No matching parts found.</td></tr>";
            return;
        }

        tbody.innerHTML = "";
        parts.forEach(p => {
            const epcBits = [];
            if (p.category_name)    epcBits.push(p.category_name);
            if (p.subcategory_name) epcBits.push(p.subcategory_name);
            if (p.type_name)        epcBits.push(p.type_name);
            if (p.component_name)   epcBits.push(p.component_name);

            const epcPath = epcBits.join(" → ");

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.id}</td>
                <td>${p.oem_number || ''}</td>
                <td>${p.part_name || ''}</td>
                <td><span class="sub-path">${epcPath}</span></td>
                <td>${p.stock_qty}</td>
                <td>${parseFloat(p.cost_price).toFixed(2)}</td>
                <td>${parseFloat(p.selling_price).toFixed(2)}</td>
                <td>
                    ${p.invoice_id
                        ? `<a href="oem_purchase_view.php?id=${p.invoice_id}" class="btn-grey btn-sm">${p.invoice_number}</a>`
                        : `<span class="sub-path">No invoice</span>`}
                </td>
                <td>${p.supplier_name || ''}</td>
                <td>
                    <a href="oem_parts_edit.php?id=${p.id}" class="btn btn-sm">Edit</a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    })
    .catch(err => {
        console.error(err);
        document.getElementById('searchStatus').textContent = "Error running search.";
        tbody.innerHTML = "<tr><td colspan='10'>Error running search.</td></tr>";
    });
}

function resetSearch() {
    document.getElementById('q').value = "";
    document.getElementById('search_field').value = "both";
    document.getElementById('category_id').value = "";
    document.getElementById('subcategory_id').innerHTML = "<option value=''>All Subcategories</option>";
    document.getElementById('type_id').innerHTML = "<option value=''>All Types</option>";
    document.getElementById('component_id').innerHTML = "<option value=''>All Components</option>";
    document.getElementById('invoice_id').value = "";
    document.getElementById('supplier_id').value = "";
    document.getElementById('cost_min').value = "";
    document.getElementById('cost_max').value = "";
    document.getElementById('in_stock_only').checked = false;
    document.getElementById('resultsBody').innerHTML =
        "<tr><td colspan='10'>Enter criteria and click Search.</td></tr>";
    document.getElementById('searchStatus').textContent = "";
}
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
