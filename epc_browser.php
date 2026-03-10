<?php
require_once __DIR__ . '/config/config.php';

$cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>EPC Browser</title>
<style>
body {
    background:#111;
    color:#f7f7f7;
    font-family:Arial, sans-serif;
}
.wrap {
    width:90%;
    margin:20px auto;
    background:#181818;
    border:1px solid #333;
    border-radius:8px;
    padding:20px;
}
h1 {
    color:#ff3333;
    text-align:center;
}
label {
    display:block;
    margin-top:10px;
    font-size:14px;
}
select {
    width:100%;
    padding:6px;
    margin-top:4px;
    background:#000;
    color:#fff;
    border:1px solid #444;
    border-radius:4px;
}
.grid {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap:15px;
    margin-bottom:20px;
}
table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
th, td {
    border:1px solid #333;
    padding:6px 8px;
    font-size:13px;
}
th {
    background:#222;
}
tr:nth-child(even){
    background:#1b1b1b;
}
.badge {
    display:inline-block;
    padding:2px 6px;
    border-radius:4px;
    background:#222;
    font-size:11px;
    color:#ccc;
}
</style>
</head>
<body>
<div class="wrap">
    <h1>EPC Browser</h1>

    <div class="grid">
        <div>
            <label>Category</label>
            <select id="b_cat">
                <option value="">-- All Categories --</option>
                <?php while ($c = $cats->fetch_assoc()): ?>
                    <option value="<?php echo (int)$c['id']; ?>">
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label>Subcategory</label>
            <select id="b_sub">
                <option value="">-- All Subcategories --</option>
            </select>
        </div>
        <div>
            <label>Type</label>
            <select id="b_type">
                <option value="">-- All Types --</option>
            </select>
        </div>
    </div>

    <h3>Components</h3>
    <table id="b_table">
        <thead>
            <tr>
                <th>Component</th>
                <th>Type</th>
                <th>Subcategory</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="4">Select filters above to view EPC components.</td></tr>
        </tbody>
    </table>
</div>

<script>
const bCat  = document.getElementById('b_cat');
const bSub  = document.getElementById('b_sub');
const bType = document.getElementById('b_type');
const bTbl  = document.getElementById('b_table').querySelector('tbody');

function clearSelect(sel, placeholder) {
    sel.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = placeholder;
    sel.appendChild(opt);
}

function loadSubcategories(catId) {
    clearSelect(bSub, '-- All Subcategories --');
    clearSelect(bType, '-- All Types --');
    if (!catId) return;
    fetch('ajax_epc_get_subcategories.php?category_id=' + encodeURIComponent(catId))
        .then(r => r.json())
        .then(rows => {
            rows.forEach(row => {
                const opt = document.createElement('option');
                opt.value = row.id;
                opt.textContent = row.name;
                bSub.appendChild(opt);
            });
        });
}

function loadTypes(subId) {
    clearSelect(bType, '-- All Types --');
    if (!subId) return;
    fetch('ajax_epc_get_types.php?subcategory_id=' + encodeURIComponent(subId))
        .then(r => r.json())
        .then(rows => {
            rows.forEach(row => {
                const opt = document.createElement('option');
                opt.value = row.id;
                opt.textContent = row.name;
                bType.appendChild(opt);
            });
        });
}

function loadComponents() {
    const typeId = bType.value;
    const subId  = bSub.value;
    const catId  = bCat.value;

    if (!typeId) {
        bTbl.innerHTML = '<tr><td colspan="4">Select at least a Type to list components.</td></tr>';
        return;
    }

    fetch('ajax_epc_get_components.php?type_id=' + encodeURIComponent(typeId))
        .then(r => r.json())
        .then(rows => {
            if (!rows.length) {
                bTbl.innerHTML = '<tr><td colspan="4">No components found.</td></tr>';
                return;
            }
            bTbl.innerHTML = '';
            rows.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.name}</td>
                    <td><span class="badge">${bType.options[bType.selectedIndex].text}</span></td>
                    <td>${bSub.value ? bSub.options[bSub.selectedIndex].text : ''}</td>
                    <td>${bCat.value ? bCat.options[bCat.selectedIndex].text : ''}</td>
                `;
                bTbl.appendChild(tr);
            });
        });
}

bCat.addEventListener('change', function() {
    loadSubcategories(this.value);
    bTbl.innerHTML = '<tr><td colspan="4">Select Type to view components.</td></tr>';
});
bSub.addEventListener('change', function() {
    loadTypes(this.value);
    bTbl.innerHTML = '<tr><td colspan="4">Select Type to view components.</td></tr>';
});
bType.addEventListener('change', loadComponents);
</script>
</body>
</html>
