<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

/* FETCH DATA */
$result = $conn->query("
SELECT m.*, p.part_name, p.stock_qty AS current_stock
FROM oem_stock_movements m
LEFT JOIN oem_parts p ON p.id = m.part_id
ORDER BY m.id DESC
");

/* =========================
   STEP 1 — SUMMARY DATA
========================= */

$summary = $conn->query("
SELECT 
COUNT(*) as total_moves,
SUM(qty) as total_qty,
SUM((selling_price - cost_price) * qty) as total_profit
FROM oem_stock_movements
WHERE movement_type='SELL_OUT'
")->fetch_assoc();

$today = date('Y-m-d');

$todayData = $conn->query("
SELECT 
SUM(qty) as today_qty
FROM oem_stock_movements
WHERE movement_type='SELL_OUT'
AND DATE(created_at) = '$today'
")->fetch_assoc();

/* TOTALS */
$totalQty = 0;
$totalProfit = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Stock History</title>

<style>
body{background:#000;color:#fff;font-family:Arial;}

/* ===============================
   STEP 3 — DASHBOARD CARDS CSS
=================================*/
.cards{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:15px;
margin-bottom:20px;
}

.card{
background:#111;
border:1px solid #222;
padding:20px;
text-align:center;
border-radius:6px;
}

.card h4{
margin:0 0 10px;
color:#ccc;
}

.card p{
font-size:22px;
font-weight:bold;
margin:0;
}

/* EXISTING STYLES */
.controls{
display:flex;
gap:10px;
margin-bottom:15px;
flex-wrap:wrap;
}

input, select{
padding:8px;
background:#111;
border:1px solid #333;
color:#fff;
}

button{
background:#c00000;
border:none;
padding:8px 12px;
color:#fff;
cursor:pointer;
}

.table-wrapper{
overflow-x:auto;
}

table{
width:100%;
border-collapse:collapse;
table-layout:fixed;
}

th,td{
padding:10px;
border:1px solid #222;
overflow:hidden;
text-overflow:ellipsis;
white-space:nowrap;
}

th{background:#111;}

.sell{color:#ff4d4d;font-weight:bold;}
.in{color:#00ff99;font-weight:bold;}
.balance{color:#ffaa00;font-weight:bold;}

.profit{
color:#00ffcc;
font-weight:bold;
}

.link{
color:#4da6ff;
cursor:pointer;
text-decoration:underline;
}

/* COLUMN WIDTHS */
th:nth-child(1), td:nth-child(1){width:60px;}
th:nth-child(2), td:nth-child(2){width:150px;}
th:nth-child(3), td:nth-child(3){width:120px;}
th:nth-child(4), td:nth-child(4){width:70px;}
th:nth-child(5), td:nth-child(5){width:120px;}
th:nth-child(6), td:nth-child(6){width:100px;}
th:nth-child(7), td:nth-child(7){width:140px;}
th:nth-child(8), td:nth-child(8){width:120px;}
th:nth-child(9), td:nth-child(9){width:160px;}
th:nth-child(10), td:nth-child(10){width:80px;}

td:nth-child(1),
td:nth-child(4),
td:nth-child(5),
td:nth-child(6),
td:nth-child(10){
text-align:center;
}

tfoot td{
background:#111;
border-top:2px solid #c00000;
font-size:14px;
}
</style>
</head>

<body>

<h2>📦 Stock History</h2>

<!-- =========================
     STEP 2 — CARDS UI
========================= -->

<div class="cards">

<div class="card">
<h4>Total Movements</h4>
<p><?= $summary['total_moves'] ?? 0 ?></p>
</div>

<div class="card">
<h4>Total Qty Sold</h4>
<p><?= $summary['total_qty'] ?? 0 ?></p>
</div>

<div class="card">
<h4>Total Profit</h4>
<p style="color:#00ffcc;">
R <?= number_format($summary['total_profit'] ?? 0,2) ?>
</p>
</div>

<div class="card">
<h4>Today Sales</h4>
<p style="color:#ffaa00;">
<?= $todayData['today_qty'] ?? 0 ?>
</p>
</div>

</div>

<!-- CONTROLS -->
<div class="controls">
<input type="text" id="search" placeholder="Search part...">

<select id="filterType">
<option value="">All</option>
<option value="SELL_OUT">Sold</option>
<option value="INVOICE_IN">Purchased</option>
</select>

<input type="date" id="fromDate">
<input type="date" id="toDate">
<button onclick="filterByDate()">Filter Date</button>

<a href="export_stock_excel.php">
<button>Export Excel</button>
</a>
</div>

<div class="table-wrapper">

<table id="tbl">
<thead>
<tr>
<th>ID</th>
<th>Part</th>
<th>Movement</th>
<th>Qty</th>
<th>Current Stock</th>
<th>Profit</th>
<th>Reference</th>
<th>User</th>
<th>Date</th>
<th>Invoice</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<?php
$sell = (float)($row['selling_price'] ?? 0);
$cost = (float)($row['cost_price'] ?? 0);
$profit = ($sell - $cost) * $row['qty'];

$totalQty += $row['qty'];
$totalProfit += $profit;
?>

<tr>

<td><?= $row['id'] ?></td>
<td><?= $row['part_name'] ?? ('#'.$row['part_id']) ?></td>

<td class="<?= ($row['movement_type']=='SELL_OUT') ? 'sell':'in' ?>">
<?= $row['movement_type'] ?>
</td>

<td><?= $row['qty'] ?></td>
<td class="balance"><?= $row['current_stock'] ?></td>
<td class="profit"><?= number_format($profit,2) ?></td>

<td><?= $row['reference'] ?></td>
<td><?= $row['created_by'] ?></td>
<td><?= $row['created_at'] ?></td>

<td>
<?php
$invoice = '';
if(strpos($row['reference'], 'POS#') === 0){
    $invoice = str_replace('POS#','',$row['reference']);
}
?>

<?php if($invoice): ?>
<span class="link" onclick="openInvoice(<?= $invoice ?>)">
<?= $invoice ?>
</span>
<?php else: ?>
-
<?php endif; ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

<tfoot>
<tr style="font-weight:bold;">
<td></td>
<td colspan="2">TOTAL</td>
<td><?= $totalQty ?></td>
<td></td>
<td class="profit"><?= number_format($totalProfit,2) ?></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>
</tfoot>

</table>

</div>

<script>
// (your existing JS unchanged)
</script>

</body>
</html>