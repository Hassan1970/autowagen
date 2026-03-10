<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$sql = "
    SELECT m.*, p.part_name, p.oem_number,
           i.invoice_number, i.invoice_date
    FROM oem_stock_movements m
    LEFT JOIN oem_parts p ON p.id = m.part_id
    LEFT JOIN supplier_oem_invoices i ON i.id = m.invoice_id
    ORDER BY m.id DESC
    LIMIT 500
";
$res = $conn->query($sql);
?>
<style>
.page-wrap {
    width: 95%;
    margin: 20px auto;
    background: #111;
    padding: 20px;
    border-radius: 8px;
    border: 2px solid #b00000;
    color: white;
}
.page-title {
    color: #ff3333;
    text-align: center;
    margin-bottom: 20px;
}
.table-wrap { overflow-x: auto; }
.table {
    width: 100%;
    border-collapse: collapse;
}
.table th, .table td {
    border: 1px solid #333;
    padding: 6px;
    font-size: 13px;
}
.table th {
    background: #b00000;
    color: white;
}
.table tr:nth-child(even) { background: #181818; }
$table tr:nth-child(odd) { background: #101010; }
.btn {
    background: #b00000;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
}
.btn:hover { background: #ff3333; }
</style>

<div class="page-wrap">
    <h2 class="page-title">OEM Stock Movements</h2>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Part</th>
                    <th>Movement</th>
                    <th>Qty</th>
                    <th>Invoice</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($m = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><?= $m['created_at'] ?></td>
                    <td><?= h($m['part_name']) ?> (<?= h($m['oem_number']) ?>)</td>
                    <td><?= $m['movement_type'] ?></td>
                    <td><?= $m['qty'] ?></td>
                    <td>
                        <?php if ($m['invoice_id']): ?>
                            <a href="oem_purchase_view.php?id=<?= $m['invoice_id'] ?>" class="btn">
                                <?= $m['invoice_number'] ?>
                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= h($m['reference']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
