<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper
function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// Encode cost using a simple map
function encode_cost($amount) {
    $map = [
        '0'=>'Q','1'=>'W','2'=>'E','3'=>'R','4'=>'T',
        '5'=>'Y','6'=>'U','7'=>'I','8'=>'O','9'=>'P',
        '.'=>'X'
    ];
    $str = number_format((float)$amount, 2, '.', '');
    return strtr($str, $map);
}

$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// -------------------------------------------------------
// PROCESS POST BEFORE ANY OUTPUT (AVOID HEADER ERRORS)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Save invoice header
    if (isset($_POST['save_invoice'])) {
        $invoice_id  = (int)($_POST['invoice_id'] ?? 0);
        $supplier_id = (int)($_POST['supplier_id'] ?? 0);
        $invoice_no  = trim($_POST['invoice_number'] ?? '');
        $invoice_dt  = trim($_POST['invoice_date'] ?? '');
        $total_amt   = trim($_POST['total_amount'] ?? '');
        $notes       = trim($_POST['notes'] ?? '');

        if ($invoice_id > 0) {
            // Update existing invoice
            $stmt = $conn->prepare("
                UPDATE supplier_invoices
                SET supplier_id = ?, invoice_number = ?, invoice_date = ?, total_amount = ?, notes = ?
                WHERE id = ?
            ");
            $stmt->bind_param("issdsi", $supplier_id, $invoice_no, $invoice_dt, $total_amt, $notes, $invoice_id);
            $stmt->execute();
            $stmt->close();

        } else {
            // Insert new invoice
            $stmt = $conn->prepare("
                INSERT INTO supplier_invoices (supplier_id, invoice_number, invoice_date, total_amount, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issds", $supplier_id, $invoice_no, $invoice_dt, $total_amt, $notes);
            $stmt->execute();
            $invoice_id = $stmt->insert_id;
            $stmt->close();
        }

        // 1b) MULTIPLE FILE UPLOADS FOR INVOICE ATTACHMENTS
        if (!empty($_FILES['attachments']['name'][0])) {

            $uploadDir = __DIR__ . '/uploads/supplier_invoices/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $allowed_ext = ['pdf','jpg','jpeg','png','gif'];

            foreach ($_FILES['attachments']['name'] as $idx => $origName) {
                $origName = trim($origName);
                if ($origName === '') continue;

                $tmpName = $_FILES['attachments']['tmp_name'][$idx];
                $error   = $_FILES['attachments']['error'][$idx];

                if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($tmpName)) {
                    continue;
                }

                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_ext)) {
                    continue;
                }

                $newName   = 'inv_' . $invoice_id . '_' . time() . '_' . $idx . '.' . $ext;
                $fullPath  = $uploadDir . $newName;
                $relPath   = 'uploads/supplier_invoices/' . $newName;

                if (move_uploaded_file($tmpName, $fullPath)) {
                    $mime = mime_content_type($fullPath);
                    $stmt = $conn->prepare("
                        INSERT INTO supplier_invoice_files (invoice_id, file_path, original_name, file_type)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->bind_param("isss", $invoice_id, $relPath, $origName, $mime);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // Redirect so refresh doesn't re-post
        header("Location: invoice_add.php?id=" . $invoice_id);
        exit;
    }

    // 2) Add part item to invoice
    if (isset($_POST['add_item'])) {
        $invoice_id = (int)($_POST['invoice_id'] ?? 0);

        // IMPORTANT: If invoice_id is 0, user did not save header yet
        if ($invoice_id > 0) {
            $part_type      = $_POST['part_type'] ?? 'OEM';
            $category_id    = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $subcategory_id = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
            $type_id        = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : null;
            $component_id   = !empty($_POST['component_id']) ? (int)$_POST['component_id'] : null;
            $part_name      = trim($_POST['part_name'] ?? '');
            $cost_price     = (float)($_POST['cost_price'] ?? 0);
            $qty            = (int)($_POST['qty'] ?? 1);
            $notes_item     = trim($_POST['item_notes'] ?? '');
            $encoded_cost   = encode_cost($cost_price);

            if ($part_name !== '') {
                $stmt = $conn->prepare("
                    INSERT INTO supplier_invoice_items
                    (invoice_id, part_type, category_id, subcategory_id, type_id, component_id,
                     part_name, cost_price, encoded_cost, qty, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param(
                    "isiiiisddis",
                    $invoice_id,
                    $part_type,
                    $category_id,
                    $subcategory_id,
                    $type_id,
                    $component_id,
                    $part_name,
                    $cost_price,
                    $encoded_cost,
                    $qty,
                    $notes_item
                );
                $stmt->execute();
                $stmt->close();
            }

            // Redirect after adding item to avoid resubmission on refresh
            header("Location: invoice_add.php?id=" . $invoice_id);
            exit;
        }
    }
}

// ----------------------
// Load dropdown lists
// ----------------------
$suppliers  = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

// ----------------------
// Load invoice record
// ----------------------
$header = null;
$items = [];
$invoice_not_found = false;

if ($invoice_id > 0) {

    $stmt = $conn->prepare("
        SELECT si.*, s.supplier_name
        FROM supplier_invoices si
        LEFT JOIN suppliers s ON s.id = si.supplier_id
        WHERE si.id = ?
    ");
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $header = $res->fetch_assoc();
    $stmt->close();

    if (!$header) {
        $invoice_not_found = true;
    } else {

        $stmt = $conn->prepare("
            SELECT sii.*, c.name AS category_name, sc.name AS subcategory_name
            FROM supplier_invoice_items sii
            LEFT JOIN categories c   ON c.id  = sii.category_id
            LEFT JOIN subcategories sc ON sc.id = sii.subcategory_id
            WHERE sii.invoice_id = ?
            ORDER BY sii.id ASC
        ");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
    }
}

// Load files
$files = [];
if ($invoice_id > 0) {
    $fs = $conn->prepare("SELECT * FROM supplier_invoice_files WHERE invoice_id = ? ORDER BY created_at ASC");
    $fs->bind_param("i", $invoice_id);
    $fs->execute();
    $fres = $fs->get_result();
    while ($f = $fres->fetch_assoc()) {
        $files[] = $f;
    }
    $fs->close();
}

include __DIR__ . "/includes/header.php";
?>

<style>
.wrap {
    width:90%;
    max-width:1100px;
    margin:30px auto 60px;
    background:#111;
    border:2px solid #b00000;
    border-radius:10px;
    padding:20px 25px 30px;
}
h1,h2 { color:#ff3333; margin-top:0; }
label { display:block; margin:4px 0 2px; font-size:13px; }
input, select, textarea {
    width:100%; padding:6px; background:#000; color:#fff;
    border:1px solid #444; border-radius:4px; margin-bottom:10px; box-sizing:border-box;
}
button, .btn {
    background:#c00000; color:#fff; padding:7px 14px; border:none;
    border-radius:4px; cursor:pointer; margin-top:5px;
}
.btn-secondary { background:#333; }
.row { display:flex; flex-wrap:wrap; gap:14px; }
.col-3 { flex:0 0 23%; }
.col-4 { flex:0 0 31%; }
.col-6 { flex:0 0 48%; }
.col-12 { flex:0 0 100%; }

@media (max-width:768px) {
    .col-3, .col-4, .col-6 { flex:0 0 100%; }
}

table {
    width:100%; margin-top:20px; border-collapse:collapse;
}
th, td {
    padding:6px 8px;
    border-bottom:1px solid #333;
}
th { background:#222; }
</style>

<script>
// Helper to get element by ID
function el(id) { return document.getElementById(id); }

function fillSelect(select, data, placeholder) {
    select.innerHTML = '';
    let opt = document.createElement('option');
    opt.value = '';
    opt.textContent = placeholder;
    select.appendChild(opt);

    data.forEach(row => {
        let o = document.createElement('option');
        o.value = row.id;
        o.textContent = row.name;
        select.appendChild(o);
    });
}

async function loadSubcategories(catId) {
    const subSelect  = el('subcategory_id');
    const typeSelect = el('type_id');
    const compSelect = el('component_id');

    fillSelect(subSelect, [], '-- Select Subcategory --');
    fillSelect(typeSelect, [], '-- Select Type --');
    fillSelect(compSelect, [], '-- Select Component --');

    if (!catId) return;

    try {
        const res = await fetch('ajax/ajax_epc_get_subcategories.php?category_id=' + encodeURIComponent(catId));
        const data = await res.json();
        fillSelect(subSelect, data, '-- Select Subcategory --');
    } catch (e) {
        console.error(e);
    }
}

async function loadTypes(subcatId) {
    const typeSelect = el('type_id');
    const compSelect = el('component_id');

    fillSelect(typeSelect, [], '-- Select Type --');
    fillSelect(compSelect, [], '-- Select Component --');

    if (!subcatId) return;

    try {
        const res = await fetch('ajax/ajax_epc_get_types.php?subcategory_id=' + encodeURIComponent(subcatId));
        const data = await res.json();
        fillSelect(typeSelect, data, '-- Select Type --');
    } catch (e) {
        console.error(e);
    }
}

async function loadComponents(typeId) {
    const compSelect = el('component_id');
    fillSelect(compSelect, [], '-- Select Component --');

    if (!typeId) return;

    try {
        const res = await fetch('ajax/ajax_epc_get_components.php?type_id=' + encodeURIComponent(typeId));
        const data = await res.json();
        fillSelect(compSelect, data, '-- Select Component --');
    } catch (e) {
        console.error(e);
    }
}
</script>

<div class="wrap">

    <h1>Supplier Invoice & Parts</h1>

    <?php if ($invoice_not_found): ?>
        <div style="padding:10px;background:#400;border:1px solid #900;border-radius:4px;margin-bottom:10px;">
            Invoice not found.
        </div>
    <?php endif; ?>

    <!-- ------------------ INVOICE HEADER FORM ------------------- -->
    <h2>Invoice Details</h2>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="invoice_id" value="<?php echo (int)$invoice_id; ?>">

        <div class="row">
            <div class="col-4">
                <label>Supplier</label>
                <select name="supplier_id" required>
                    <option value="">-- Select Supplier --</option>
                    <?php mysqli_data_seek($suppliers, 0);
                    while ($s = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($header && $header['supplier_id']==$s['id']) ? 'selected':''; ?>>
                            <?php echo h($s['supplier_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-4">
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" value="<?php echo h($header['invoice_number'] ?? ''); ?>" required>
            </div>

            <div class="col-4">
                <label>Invoice Date</label>
                <input type="date" name="invoice_date" value="<?php echo h($header['invoice_date'] ?? date('Y-m-d')); ?>" required>
            </div>

            <div class="col-4">
                <label>Total Amount (optional)</label>
                <input type="number" step="0.01" name="total_amount" value="<?php echo h($header['total_amount'] ?? ''); ?>">
            </div>

            <div class="col-12">
                <label>Notes</label>
                <textarea name="notes"><?php echo h($header['notes'] ?? ''); ?></textarea>
            </div>

            <div class="col-12">
                <label>Invoice Attachments (PDF / Images)</label>
                <input type="file" name="attachments[]" multiple>
                <small style="font-size:11px;color:#aaa;">
                    You can upload multiple files: PDF, JPG, PNG, GIF.
                </small>
            </div>
        </div>

        <button type="submit" name="save_invoice">Save Invoice Details</button>
        <a href="main_dashboard.php" class="btn-secondary btn">Back to Dashboard</a>
    </form>

    <?php if ($invoice_id > 0 && !$invoice_not_found): ?>

    <hr style="border-color:#333;margin:25px 0;">

    <!-- ------------------ ADD ITEM FORM ------------------- -->
    <h2>Add Parts to Invoice #<?php echo $invoice_id; ?></h2>

    <form method="post">
        <input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>">

        <div class="row">

            <div class="col-3">
                <label>Part Type</label>
                <select name="part_type">
                    <option value="OEM">OEM</option>
                    <option value="SecondHand">SecondHand</option>
                    <option value="Stripped">Stripped</option>
                </select>
            </div>

            <div class="col-3">
                <label>Main Category</label>
                <select name="category_id" id="category_id" onchange="loadSubcategories(this.value)">
                    <option value="">-- Select Category --</option>
                    <?php mysqli_data_seek($categories, 0);
                    while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo h($c['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-3">
                <label>Subcategory</label>
                <select name="subcategory_id" id="subcategory_id" onchange="loadTypes(this.value)">
                    <option value="">-- Select Subcategory --</option>
                </select>
            </div>

            <div class="col-3">
                <label>Type</label>
                <select name="type_id" id="type_id" onchange="loadComponents(this.value)">
                    <option value="">-- Select Type --</option>
                </select>
            </div>

            <div class="col-3">
                <label>Component</label>
                <select name="component_id" id="component_id">
                    <option value="">-- Select Component --</option>
                </select>
            </div>

            <div class="col-6">
                <label>Part Name</label>
                <input type="text" name="part_name" required>
            </div>

            <div class="col-3">
                <label>Cost Price</label>
                <input type="number" step="0.01" name="cost_price" required>
            </div>

            <div class="col-3">
                <label>Quantity</label>
                <input type="number" name="qty" value="1">
            </div>

            <div class="col-12">
                <label>Item Notes</label>
                <textarea name="item_notes"></textarea>
            </div>

        </div>

        <button type="submit" name="add_item">Add Item</button>
    </form>

    <!-- ------------------ ITEMS TABLE ------------------- -->

    <h2>Invoice Items</h2>

    <?php if (empty($items)): ?>
        <p>No items added yet.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Part Type</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Part Name</th>
                <th>Qty</th>
                <th>Cost</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $it): ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td><?php echo h($it['part_type']); ?></td>
                <td><?php echo h($it['category_name']); ?></td>
                <td><?php echo h($it['subcategory_name']); ?></td>
                <td><?php echo h($it['part_name']); ?></td>
                <td><?php echo (int)$it['qty']; ?></td>
                <td><?php echo number_format($it['cost_price'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- ------------------ FILE ATTACHMENTS ------------------- -->

    <h2>Attachments</h2>
    <?php if (empty($files)): ?>
        <p>No files uploaded yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($files as $f): ?>
            <li>
                <a href="<?php echo h($f['file_path']); ?>" target="_blank"><?php echo h($f['original_name']); ?></a>
                <span style="font-size:11px;color:#888;">
                    (<?php echo h($f['file_type']); ?>, <?php echo h($f['created_at']); ?>)
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div style="margin-top:20px;">
        <a href="main_dashboard.php" class="btn-secondary btn">Finish &amp; Exit</a>
        <a href="supplier_invoices_list.php" class="btn-secondary btn" style="margin-left:10px;">View All Invoices</a>
    </div>

    <?php endif; // invoice_id > 0 ?>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
