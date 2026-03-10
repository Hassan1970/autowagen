<?php
require_once __DIR__ . "/config/config.php";

/*
|--------------------------------------------------------------------------
| GET INVOICE ID
|--------------------------------------------------------------------------
*/
$invoice_id = (int)($_GET['id'] ?? 0);
if ($invoice_id <= 0) {
    die("Invalid invoice ID");
}

/*
|--------------------------------------------------------------------------
| LOAD INVOICE (NO CUSTOMER JOIN)
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        id,
        invoice_number,
        invoice_date,
        customer_name,
        customer_phone,
        payment_method,
        total_amount,
        amount_paid,
        vat_enabled,
        vat_rate,
        notes,
        created_at
    FROM pos_invoices
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("Invoice not found");
}

/*
|--------------------------------------------------------------------------
| LOAD INVOICE ITEMS
|--------------------------------------------------------------------------
*/
$item_sql = "
    SELECT
        part_type,
        part_name,
        quantity,
        price,
        subtotal
    FROM pos_invoice_items
    WHERE invoice_id = ?
";

$item_stmt = $conn->prepare($item_sql);
$item_stmt->bind_param("i", $invoice_id);
$item_stmt->execute();
$items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$item_stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <style>
        body {
            background:#111;
            color:#fff;
            font-family: Arial, sans-serif;
        }
        .wrap {
            width: 900px;
            margin: 30px auto;
            background:#000;
            padding: 20px;
            border: 2px solid #b00000;
        }
        h2 {
            color:#ff4444;
            margin-top:0;
        }
        table {
            width:100%;
            border-collapse: collapse;
            margin-top:15px;
        }
        th, td {
            border:1px solid #b00000;
            padding:6px;
        }
        th {
            background:#1b1b1b;
        }
        .right {
            text-align:right;
        }
    </style>
</head>
<body>

<div class="wrap">
    <h2>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></h2>

    <p>
        <b>Date:</b> <?= htmlspecialchars($invoice['invoice_date']) ?><br>
        <b>Customer:</b> <?= htmlspecialchars($invoice['customer_name']) ?><br>
        <b>Phone:</b> <?= htmlspecialchars($invoice['customer_phone']) ?><br>
        <b>Payment Method:</b> <?= htmlspecialchars($invoice['payment_method']) ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Part Name</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['part_type']) ?></td>
                <td><?= htmlspecialchars($row['part_name']) ?></td>
                <td class="right"><?= (int)$row['quantity'] ?></td>
                <td class="right"><?= number_format($row['price'], 2) ?></td>
                <td class="right"><?= number_format($row['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="right">
        <b>Total:</b> <?= number_format($invoice['total_amount'], 2) ?><br>
        <b>Paid:</b> <?= number_format($invoice['amount_paid'], 2) ?><br>
        <b>Balance:</b> <?= number_format($invoice['total_amount'] - $invoice['amount_paid'], 2) ?>
    </p>

    <?php if (!empty($invoice['notes'])): ?>
        <p><b>Notes:</b><br><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
    <?php endif; ?>
</div>

</body>
</html>
