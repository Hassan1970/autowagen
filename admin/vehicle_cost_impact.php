<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';

$res = $conn->query("
    SELECT 
        CONCAT(v.stock_code,' ',v.make,' ',v.model,' ',v.year) AS vehicle,
        SUM(tpp.cost_incl) AS total_spend
    FROM third_party_parts tpp
    JOIN vehicles v ON v.id = tpp.vehicle_id
    GROUP BY v.id
    ORDER BY total_spend DESC
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
.col-vehicle{ width:70%; }
.col-total{ width:30%; }
.total-red{
    color:#ff4444;
    font-weight:bold;
}
</style>

<div class="admin-wrap">
    <h1 class="admin-title">Vehicle Cost Impact (3rd-Party Parts)</h1>

    <table class="admin-table">
        <thead>
            <tr>
                <th class="col-vehicle">Vehicle</th>
                <th class="col-total text-right">Third-Party Spend</th>
            </tr>
        </thead>
        <tbody>
            <?php while($r=$res->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($r['vehicle'])?></td>
                <td class="text-right total-red">
                    R <?=number_format($r['total_spend'],2)?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
