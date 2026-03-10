<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Load all invoices
$sql = "
    SELECT 
        inv.id,
        inv.invoice_number,
        inv.invoice_date,
        inv.total_amount,
        inv.payment_method,
        inv.status,
        c.full_name,
        c.phone,
        c.customer_type
    FROM pos_invoices inv
    LEFT JOIN customers c ON inv.customer_id = c.id
    ORDER BY inv.id DESC
";
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>POS Invoice List</title>

<style>
body { background:#000; color:#fff; font-family:Arial; }
.wrap {
    width:92%; margin:25px auto; padding:20px;
    background:#111; border:2px solid #b00000;
    border-radius:10px;
}
h2 { margin:0 0 15px; color:#ff3333; }

.btn {
    background:#b00000; color:#fff; padding:7px 14px;
    border-radius:6px; text-decoration:none; font-size:13px;
}
.btn:hover { background:#ff1a1a; }

.table { width:100%; border-collapse:collapse; margin-top:20px; }
.table th, .table td {
    border:1px solid #b00000; padding:8px; font-size:13px;
}
.table th { background:#1f1f1f; color:#ff3333; }

.status-paid { color:#00ff00; font-weight:bold; }
.status-unpaid { color:#ff3333; font-weight:bold; }
</style>
</head>

<body>
<div class="wrap">

    <h2>🧾 POS Invoice List</h2>

    <a href="pos_invoice_add.php" class="btn">➕ Create New Invoice</a>

    <table class="table">
        <tr>
            <th>ID</th>
            <th>Invoice No</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Customer Type</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($res->num_rows == 0): ?>
            <tr><td colspan="10" style="text-align:center;">No invoices found.</td></tr>
        <?php else: ?>

        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= h($row['invoice_number']) ?></td>
            <td><?= h($row['invoice_date']) ?></td>

            <td><?= h($row['full_name']) ?></td>
            <td><?= h($row['phone']) ?></td>
            <td><?= h($row['customer_type']) ?></td>

            <td>R <?= number_format($row['total_amount'], 2) ?></td>
            <td><?= h($row['payment_method']) ?></td>

            <td>
                <?php if ($row['status'] === 'PAID'): ?>
                    <span class="status-paid">PAID</span>
                <?php else: ?>
                    <span class="status-unpaid">UNPAID</span>
                <?php endif; ?>
            </td>

            <td>
                <a href="pos_invoice_view.php?id=<?= $row['id'] ?>" class="btn">View</a>
                <a href="pos_invoice_pdf.php?id=<?= $row['id'] ?>" target="_blank" class="btn">PDF</a>
                <a href="pos_invoice_edit.php?id=<?= $row['id'] ?>" class="btn">Edit</a>
                <a href="pos_invoice_delete.php?id=<?= $row['id'] ?>"
                   class="btn"
                   onclick="return confirm('Delete this invoice?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>

        <?php endif; ?>
    </table>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
