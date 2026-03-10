<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

// Load all OEM purchase invoices (fixed query)
$sql = "
    SELECT 
        i.id,
        i.invoice_number,
        i.invoice_date,
        s.supplier_name AS supplier_name,
        (
            SELECT SUM(qty * cost_price) 
            FROM supplier_oem_invoice_items 
            WHERE invoice_id = i.id
        ) AS total_amount
    FROM supplier_oem_invoices i
    LEFT JOIN suppliers s ON s.id = i.supplier_id
    ORDER BY i.id DESC
";
$rows = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>OEM Purchase Invoices</title>
<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, sans-serif;
}
.wrap {
    width:90%;
    margin:20px auto;
    padding:20px;
    background:#111;
    border-radius:10px;
    border:2px solid #b00000;
}
table {
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
th, td {
    border:1px solid #b00000;
    padding:10px;
}
th {
    background:#b00000;
    text-align:left;
}
.action-btn {
    padding:6px 10px;
    margin-right:6px;
    border-radius:5px;
    text-decoration:none;
    color:#fff;
    font-size:13px;
}
.view { background:#0055ff; }
.edit { background:#ffaa00; color:#000; }
.delete { background:#cc0000; }
.pdf { background:#008000; }
.add-btn {
    display:inline-block;
    padding:10px 20px;
    background:#b00000;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    margin-bottom:10px;
}
.add-btn:hover { background:#ff3333; }
</style>
</head>
<body>

<div class="wrap">
    <h2 style="color:#ff3333;">OEM Purchase Invoices</h2>

    <a href="oem_purchase_add.php" class="add-btn">+ Add New OEM Purchase</a>

    <table>
        <tr>
            <th>#</th>
            <th>Supplier</th>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Total (R)</th>
            <th style="width:200px;">Actions</th>
        </tr>

        <?php if ($rows->num_rows === 0): ?>
            <tr>
                <td colspan="6" style="text-align:center;color:#ff3333;">
                    No OEM purchase invoices found
                </td>
            </tr>
        <?php else: ?>
            <?php $i = 1; while ($r = $rows->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($r['supplier_name']) ?></td>
                    <td><?= htmlspecialchars($r['invoice_number']) ?></td>
                    <td><?= htmlspecialchars($r['invoice_date']) ?></td>
                    <td>R <?= number_format((float)$r['total_amount'], 2) ?></td>

                    <td>
                        <a class="action-btn view" href="oem_purchase_view.php?invoice_id=<?= $r['id'] ?>">View</a>
                        <a class="action-btn pdf" href="oem_purchase_pdf.php?invoice_id=<?= $r['id'] ?>" target="_blank">PDF</a>
                        <a class="action-btn edit" href="oem_purchase_edit.php?invoice_id=<?= $r['id'] ?>">Edit</a>
                        <a class="action-btn delete" href="oem_purchase_delete.php?invoice_id=<?= $r['id'] ?>"
                           onclick="return confirm('Delete this invoice? This will reverse stock!');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>

    </table>
</div>

</body>
</html>
