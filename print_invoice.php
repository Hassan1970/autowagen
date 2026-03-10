```php
<?php
require_once __DIR__ . '/config/config.php';

/* ===============================
   VALIDATE INVOICE ID
=============================== */
$invoiceId = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

if ($invoiceId <= 0) {
    die("Invalid invoice ID");
}

/* ===============================
   GET INVOICE HEADER
=============================== */
$stmt = $conn->prepare("
    SELECT
        id,
        customer_name,
        customer_phone,
        total_amount,
        status,
        created_at
    FROM pos_invoices
    WHERE id = ?
");

$stmt->bind_param("i", $invoiceId);
$stmt->execute();

$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    die("Invoice not found");
}

/* ===============================
   GET INVOICE ITEMS
=============================== */
$stmtItems = $conn->prepare("
SELECT
    part_id,
    part_type,
    part_name,
    quantity,
    price,
    subtotal,
    COALESCE(vehicle_stock_code,'-') AS vehicle_stock_code
FROM pos_invoice_items
WHERE invoice_id = ?
ORDER BY id ASC
");

$stmtItems->bind_param("i", $invoiceId);
$stmtItems->execute();

$items = $stmtItems->get_result();

/* ===============================
   FORMAT PART TYPE
=============================== */
function formatPartType($type)
{
    switch($type)
    {
        case 'STRIP': return 'STRIPPED';
        case 'TP': return 'THIRD-PARTY';
        case 'OEM': return 'OEM';
        case 'NEW': return 'REPLACEMENT';
        default: return $type;
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Invoice #<?= $invoice['id'] ?></title>

<style>

body{
font-family:Arial;
margin:40px;
color:#000;
}

.company{
font-size:26px;
font-weight:bold;
}

.subtitle{
font-size:14px;
margin-bottom:20px;
}

.header{
margin-top:20px;
margin-bottom:20px;
}

table{
width:100%;
border-collapse:collapse;
margin-top:20px;
}

th,td{
border:1px solid #000;
padding:8px;
font-size:14px;
}

th{
background:#eee;
}

.total{
margin-top:20px;
font-size:22px;
font-weight:bold;
text-align:right;
}

.print-btn{
margin-top:30px;
padding:12px 20px;
font-size:16px;
cursor:pointer;
}

@media print{

.print-btn{
display:none;
}

body{
margin:10px;
}

}

</style>

</head>

<body>


<div class="company">
AUTO WAGEN
</div>

<div class="subtitle">
Vehicle Parts Invoice System
</div>


<div class="header">

<b>Invoice Number:</b> INV-<?= str_pad($invoice['id'],6,'0',STR_PAD_LEFT) ?><br>

<b>Invoice ID:</b> <?= $invoice['id'] ?><br>

<b>Date:</b> <?= $invoice['created_at'] ?><br>

<b>Status:</b> <?= strtoupper($invoice['status']) ?><br><br>

<b>Customer:</b> <?= htmlspecialchars($invoice['customer_name']) ?><br>

<b>Phone:</b> <?= htmlspecialchars($invoice['customer_phone']) ?><br>

</div>


<table>

<tr>
<th>Item</th>
<th>Vehicle Code</th>
<th>Type</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
</tr>

<?php while($row = $items->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['part_name']) ?></td>

<td><?= htmlspecialchars($row['vehicle_stock_code']) ?></td>

<td><?= formatPartType($row['part_type']) ?></td>

<td><?= $row['quantity'] ?></td>

<td>R <?= number_format($row['price'],2) ?></td>

<td>R <?= number_format($row['subtotal'],2) ?></td>

</tr>

<?php endwhile; ?>

</table>


<div class="total">
Grand Total: R <?= number_format($invoice['total_amount'],2) ?>
</div>


<button class="print-btn" onclick="window.print()">
Print Invoice / Save PDF
</button>


<script>
// optional auto print
// window.print();
</script>

</body>

</html>
```
