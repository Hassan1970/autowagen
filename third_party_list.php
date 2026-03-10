<?php
require_once __DIR__ . '/config/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= FETCH INVOICES ================= */
$sql = "
    SELECT
        tpp.third_supplier_id,
        tpp.invoice_number,
        MAX(tpp.invoice_date) AS invoice_date,
        s.supplier_name,
        COUNT(*) AS total_parts,
        SUM(tpp.cost_incl) AS total_cost
    FROM third_party_parts tpp
    INNER JOIN third_party_suppliers s
        ON s.id = tpp.third_supplier_id
    GROUP BY tpp.third_supplier_id, tpp.invoice_number
    ORDER BY invoice_date DESC
";

$result = $conn->query($sql);

include __DIR__ . '/includes/header.php';
?>

<style>
/* ================= PAGE WRAP ================= */
.wrap {
    width: 90%;
    max-width: 1200px;
    margin: 30px auto;
    padding: 20px;
    background: #111;
    border: 2px solid red;
    border-radius: 10px;
}

/* ================= TITLES ================= */
h1 {
    color: #ff3333;
}

/* ================= TABLE ================= */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    padding: 8px;
    border-bottom: 1px solid #333;
}

th {
    background: #222;
    color: #ff3333;
}

/* ================= INVOICE LINK (FIXED COLOR) ================= */
a.invoice-link {
    color: #ffffff;
    text-decoration: none;
    font-weight: 500;
}

a.invoice-link:hover {
    color: #ff3333;
}

/* ================= VIEW BUTTON ================= */
a.btn {
    background: #c00000;
    color: #ffffff;
    padding: 4px 12px;
    border-radius: 4px;
    text-decoration: none;
}
</style>

<div class="wrap">
    <h1>3rd Party Invoices</h1>

    <table>
        <tr>
            <th>Invoice</th>
            <th>Date</th>
            <th>Supplier</th>
            <th>Total Parts</th>
            <th>Total Cost</th>
            <th>Action</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <a class="invoice-link"
                           href="third_party_invoice_view.php?invoice_number=<?= urlencode($row['invoice_number']) ?>&supplier_id=<?= (int)$row['third_supplier_id'] ?>">
                            <?= htmlspecialchars($row['invoice_number']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($row['invoice_date']) ?></td>
                    <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                    <td><?= (int)$row['total_parts'] ?></td>
                    <td>R <?= number_format((float)$row['total_cost'], 2) ?></td>
                    <td>
                        <a class="btn"
                           href="third_party_invoice_view.php?invoice_number=<?= urlencode($row['invoice_number']) ?>&supplier_id=<?= (int)$row['third_supplier_id'] ?>">
                            View
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center;color:#777;">
                    No invoices found
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
