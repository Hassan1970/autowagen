<?php
require_once __DIR__ . '/config/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= UPLOAD DIR ================= */
$uploadDir = __DIR__ . '/uploads/third_party_parts/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* ================= HELPER ================= */
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/* ================= STICKY HEADER ================= */
$sticky_supplier = (int) ($_GET['third_supplier_id'] ?? 0);
$sticky_invoice  = trim($_GET['invoice_number'] ?? '');
$sticky_date     = trim($_GET['invoice_date'] ?? date('Y-m-d'));

/* ================= TERMINATE ================= */
if (isset($_POST['terminate_invoice'])) {
    header('Location: third_party_list.php');
    exit;
}

/* ================= SAVE PART ================= */
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_part'])) {

    $third_supplier_id = (int) ($_POST['third_supplier_id'] ?? 0);
    $invoice_number    = trim($_POST['invoice_number'] ?? '');
    $invoice_date      = trim($_POST['invoice_date'] ?? '');

    /* 🔴 FIX: vehicle_id must be NULL or valid ID */
    $vehicle_id = (!empty($_POST['vehicle_id']) && (int)$_POST['vehicle_id'] > 0)
        ? (int)$_POST['vehicle_id']
        : null;

    $category_id    = (int) ($_POST['category_id'] ?? 0);
    $subcategory_id = (int) ($_POST['subcategory_id'] ?? 0);
    $type_id        = (int) ($_POST['type_id'] ?? 0);
    $component_id   = (int) ($_POST['component_id'] ?? 0);

    $description   = trim($_POST['description'] ?? '');
    $cost_incl     = (float) ($_POST['cost_incl'] ?? 0);
    $selling_price = (float) ($_POST['selling_price'] ?? 0);
    $side          = trim($_POST['side'] ?? '');
    $notes         = trim($_POST['notes'] ?? '');

    if ($third_supplier_id <= 0) $errors[] = "Supplier is required";
    if ($invoice_number === '')  $errors[] = "Invoice number is required";
    if ($description === '')     $errors[] = "Description is required";

    /* ================= PHOTO ================= */
    $photoName = null;
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $photoName = 'tp_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName);
        }
    }

    if (!$errors) {

        $stmt = $conn->prepare("
            INSERT INTO third_party_parts
            (vehicle_id, third_supplier_id, category_id, subcategory_id, type_id, component_id,
             description, invoice_number, invoice_date,
             cost_incl, selling_price, side, notes, photo)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "iiiiiiissddsss",
            $vehicle_id,
            $third_supplier_id,
            $category_id,
            $subcategory_id,
            $type_id,
            $component_id,
            $description,
            $invoice_number,
            $invoice_date,
            $cost_incl,
            $selling_price,
            $side,
            $notes,
            $photoName
        );

        $stmt->execute();
        $stmt->close();

        header(
            "Location: third_party_entry.php?third_supplier_id=$third_supplier_id" .
            "&invoice_number=" . urlencode($invoice_number) .
            "&invoice_date=$invoice_date"
        );
        exit;
    }
}

/* ================= DATA ================= */
$thirdSuppliers = $conn->query("SELECT id, supplier_name FROM third_party_suppliers ORDER BY supplier_name");
$vehicles       = $conn->query("SELECT id, stock_code, make, model, year FROM vehicles ORDER BY id DESC");
$categories     = $conn->query("SELECT id, name FROM categories ORDER BY name");

include __DIR__ . '/includes/header.php';
?>

<style>
.wrap{width:90%;max-width:1200px;margin:30px auto;background:#111;border:2px solid red;border-radius:10px;padding:20px;}
h1{color:#ff3333}
input,select,textarea{width:100%;padding:6px;background:#000;color:#fff;border:1px solid #444;border-radius:4px;margin-bottom:10px}
.row{display:flex;flex-wrap:wrap;gap:12px}
.col-3{flex:0 0 23%}
.col-4{flex:0 0 31%}
.col-6{flex:0 0 48%}
.col-12{flex:0 0 100%}
button,.btn{background:#c00000;color:#fff;padding:7px 14px;border:none;border-radius:4px;cursor:pointer}
.btn-secondary{background:#333}
</style>

<script>
async function loadSubcategories(id){
    subcategory_id.innerHTML=''; type_id.innerHTML=''; component_id.innerHTML='';
    if(!id) return;
    let r=await fetch('ajax/epc_get_subcategories.php?category_id='+id);
    let d=await r.json();
    subcategory_id.innerHTML='<option value="">-- Select Subcategory --</option>';
    d.forEach(x=>subcategory_id.innerHTML+=`<option value="${x.id}">${x.name}</option>`);
}
async function loadTypes(id){
    type_id.innerHTML=''; component_id.innerHTML='';
    if(!id) return;
    let r=await fetch('ajax/epc_get_types.php?subcategory_id='+id);
    let d=await r.json();
    type_id.innerHTML='<option value="">-- Select Type --</option>';
    d.forEach(x=>type_id.innerHTML+=`<option value="${x.id}">${x.name}</option>`);
}
async function loadComponents(id){
    component_id.innerHTML='';
    if(!id) return;
    let r=await fetch('ajax/epc_get_components.php?type_id='+id);
    let d=await r.json();
    component_id.innerHTML='<option value="">-- Select Component --</option>';
    d.forEach(x=>component_id.innerHTML+=`<option value="${x.id}">${x.name}</option>`);
}
</script>

<div class="wrap">
<h1>3rd Party Invoice Entry</h1>

<?php if ($errors): ?>
<div style="background:#400;padding:10px">
<?php foreach($errors as $e) echo h($e)."<br>"; ?>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<div class="row">
<div class="col-4">
<label>Supplier</label>
<select name="third_supplier_id">
<option value="">-- Select --</option>
<?php while($s=$thirdSuppliers->fetch_assoc()): ?>
<option value="<?=$s['id']?>" <?=($sticky_supplier==$s['id']?'selected':'')?>>
<?=h($s['supplier_name'])?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="col-4">
<label>Invoice Number</label>
<input type="text" name="invoice_number" value="<?=h($sticky_invoice)?>">
</div>

<div class="col-4">
<label>Invoice Date</label>
<input type="date" name="invoice_date" value="<?=h($sticky_date)?>">
</div>
</div>

<hr>

<div class="row">
<div class="col-4">
<label>Vehicle (optional)</label>
<select name="vehicle_id">
<option value="">-- None --</option>
<?php while($v=$vehicles->fetch_assoc()): ?>
<option value="<?=$v['id']?>"><?=h($v['stock_code'].' '.$v['make'].' '.$v['model'])?></option>
<?php endwhile; ?>
</select>
</div>

<div class="col-3">
<label>Category</label>
<select name="category_id" onchange="loadSubcategories(this.value)">
<option value="">-- Select --</option>
<?php while($c=$categories->fetch_assoc()): ?>
<option value="<?=$c['id']?>"><?=h($c['name'])?></option>
<?php endwhile; ?>
</select>
</div>

<div class="col-3">
<label>Subcategory</label>
<select name="subcategory_id" id="subcategory_id" onchange="loadTypes(this.value)"></select>
</div>

<div class="col-3">
<label>Type</label>
<select name="type_id" id="type_id" onchange="loadComponents(this.value)"></select>
</div>

<div class="col-3">
<label>Component</label>
<select name="component_id" id="component_id"></select>
</div>

<div class="col-6">
<label>Description</label>
<input type="text" name="description">
</div>

<div class="col-3">
<label>Cost Incl</label>
<input type="number" step="0.01" name="cost_incl">
</div>

<div class="col-3">
<label>Selling Price</label>
<input type="number" step="0.01" name="selling_price">
</div>

<div class="col-3">
<label>Side</label>
<select name="side">
<option value="">N/A</option>
<option>Left</option>
<option>Right</option>
<option>Front</option>
<option>Rear</option>
</select>
</div>

<div class="col-12">
<label>Notes</label>
<textarea name="notes"></textarea>
</div>

<div class="col-12">
<label>Photo</label>
<input type="file" name="photo">
</div>
</div>

<button type="submit" name="save_part">Save Part</button>
<button type="submit" name="terminate_invoice" class="btn-secondary">All Parts Entered</button>

</form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
