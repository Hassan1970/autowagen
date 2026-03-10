<?php
require_once __DIR__ . '/config/config.php';

$invoice_number = trim($_GET['invoice_number'] ?? '');
$supplier_id    = (int) ($_GET['supplier_id'] ?? 0);

if ($invoice_number === '' || $supplier_id <= 0) {
    die('Invalid invoice');
}

/* ================= SAVE SELLING PRICE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['part_id'])) {
    $part_id       = (int) $_POST['part_id'];
    $selling_price = ($_POST['selling_price'] !== '')
        ? (float) $_POST['selling_price']
        : null;

    $stmt = $conn->prepare("
        UPDATE third_party_parts
        SET selling_price = ?
        WHERE id = ?
    ");
    $stmt->bind_param("di", $selling_price, $part_id);
    $stmt->execute();
    $stmt->close();

    header("Location: third_party_invoice_view.php?invoice_number="
        . urlencode($invoice_number) . "&supplier_id=" . $supplier_id);
    exit;
}

/* ================= HEADER ================= */
$stmt = $conn->prepare("
    SELECT s.supplier_name, MAX(tpp.invoice_date) AS invoice_date
    FROM third_party_parts tpp
    JOIN third_party_suppliers s ON s.id = tpp.third_supplier_id
    WHERE tpp.invoice_number = ? AND tpp.third_supplier_id = ?
");
$stmt->bind_param("si", $invoice_number, $supplier_id);
$stmt->execute();
$header = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ================= PARTS ================= */
$stmt = $conn->prepare("
    SELECT
        tpp.id,
        c.name  AS category,
        sc.name AS subcategory,
        t.name  AS type,
        cp.name AS component,
        tpp.description,
        tpp.side,
        tpp.cost_incl,
        tpp.selling_price,
        tpp.photo
    FROM third_party_parts tpp
    LEFT JOIN categories c     ON c.id = tpp.category_id
    LEFT JOIN subcategories sc ON sc.id = tpp.subcategory_id
    LEFT JOIN types t          ON t.id = tpp.type_id
    LEFT JOIN components cp    ON cp.id = tpp.component_id
    WHERE tpp.invoice_number = ?
      AND tpp.third_supplier_id = ?
");
$stmt->bind_param("si", $invoice_number, $supplier_id);
$stmt->execute();
$parts = $stmt->get_result();
$stmt->close();

include __DIR__ . '/includes/header.php';
?>

<style>
.wrap{width:95%;max-width:1400px;margin:30px auto;background:#111;border:2px solid red;border-radius:10px;padding:20px;}
h1{color:#ff3333}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{padding:8px;border-bottom:1px solid #333;vertical-align:top}
th{background:#222;color:#ff3333}
img.thumb{max-width:60px;border-radius:4px}
input.price{
    width:90px;
    background:#000;
    color:#fff;
    border:1px solid #444;
    padding:4px;
}
button.save{
    background:#c00000;
    color:#fff;
    border:none;
    padding:4px 10px;
    border-radius:4px;
    cursor:pointer;
}
.muted{color:#777;font-style:italic}
</style>

<div class="wrap">
<h1>Invoice <?=htmlspecialchars($invoice_number)?></h1>
<p>
<strong>Supplier:</strong> <?=htmlspecialchars($header['supplier_name'])?><br>
<strong>Date:</strong> <?=htmlspecialchars($header['invoice_date'])?>
</p>

<table>
<tr>
<th>Category</th>
<th>Subcategory</th>
<th>Type</th>
<th>Component</th>
<th>Description</th>
<th>Side</th>
<th>Cost Incl</th>
<th>Selling Price</th>
<th>Action</th>
<th>Photo</th>
</tr>

<?php
$totalCost = 0;
$totalSell = 0;

while($p = $parts->fetch_assoc()):
    $cost = (float)$p['cost_incl'];
    $sell = $p['selling_price'];

    $totalCost += $cost;
    if ($sell !== null) $totalSell += (float)$sell;
?>
<tr>
<td><?=htmlspecialchars($p['category'])?></td>
<td><?=htmlspecialchars($p['subcategory'])?></td>
<td><?=htmlspecialchars($p['type'])?></td>
<td><?=htmlspecialchars($p['component'])?></td>
<td><?=htmlspecialchars($p['description'])?></td>
<td><?=htmlspecialchars($p['side'])?></td>
<td>R <?=number_format($cost,2)?></td>
<td>
<form method="post" style="display:inline">
<input type="hidden" name="part_id" value="<?=$p['id']?>">
<input class="price" type="number" step="0.01"
       name="selling_price"
       value="<?= $sell !== null ? number_format($sell,2,'.','') : '' ?>"
       placeholder="Set price">
</td>
<td>
<button class="save" type="submit">Save</button>
</form>
</td>
<td>
<?php if ($p['photo']): ?>
<img src="uploads/third_party_parts/<?=htmlspecialchars($p['photo'])?>" class="thumb">
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>

<div style="margin-top:15px;text-align:right">
<strong>Total Cost:</strong> R <?=number_format($totalCost,2)?><br>
<strong>Total Selling:</strong> R <?=number_format($totalSell,2)?>
</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
