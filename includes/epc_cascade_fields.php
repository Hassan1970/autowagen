<?php
// includes/epc_cascade_fields.php
if (!isset($conn)) {
    require_once __DIR__ . '/../config/config.php';
}

// Load categories for first dropdown
$epcCats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<div class="epc-block">
    <h3>EPC Classification</h3>

    <label>Category</label>
    <select name="category_id" id="epc_category" required>
        <option value="">-- Select Category --</option>
        <?php while ($c = $epcCats->fetch_assoc()): ?>
            <option value="<?php echo (int)$c['id']; ?>">
                <?php echo htmlspecialchars($c['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Subcategory</label>
    <select name="subcategory_id" id="epc_subcategory">
        <option value="">-- Select Subcategory --</option>
    </select>

    <label>Type</label>
    <select name="type_id" id="epc_type">
        <option value="">-- Select Type --</option>
    </select>

    <label>Component</label>
    <select name="component_id" id="epc_component">
        <option value="">-- Select Component --</option>
    </select>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const catSel  = document.getElementById('epc_category');
    const subSel  = document.getElementById('epc_subcategory');
    const typeSel = document.getElementById('epc_type');
    const compSel = document.getElementById('epc_component');

    function clearSelect(sel, placeholder) {
        sel.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        sel.appendChild(opt);
    }

    catSel.addEventListener('change', function () {
        const catId = this.value;
        clearSelect(subSel, '-- Select Subcategory --');
        clearSelect(typeSel, '-- Select Type --');
        clearSelect(compSel, '-- Select Component --');

        if (!catId) return;

        fetch('ajax_epc_get_subcategories.php?category_id=' + encodeURIComponent(catId))
            .then(r => r.json())
            .then(rows => {
                rows.forEach(row => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.name;
                    subSel.appendChild(opt);
                });
            })
            .catch(console.error);
    });

    subSel.addEventListener('change', function () {
        const subId = this.value;
        clearSelect(typeSel, '-- Select Type --');
        clearSelect(compSel, '-- Select Component --');

        if (!subId) return;

        fetch('ajax_epc_get_types.php?subcategory_id=' + encodeURIComponent(subId))
            .then(r => r.json())
            .then(rows => {
                rows.forEach(row => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.name;
                    typeSel.appendChild(opt);
                });
            })
            .catch(console.error);
    });

    typeSel.addEventListener('change', function () {
        const typeId = this.value;
        clearSelect(compSel, '-- Select Component --');

        if (!typeId) return;

        fetch('ajax_epc_get_components.php?type_id=' + encodeURIComponent(typeId))
            .then(r => r.json())
            .then(rows => {
                rows.forEach(row => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.name;
                    compSel.appendChild(opt);
                });
            })
            .catch(console.error);
    });
});
</script>
