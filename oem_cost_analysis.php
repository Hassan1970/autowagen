<?php
require_once "config/config.php";
include "includes/header.php";

if (!function_exists('h')) {
    function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

$from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-90 days'));
$to   = isset($_GET['to'])   ? $_GET['to']   : date('Y-m-d');

$from_dt = $from;
$to_dt   = $to;

// Spend per supplier
$sqlSupplier = "
    SELECT 
        s.id,
        s.supplier_name,
        COUNT(DISTINCT soii.id) AS item_count,
        SUM(soii.line_total) AS total_spend
    FROM supplier_oem_invoice_items soii
    INNER JOIN supplier_oem_invoices soi ON soi.id = soii.invoice_id
    INNER JOIN suppliers s ON s.id = soi.supplier_id
    WHERE soi.invoice_date BETWEEN ? AND ?
    GROUP BY s.id, s.supplier_name
    ORDER BY total_spend DESC
";

$stmt1 = $conn->prepare($sqlSupplier);
$stmt1->bind_param("ss", $from_dt, $to_dt);
$stmt1->execute();
$resSupplier = $stmt1->get_result();

$total_spend_all = 0;
$suppliersData = [];
while ($row = $resSupplier->fetch_assoc()) {
    $suppliersData[] = $row;
    $total_spend_all += $row['total_spend'];
}

// Spend per invoice (drilldown)
$sqlInvoices = "
    SELECT 
        soi.id,
        soi.invoice_number,
        soi.invoice_date,
        s.supplier_name,
        soi.total_amount,
        soi.account_type
    FROM supplier_oem_invoices soi
    INNER JOIN suppliers s ON s.id = soi.supplier_id
    WHERE soi.invoice_date BETWEEN ? AND ?
    ORDER BY soi.invoice_date DESC, soi.id DESC
";

$stmt2 = $conn->prepare($sqlInvoices);
$stmt2->bind_param("ss", $from_dt, $to_dt);
$stmt2->execute();
$resInvoices = $stmt2->get_result();
?>
<style>
.page {
    width:95%;
    margin:20px auto;
    background:#111;
    border:2px solid #b00000;
    color:#fff;
    padding:20px;
    border-radius:8px;
}
.page h2 {
    text-align:center;
    color:#ff3333;
    margin-bottom:20px;
}
.page form {
    display:flex;
    gap:10px;
    justify-content:center;
    flex-wrap:wrap;
    margin-bottom:15px;
}
.page label {
    color:#ff6666;
    font-weight:bold;
}
.page input[type="date"] {
    background:#222;
    color:#fff;
    border:1px solid #444;
    padding:5px 8px;
    border-radius:4px;
}
.page button {
    background:#b00000;
    color:#fff;
    border:none;
    padding:6px 12px;
    border-radius:5px;
    cursor:pointer;
}
.page button:hover {
    background:#ff3333;
}
.summary {
    margin:10px 0 20px;
}
.summary strong {
    color:#ffaaaa;
}
table.report {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    font-size:13px;
}
table.report th, table.report td {
    border:1px solid #333;
    padding:6px 8px;
}
table.report th {
    background:#222;
    color:#ffdddd;
}
table.report tr:nth-child(even) { background:#181818; }
table.report tr:nth-child(odd) { background:#151515; }
td.num { text-align:right; }
.section-title {
    margin-top:20px;
    font-size:16px;
    color:#ff6666;
}
.badge-account {
    padding:2px 6px;
    border-radius:4px;
    font-size:11px;
    background:#222;
    border:1px solid #444;
}
</style>

<div class="page">
    <h2>OEM Cost Analysis</h2>

    <form method="get">
        <label>From:
            <input type="date" name="from" value="<?php echo h($from); ?>">
        </label>
        <label>To:
            <input type="date" name="to" value="<?php echo h($to); ?>">
        </label>
        <button type="submit">Filter</button>
    </form>

    <div class="summary">
        <p>Total OEM spend in this period: 
            <strong>R <?php echo number_format($total_spend_all, 2); ?></strong>
        </p>
    </div>

    <div class="section-title">Spend by Supplier</div>
    <?php if (empty($suppliersData)): ?>
        <p>No OEM invoice items in this period.</p>
    <?php else: ?>
        <table class="report">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Item Count</th>
                    <th>Total Spend</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($suppliersData as $s): 
                $pct = $total_spend_all > 0 ? ($s['total_spend'] / $total_spend_all * 100) : 0;
            ?>
                <tr>
                    <td><?php echo h($s['supplier_name']); ?></td>
                    <td class="num"><?php echo (int)$s['item_count']; ?></td>
                    <td class="num">R <?php echo number_format($s['total_spend'], 2); ?></td>
                    <td class="num"><?php echo number_format($pct, 1); ?> %</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="section-title">Invoices (Header View)</div>
    <?php if ($resInvoices->num_rows === 0): ?>
        <p>No OEM invoices in this period.</p>
    <?php else: ?>
        <table class="report">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Supplier</th>
                    <th>Account Type</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($inv = $resInvoices->fetch_assoc()): ?>
                <tr>
                    <td><?php echo h($inv['invoice_date']); ?></td>
                    <td><?php echo h($inv['invoice_number']); ?></td>
                    <td><?php echo h($inv['supplier_name']); ?></td>
                    <td><span class="badge-account"><?php echo h($inv['account_type']); ?></span></td>
                    <td class="num">
                        <?php echo $inv['total_amount'] !== null 
                            ? 'R ' . number_format($inv['total_amount'], 2)
                            : '-'; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php
$stmt1->close();
$stmt2->close();
$conn->close();
