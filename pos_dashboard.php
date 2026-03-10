```php
<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

/* ===============================
   TODAY SALES
=================================*/
$todaySales = 0;
$todayInvoices = 0;

$sql = "
    SELECT 
        COUNT(*) as total_invoices,
        IFNULL(SUM(total_amount),0) as total_sales
    FROM pos_invoices
    WHERE DATE(created_at) = CURDATE()
";

$res = $conn->query($sql);
if($row = $res->fetch_assoc()){
    $todaySales = $row['total_sales'];
    $todayInvoices = $row['total_invoices'];
}

/* ===============================
   UNPAID INVOICES
=================================*/
$unpaid = 0;

$sql2 = "SELECT COUNT(*) as unpaid FROM pos_invoices WHERE status='unpaid'";
$res2 = $conn->query($sql2);
if($r = $res2->fetch_assoc()){
    $unpaid = $r['unpaid'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>POS Control Center</title>

<style>

body{
    background:#000;
    color:#fff;
    font-family:Arial;
}

.wrap{
    width:92%;
    margin:30px auto;
}

h1{
    color:#ff2e2e;
}

/* STAT CARDS */

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:40px;
}

.stat-card{
    background:#111;
    padding:25px;
    border-radius:10px;
    border:2px solid #1f1f1f;
}

.stat-card h2{
    margin:0;
    color:#00ff9c;
}

.stat-card p{
    margin:5px 0 0;
    color:#aaa;
}

/* ACTION CARDS */

.grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
}

.card{
    background:#111;
    padding:30px;
    border-radius:10px;
    text-align:center;
    border:2px solid #1f1f1f;
    transition:0.2s;
}

.card:hover{
    border:2px solid #ff2e2e;
    transform:translateY(-6px);
}

.btn{
    display:inline-block;
    margin-top:15px;
    padding:12px 20px;
    background:#b00000;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-weight:bold;
}

.btn:hover{
    background:#ff2e2e;
}

</style>
</head>

<body>

<div class="wrap">

<h1>🛒 POS Command Center</h1>

<!-- ===============================
     LIVE BUSINESS STATS
=================================-->

<div class="stats">

<div class="stat-card">
<h2>R <?php echo number_format($todaySales,2); ?></h2>
<p>Today's Revenue</p>
</div>

<div class="stat-card">
<h2><?php echo $todayInvoices; ?></h2>
<p>Invoices Today</p>
</div>

<div class="stat-card">
<h2><?php echo $unpaid; ?></h2>
<p>Unpaid Invoices</p>
</div>

<div class="stat-card">
<h2>
R <?php 
echo $todayInvoices > 0 
    ? number_format($todaySales/$todayInvoices,2) 
    : "0.00"; 
?>
</h2>
<p>Average Sale</p>
</div>

</div>


<!-- ===============================
     QUICK ACTIONS
=================================-->

<div class="grid">

<div class="card">
<h2>🧾 New Sale</h2>
<p>Create a new invoice</p>
<a class="btn" href="pos_invoice_add_v2.php">Open POS</a>
</div>

<div class="card">
<h2>📄 Invoices</h2>
<p>View all sales</p>
<a class="btn" href="pos_sales_list.php">View Invoices</a>
</div>

<div class="card">
<h2>📊 Sales Report</h2>
<p>Track category performance</p>
<a class="btn" href="sales_report.php">Open Report</a>
</div>

<div class="card">
<h2>⚠ Stock Alerts</h2>
<p>Items running low</p>
<a class="btn" href="stock_alerts_center.php">Check Stock</a>
</div>

</div>

</div>

</body>
</html>
```
