<?php
require_once __DIR__ . '/config/config.php';
$page_title = "Supplier Invoice Items";
include __DIR__ . '/includes/header.php';

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// ------------------------------------------
// Filters
// ------------------------------------------
$f_supplier = (int)($_GET['supplier_id'] ?? 0);
$f_invoice  = trim($_GET['invoice_number'] ?? "");
$f_type     = trim($_GET['part_type'] ?? "");
$f_cat      = (int)($_GET['category_id'] ?? 0);
$f_from     = trim($_GET['date_from'] ?? "");
$f_to       = trim($_GET['date_to'] ?? "");

$where = [];
$types = "";
$params = [];

// Supplier filter
if ($f_supplier > 0) {
    $where[] = "si.supplier_id = ?";
    $types  .= "i";
    $params[] = $f_supplier;
}

// Invoice #
if ($f_invoice !== "") {
    $where[] = "si.invoice_number LIKE ?";
    $types  .= "s";
    $params[] = "%$f_invoice%";
}

// Part type
if ($f_type !== "") {
    $where[] = "sii.part_type = ?";
    $types  .= "s";
    $params[] = $f_type;
}

// Category
if ($f_cat > 0) {
    $where[] = "sii.category_id = ?";
    $types  .= "i";
    $params[] = $f_cat;
}

// Date range
if ($f_from !== "") {
    $where[] = "si.invoice_date >= ?";
    $types  .= "s";
    $params[] = $f_from;
}
if ($f_to !== "") {
    $where[] = "si.invoice_date <= ?";
    $types  .= "s";
    $params[] = $f_to;
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// --------------------------------------------
// Load suppliers + categories for dropdown
// --------------------------------------------
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

// --------------------------------------------
// Build main query
// --------------------------------------------
$sql = "
SELECT 
    sii.id AS item_id,
    sii.part_name,
    sii.part_type,
    sii.cost_price,
    sii.qty,
    sii.encoded_cost,
    si.invoice_number,
    si.invoice_date,
    s.supplier_name,
    c.name AS category_name,
    sc.name AS subcategory_name,
    sii.invoice_id
FROM supplier_invoice_items sii
LEFT JOIN supplier_invoices si ON si.id = sii.invoice_id
LEFT JOIN suppliers s ON s.id = si.supplier_id
LEFT JOIN categories c ON c.id = sii.category_id
LEFT JOIN subcategories sc ON sc.id = sii.subcategory_id
$whereSql
ORDER BY sii.id DESC
";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$items = $stmt->get_result();

// For export links – keep current filters
$exportQuery = http_build_query([
    'supplier_id'    => $f_supplier,
    'invoice_number' => $f_invoice,
    'part_type'      => $f_type,
    'category_id'    => $f_cat,
    'date_from'      => $f_from,
    'date_to'        => $f_to,
]);

$grandTotal = 0;
?>

<style>
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:6px 8px;border-bottom:1px solid #333;}
th{background:#222;}
.btn-small{padding:4px 8px;font-size:12px;}
.thumb-img{
    width:40px;height:40px;object-fit:cover;border:1px solid #333;border-radius:4px;
}
@media (max-width:900px){
    .page-header h1 { font-size:20px; }
    table, thead, tbody, th, td, tr { font-size:12px; }
}
</style>

<div class="page-header">
    <div>
        <h1>Supplier Invoice Items</h1>
        <p>Every part added from suppliers — all invoices combined.</p>
    </div>
</div>

<!-- FILTER BAR -->
<div style="background:#111;border:1px solid #b00000;border-radius:6px;padding:12px;margin-bottom:15px;">
<form method="get">
<div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">

    <div style="flex:0 0 180px;">
        <label style="font-size:12px;">Supplier</label>
        <select name="supplier_id" style="width:100%;">
            <option value="0">All</option>
            <?php while($s=$suppliers->fetch_assoc()): ?>
                <option value="<?=$s['id']?>" <?=($f_supplier==$s['id'])?'selected':''?>>
                    <?=h($s['supplier_name'])?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div style="flex:0 0 140px;">
        <label style="font-size:12px;">Invoice #</label>
        <input name="invoice_number" value="<?=h($f_invoice)?>">
    </div>

    <div style="flex:0 0 140px;">
        <label style="font-size:12px;">Part Type</label>
        <select name="part_type">
            <option value="">All</option>
            <option value="OEM" <?=($f_type=="OEM"?"selected":"")?>>OEM</option>
            <option value="SecondHand" <?=($f_type=="SecondHand"?"selected":"")?>>SecondHand</option>
            <option value="Stripped" <?=($f_type=="Stripped"?"selected":"")?>>Stripped</option>
        </select>
    </div>

    <div style="flex:0 0 180px;">
        <label style="font-size:12px;">Category</label>
        <select name="category_id" style="width:100%;">
            <option value="0">All</option>
            <?php mysqli_data_seek($categories,0);
            while($c=$categories->fetch_assoc()): ?>
                <option value="<?=$c['id']?>" <?=($f_cat==$c['id'])?'selected':''?>>
                    <?=h($c['name'])?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div style="flex:0 0 140px;">
        <label style="font-size:12px;">From</label>
        <input type="date" name="date_from" value="<?=h($f_from)?>">
    </div>

    <div style="flex:0 0 140px;">
        <label style="font-size:12px;">To</label>
        <input type="date" name="date_to" value="<?=h($f_to)?>">
    </div>

    <button class="btn" style="flex:0 0 120px;">Search</button>
    <a href="supplier_invoice_items_list.php" class="btn secondary" style="flex:0 0 80px;text-align:center;">Reset</a>

</div>
</form>
</div>

<!-- ITEMS TABLE -->
<div style="background:#111;border:1px solid #b00000;border-radius:6px;padding:15px;">

    <div style="margin-bottom:10px;">
        <a href="export_invoice_items_excel.php?<?=$exportQuery?>" class="btn">Export Excel</a>
        <a href="export_invoice_items_pdf.php?<?=$exportQuery?>" class="btn secondary">Export PDF</a>
    </div>

    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Invoice #</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Part Type</th>
                <th>Category</th>
                <th>Part Name</th>
                <th>Cost</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Photos</th>
                <th style="width:210px;">Actions</th>
            </tr>
        </thead>
        <tbody>

<?php while($row = $items->fetch_assoc()):
    $total = $row['cost_price'] * $row['qty'];
    $grandTotal += $total;

    // Load photos for this item (per-line photos)
    $photos = $conn->query("SELECT file_name FROM invoice_item_photos WHERE item_id=".(int)$row['item_id']);
?>
<tr>
    <td><?= $row['item_id'] ?></td>
    <td><?= h($row['invoice_number']) ?></td>
    <td><?= h($row['supplier_name']) ?></td>
    <td><?= h($row['invoice_date']) ?></td>
    <td><?= h($row['part_type']) ?></td>
    <td><?= h($row['category_name']) ?></td>
    <td><?= h($row['part_name']) ?></td>
    <td>R <?= number_format($row['cost_price'],2) ?></td>
    <td><?= (int)$row['qty'] ?></td>
    <td>R <?= number_format($total,2) ?></td>
    <td>
        <?php if ($photos && $photos->num_rows): ?>
            <?php while($p = $photos->fetch_assoc()): ?>
                <img src="uploads/invoice_items/<?=h($p['file_name'])?>"
                     class="thumb-img"
                     onclick="window.open('uploads/invoice_items/<?=h($p['file_name'])?>','_blank')" />
            <?php endwhile; ?>
        <?php else: ?>
            <span style="color:#555;">–</span>
        <?php endif; ?>
    </td>
    <td>
        <a href="invoice_view.php?id=<?=$row['invoice_id']?>" class="btn secondary btn-small">View Invoice</a>
        <a href="invoice_item_edit.php?id=<?=$row['item_id']?>" class="btn btn-small">Edit</a>
        <a href="invoice_item_delete.php?id=<?=$row['item_id']?>"
           class="btn btn-small secondary"
           onclick="return confirm('Delete this item?');">
            Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>

<tr>
    <td colspan="9" style="text-align:right;font-weight:bold;">Grand Total:</td>
    <td style="font-weight:bold;">R <?= number_format($grandTotal,2) ?></td>
    <td colspan="2"></td>
</tr>

        </tbody>
    </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
