<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';

$res = $conn->query("
    SELECT 
        invoice_number,
        invoice_date,
        COUNT(*) AS items,
        SUM(cost_incl) AS total
    FROM third_party_parts
    GROUP BY invoice_number, invoice_date
    ORDER BY invoice_date DESC
");
?>

<style>
.admin-wrap{
    width:90%;
    max-width:1000px;
    margin:30px auto;
}
.admin-title{
    color:#ff3333;
    margin-bottom:15px;
}
.admin-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    background:#111;
    border:2px solid #b00000;
}
.admin-table th{
    background:#1a1a1a;
    color:#fff;
    padding:10px;
    font-size:13px;
    text-align:left;
    border-bottom:2px solid #b00000;
}
.admin-table td{
    padding:8px 10px;
    font-size:13px;
    color:#eee;
    border-bottom:1px solid #333;
}
.text-center{ text-align:center; }
.text-right{ text-align:right; }
.col-invoice{ width:35%; }
.col-date{ width:20%; }
.col-items{ width:15%; }
.col-total{ width:30%; }
.total-red{
    color:#ff4444;
    font-weight:bold;
}
</style>

<div class="admin-wrap">
    <h1 class="admin-title">Invoice Spend</h1>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="col-invoice">Invoice #</th>
                <th class="col-date text-center">Date</th>
                <th class="col-items text-center">Items</th>
                <th class="col-total text-right">Total (Incl)</th>
            </tr>
        </thead>
        <tbody>
            <?php while($r=$res->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($r['invoice_number'])?></td>
                <td class="text-center"><?=$r['invoice_date']?></td>
                <td class="text-center"><?=$r['items']?></td>
                <td class="text-right total-red">
                    R <?=number_format($r['total'],2)?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
