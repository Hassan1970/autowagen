<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('h')) {
    function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid invoice ID.");
}

// Load invoice header (using suppliers table)
$sql = "
    SELECT rsi.*, s.supplier_name
    FROM replacement_supplier_invoices rsi
    LEFT JOIN suppliers s ON s.id = rsi.supplier_id
    WHERE rsi.id = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$invRes = $stmt->get_result();
$invoice = $invRes->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("Invoice not found.");
}

// Load items
$sql = "
    SELECT i.*, p.part_number, p.part_name
    FROM replacement_supplier_invoice_items i
    LEFT JOIN replacement_parts p ON p.id = i.part_id
    WHERE i.invoice_id = ?
    ORDER BY i.id ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
    $total += ((float)$row['qty']) * ((float)$row['supplier_price']);
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>View Replacement Supplier Invoice</title>
<style>
body { background:#000; color:#fff; font-family:Arial,sans-serif; margin:0; }
.wrap {
    width:80%;
    margin:25px auto;
    background:#111;
    border:2px solid #b00000;
    border-radius:8px;
    padding:20px 25px 25px;
}
h1 { margin:0 0 15px 0; color:#ff3333; }
label { font-weight:bold; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th,td { padding:6px 5px; border-bottom:1px solid #333; font-size:12px; }
th { background:#181818; }
.total-row td { font-weight:bold; border-top:2px solid #666; }
.btn {
    display:inline-block;
    padding:6px 12px;
    background:#444;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
    font-size:12px;
    margin-top:10px;
}
.btn-edit { background:#0044aa; }
</style>
</head>
<body>

<div class="wrap">
    <h1>Replacement Supplier Invoice #<?php echo (int)$invoice['id']; ?></h1>

    <p>
        <label>Supplier:</label>
        <?php echo h($invoice['supplier_name']); ?><br>
        <label>Invoice Number:</label>
        <?php echo h($invoice['invoice_number']); ?><br>
        <label>Invoice Date:</label>
        <?php echo h($invoice['invoice_date']); ?><br>
        <label>Notes:</label>
        <?php echo nl2br(h($invoice['notes'])); ?><br>
        <label>Created:</label>
        <?php echo h($invoice['created_at']); ?>
    </p>

    <h3>Items</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Part #</th>
                <th>Part Name</th>
                <th style="text-align:right;">Qty</th>
                <th style="text-align:right;">Supplier Cost</th>
                <th style="text-align:right;">Line Total</th>
                <th>Supplier Part #</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="7">No items.</td></tr>
            <?php else: ?>
                <?php $i = 1; foreach ($items as $it): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo h($it['part_number']); ?></td>
                    <td><?php echo h($it['part_name']); ?></td>
                    <td style="text-align:right;"><?php echo (int)$it['qty']; ?></td>
                    <td style="text-align:right;"><?php echo number_format((float)$it['supplier_price'], 2); ?></td>
                    <td style="text-align:right;"><?php echo number_format((float)$it['qty'] * (float)$it['supplier_price'], 2); ?></td>
                    <td><?php echo h($it['supplier_part_number']); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">Invoice Total:</td>
                    <td style="text-align:right;"><?php echo number_format($total, 2); ?></td>
                    <td></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a class="btn" href="replacement_purchase_list.php">← Back to Invoice List</a>
    <a class="btn btn-edit" href="replacement_purchase_edit.php?id=<?php echo (int)$invoice['id']; ?>">Edit Invoice Header</a>
</div>

</body>
</html>
