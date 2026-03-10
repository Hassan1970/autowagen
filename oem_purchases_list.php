<?php
require_once "config/config.php";
include "includes/header.php";

// -----------------------------
// Load OEM Purchase Invoices
// -----------------------------
$sql = "
    SELECT op.id, op.invoice_no, op.invoice_date, op.total_amount,
           s.supplier_name
    FROM oem_purchases op
    LEFT JOIN suppliers s ON op.supplier_id = s.id
    ORDER BY op.id DESC
";
$result = $conn->query($sql);
?>

<style>
.page {
    width: 95%;
    margin: 20px auto;
    background: #111;
    padding: 20px;
    border: 2px solid #b00000;
    color: white;
    border-radius: 8px;
}
h2 {
    color: #ff3333;
    text-align: center;
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
table th {
    background: #b00000;
    color: white;
    padding: 10px;
    text-align: left;
}
table td {
    padding: 10px;
    border-bottom: 1px solid #333;
    color: #ddd;
}
tr:hover {
    background: #222;
}
.btn {
    background: #b00000;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    margin-right: 5px;
}
.btn:hover {
    background: #ff3333;
}
.btn-green {
    background: green;
}
.btn-green:hover {
    background: #00cc00;
}
</style>

<div class="page">
    <h2>OEM Purchase Invoices</h2>

    <a href="oem_purchase_add.php" class="btn btn-green">+ Add New Purchase Invoice</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Invoice No</th>
            <th>Date</th>
            <th>Supplier</th>
            <th>Total Amount</th>
            <th>Actions</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['invoice_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['invoice_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name'] ?? "Unknown"); ?></td>
                    <td><?php echo number_format($row['total_amount'], 2); ?></td>
                    <td>
                        <a class="btn" href="oem_purchase_view.php?id=<?php echo $row['id']; ?>">View</a>
                        <a class="btn" href="oem_purchase_edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                        <a class="btn" href="oem_purchase_pdf.php?id=<?php echo $row['id']; ?>">PDF</a>
                        <a class="btn" style="background:#700000;"
                           href="oem_purchase_delete.php?id=<?php echo $row['id']; ?>"
                           onclick="return confirm('Delete this purchase invoice?');">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center; color:#ff3333;">
                    No purchase invoices found.
                </td>
            </tr>
        <?php endif; ?>

    </table>
</div>

<?php include "includes/footer.php"; ?>
