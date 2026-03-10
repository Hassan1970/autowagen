<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

$invoice_id = intval($_GET['invoice_id'] ?? 0);
if ($invoice_id <= 0) { die("Invalid invoice ID."); }

// Load invoice header
$inv = $conn->query("
    SELECT si.*, s.supplier_name 
    FROM supplier_invoices si
    LEFT JOIN suppliers s ON s.id = si.supplier_id
    WHERE si.id = {$invoice_id}
")->fetch_assoc();

if (!$inv) die("Invoice not found.");


// Load EPC data
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$subcats    = $conn->query("SELECT * FROM subcategories ORDER BY name ASC");
$types      = $conn->query("SELECT * FROM types ORDER BY name ASC");
$components = $conn->query("SELECT * FROM components ORDER BY name ASC");


// Handle form submission
if ($_SERVER['REQUEST_METHOD']==='POST') {

    $part_type     = $_POST['part_type'] ?? '';
    $category_id   = intval($_POST['category_id'] ?? 0);
    $subcategory_id= intval($_POST['subcategory_id'] ?? 0);
    $type_id       = intval($_POST['type_id'] ?? 0);
    $component_id  = intval($_POST['component_id'] ?? 0);

    $part_name     = trim($_POST['part_name'] ?? '');
    $cost_price    = floatval($_POST['cost_price'] ?? 0);
    $encoded_cost  = encode_cost_secure($cost_price);
    $qty           = intval($_POST['qty'] ?? 1);
    $notes         = trim($_POST['notes'] ?? '');

    if ($part_name === '') {
        echo "<p style='color:#f55;'>Part name is required.</p>";
    } else {

        // Insert invoice item
        $stmt = $conn->prepare("
            INSERT INTO supplier_invoice_items
                (invoice_id, part_type, category_id,
                subcategory_id, type_id, component_id,
                part_name, cost_price, encoded_cost, qty, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isiiiisssis",
            $invoice_id, $part_type, $category_id, $subcategory_id,
            $type_id, $component_id, $part_name, $cost_price,
            $encoded_cost, $qty, $notes
        );

        $stmt->execute();
        $stmt->close();

        echo "<p style='color:#6f6;'>Part added!</p>";
    }
}
?>

<div class="page-header">
    <h1>Add Parts to Invoice — <?=h($inv['id'])?></h1>
    <p>Supplier: <b><?=h($inv['supplier_name'])?></b></p>
    <p>Invoice Date: <?=h($inv['invoice_date'])?></p>
</div>

<div style="background:#111;border:1px solid #b00000;padding:20px;border-radius:8px;max-width:900px;">

<form method="post" enctype="multipart/form-data">

    <h3 style="color:#ff3333;">Part Type *</h3>
    <select name="part_type" required>
        <option value="">-- Select --</option>
        <option>OEM (New Original)</option>
        <option>Replacement (New)</option>
        <option>Secondhand (Bought-In)</option>
        <option>Stripped (From Vehicle)</option>
    </select>

    <h3 style="color:#ff3333;margin-top:20px;">EPC / Location</h3>

    <label>Category</label>
    <select name="category_id">
        <option value="">-- Select --</option>
        <?php while ($r=$categories->fetch_assoc()): ?>
            <option value="<?=$r['id']?>"><?=$r['name']?></option>
        <?php endwhile; ?>
    </select>

    <label>Subcategory</label>
    <select name="subcategory_id">
        <option value="">-- Select --</option>
        <?php while ($r=$subcats->fetch_assoc()): ?>
            <option value="<?=$r['id']?>"><?=$r['name']?></option>
        <?php endwhile; ?>
    </select>

    <label>Type</label>
    <select name="type_id">
        <option value="">-- Select --</option>
        <?php while ($r=$types->fetch_assoc()): ?>
            <option value="<?=$r['id']?>"><?=$r['name']?></option>
        <?php endwhile; ?>
    </select>

    <label>Component</label>
    <select name="component_id">
        <option value="">-- Select --</option>
        <?php while ($r=$components->fetch_assoc()): ?>
            <option value="<?=$r['id']?>"><?=$r['name']?></option>
        <?php endwhile; ?>
    </select>

    <h3 style="color:#ff3333;margin-top:20px;">Part Details</h3>

    <label>Part Name *</label>
    <input type="text" name="part_name" required>

    <label>Cost Price *</label>
    <input type="number" name="cost_price" step="0.01">

    <label>Encoded Cost (auto)</label>
    <input type="text" readonly value="" placeholder="Generated after saving">

    <label>Quantity</label>
    <input type="number" name="qty" value="1">

    <label>Notes</label>
    <textarea name="notes" rows="3"></textarea>

    <button class="btn" style="margin-top:15px;">Add Part</button>

</form>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
