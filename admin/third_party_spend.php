<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';

$res = $conn->query("
    SELECT 
        s.supplier_name,
        SUM(tpp.cost_incl) AS spend
    FROM third_party_parts tpp
    JOIN third_party_suppliers s ON s.id = tpp.third_supplier_id
    GROUP BY s.id
    ORDER BY spend DESC
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
.text-right{ text-align:right; }
.total-red{
    color:#ff4444;
    font-weight:bold;
}
</style>

<div class="admin-wrap">
    <h1 class="admin-title">3rd-Party Supplier Spend</h1>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Supplier</th>
                <th class="text-right">Total Spend</th>
            </tr>
        </thead>
        <tbody>
            <?php while($r=$res->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($r['supplier_name'])?></td>
                <td class="text-right total-red">
                    R <?=number_format($r['spend'],2)?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
