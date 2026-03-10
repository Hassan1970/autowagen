<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("Invalid part ID.");

$sql = "
SELECT p.*, 
       c.name  AS category_name,
       sb.name AS subcategory_name,
       t.name  AS type_name,
       cp.name AS component_name
FROM replacement_parts p
LEFT JOIN categories  c  ON p.category_id    = c.id
LEFT JOIN subcategories sb ON p.subcategory_id = sb.id
LEFT JOIN types       t  ON p.type_id        = t.id
LEFT JOIN components  cp ON p.component_id   = cp.id
WHERE p.id = ?
LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res  = $stmt->get_result();
$part = $res->fetch_assoc();

if (!$part) {
    die("Part not found.");
}

$cats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<style>
body { background:#000; color:#fff; font-family:Arial; }
.wrap {
    width:80%;
    margin:20px auto;
    background:#111;
    border:2px solid #b00000;
    padding:20px;
    border-radius:8px;
}
input, select, textarea {
    width:100%;
    background:#222; color:#fff;
    border:1px solid #444;
    padding:6px;
    margin:4px 0;
}
label { margin-top:10px; display:block; }
.btn {
    padding:8px 15px;
    background:#b00000;
    color:#fff;
    border:none;
    border-radius:4px;
    cursor:pointer;
}
.btn:hover { background:#ff3333; }
</style>

<div class="wrap">
<h2>Edit Replacement Part</h2>

<form method="post" action="replacement_part_update.php">
<input type="hidden" name="id" value="<?php echo $id; ?>">

<label>Part Number</label>
<input type="text" name="part_number" value="<?php echo h($part['part_number']); ?>">

<label>Part Name</label>
<input type="text" name="part_name" value="<?php echo h($part['part_name']); ?>">

<label>Category</label>
<select name="category_id" id="category">
    <option value="">-- Select Category --</option>
    <?php while($c = $cats->fetch_assoc()): ?>
        <option value="<?php echo (int)$c['id']; ?>"
            <?php if ((int)$part['category_id'] === (int)$c['id']) echo 'selected'; ?>>
            <?php echo h($c['name']); ?>
        </option>
    <?php endwhile; ?>
</select>

<label>Subcategory</label>
<select name="subcategory_id" id="subcategory" data-selected="<?php echo (int)$part['subcategory_id']; ?>">
    <option value="">-- Select Subcategory --</option>
</select>

<label>Type</label>
<select name="type_id" id="type" data-selected="<?php echo (int)$part['type_id']; ?>">
    <option value="">-- Select Type --</option>
</select>

<label>Component</label>
<select name="component_id" id="component" data-selected="<?php echo (int)$part['component_id']; ?>">
    <option value="">-- Select Component --</option>
</select>

<label>Cost Price (BLACKWHITE)</label>
<input type="text" name="cost_code" value="<?php echo h($part['cost_code']); ?>">

<label>Selling Price</label>
<input type="number" step="0.01" name="selling_price" value="<?php echo h($part['selling_price']); ?>">

<label>Stock Qty</label>
<input type="number" name="stock_qty" value="<?php echo h($part['stock_qty']); ?>">

<label>Notes</label>
<textarea name="notes" rows="3"><?php echo h($part['notes']); ?></textarea>

<br><br>
<button class="btn" type="submit">Save Changes</button>

</form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let category   = document.getElementById("category");
    let subcat     = document.getElementById("subcategory");
    let typeField  = document.getElementById("type");
    let compField  = document.getElementById("component");

    let savedSub   = subcat.getAttribute("data-selected");
    let savedType  = typeField.getAttribute("data-selected");
    let savedComp  = compField.getAttribute("data-selected");

    if (category.value !== "") {
        loadSubcategories(category.value, true);
    }

    category.addEventListener("change", function () {
        let catId = this.value;
        resetDropdowns("category");
        if (catId) loadSubcategories(catId, false);
    });

    subcat.addEventListener("change", function () {
        let subId = this.value;
        resetDropdowns("subcategory");
        if (subId) loadTypes(subId, false);
    });

    typeField.addEventListener("change", function () {
        let typeId = this.value;
        resetDropdowns("type");
        if (typeId) loadComponents(typeId, false);
    });

    function resetDropdowns(level) {
        if (level === "category") {
            subcat.innerHTML    = '<option value="">-- Select Subcategory --</option>';
            typeField.innerHTML = '<option value="">-- Select Type --</option>';
            compField.innerHTML = '<option value="">-- Select Component --</option>';
        } else if (level === "subcategory") {
            typeField.innerHTML = '<option value="">-- Select Type --</option>';
            compField.innerHTML = '<option value="">-- Select Component --</option>';
        } else if (level === "type") {
            compField.innerHTML = '<option value="">-- Select Component --</option>';
        }
    }

    function loadSubcategories(catId, firstLoad) {
        fetch("ajax/epc_get_subcategories.php?category_id=" + catId)
        .then(r => r.json())
        .then(data => {
            let opts = '<option value="">-- Select Subcategory --</option>';
            data.forEach(row => {
                opts += `<option value="${row.id}">${row.name}</option>`;
            });
            subcat.innerHTML = opts;

            if (firstLoad && savedSub) {
                subcat.value = savedSub;
                if (savedSub) loadTypes(savedSub, true);
            }
        })
        .catch(console.error);
    }

    function loadTypes(subId, firstLoad) {
        fetch("ajax/epc_get_types.php?subcategory_id=" + subId)
        .then(r => r.json())
        .then(data => {
            let opts = '<option value="">-- Select Type --</option>';
            data.forEach(row => {
                opts += `<option value="${row.id}">${row.name}</option>`;
            });
            typeField.innerHTML = opts;

            if (firstLoad && savedType) {
                typeField.value = savedType;
                if (savedType) loadComponents(savedType, true);
            }
        })
        .catch(console.error);
    }

    function loadComponents(typeId, firstLoad) {
        fetch("ajax/epc_get_components.php?type_id=" + typeId)
        .then(r => r.json())
        .then(data => {
            let opts = '<option value="">-- Select Component --</option>';
            data.forEach(row => {
                opts += `<option value="${row.id}">${row.name}</option>`;
            });
            compField.innerHTML = opts;

            if (firstLoad && savedComp) {
                compField.value = savedComp;
            }
        })
        .catch(console.error);
    }
});
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
