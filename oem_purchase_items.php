<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

// Validate
if (!isset($_GET['invoice_id']) || !is_numeric($_GET['invoice_id'])) {
    die("<h2 style='color:red;text-align:center;'>Invalid Invoice ID</h2>");
}

$invoice_id = (int)$_GET['invoice_id'];

/* --------------------------------------------------------
   LOAD INVOICE HEADER
-------------------------------------------------------- */
$sql = "
    SELECT i.*, s.name AS supplier_name
    FROM supplier_oem_invoices i
    LEFT JOIN suppliers s ON s.id = i.supplier_id
    WHERE i.id = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("<h2 style='color:red;text-align:center;'>Invoice not found.</h2>");
}

/* --------------------------------------------------------
   LOAD INVOICE ITEMS (WITH EPC)
-------------------------------------------------------- */
$sqlItems = "
SELECT 
    it.*, 
    p.oem_number,
    p.part_name AS master_part_name,
    c.name AS cat_name,
    sc.name AS subcat_name,
    t.name AS type_name,
    comp.name AS comp_name
FROM supplier_oem_invoice_items it
LEFT JOIN oem_parts p      ON p.id = it.oem_part_id
LEFT JOIN categories c     ON c.id = it.category_id
LEFT JOIN subcategories sc ON sc.id = it.subcategory_id
LEFT JOIN types t          ON t.id = it.type_id
LEFT JOIN components comp  ON comp.id = it.component_id
WHERE it.invoice_id = ?
ORDER BY it.id ASC
";

$stmt = $conn->prepare($sqlItems);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>OEM Purchase Items</title>
<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, sans-serif;
}
.header-box {
    width:90%;
    margin:20px auto;
    background:#111;
    padding:15px;
    border-radius:10px;
    border:2px solid #b00000;
}
.table {
    width:90%;
    margin:20px auto;
    border-collapse:collapse;
}
.table th, .table td {
    border:1px solid #b00000;
    padding:10px;
}
.table th {
    background:#b00000;
    text-align:left;
}
.btn {
    display:inline-block;
    padding:10px 20px;
    background:#b00000;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    margin:15px 5%;
}
.btn:hover {
    background:#ff3333;
}
</style>
</head>
<body>

<h2 style="text-align:center;color:#ff3333;">OEM Purchase Items</h2>

<div class="header-box">
    <p><b>Supplier:</b> <?= htmlspecialchars($invoice['supplier_name']) ?></p>
    <p><b>Invoice #:</b> <?= htmlspecialchars($invoice['invoice_number']) ?></p>
    <p><b>Date:</b> <?= htmlspecialchars($invoice['invoice_date']) ?></p>
    <p><b>Total Amount:</b> R <?= number_format((float)$invoice['total_amount'], 2) ?></p>
</div>

<a class="btn" href="oem_purchase_add.php">+ New OEM Invoice</a>

<table class="table">
    <tr>
        <th>#</th>
        <th>OEM Number</th>
        <th>Part Name</th>
        <th>EPC Structure</th>
        <th style="width:80px;">Qty</th>
        <th style="width:120px;">Cost</th>
        <th style="width:120px;">Total</th>
    </tr>

    <?php if ($items->num_rows == 0): ?>
        <tr><td colspan="7" style="text-align:center;color:#ff3333;">No Items Added</td></tr>
    <?php else: ?>
        <?php $i = 1; while($row = $items->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['oem_number']) ?></td>
                <td><?= htmlspecialchars($row['part_name']) ?></td>

                <td>
                    <?= htmlspecialchars($row['cat_name']) ?><br>
                    <small>
                        <?= htmlspecialchars($row['subcat_name']) ?> →
                        <?= htmlspecialchars($row['type_name']) ?> →
                        <?= htmlspecialchars($row['comp_name']) ?>
                    </small>
                </td>

                <td><?= (int)$row['qty'] ?></td>
                <td>R <?= number_format((float)$row['cost_price'], 2) ?></td>
                <td>R <?= number_format((float)$row['line_total'], 2) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php endif; ?>
</table>

</body>
</html>
