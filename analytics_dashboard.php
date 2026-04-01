<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* DATE FILTER */
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$where = "WHERE movement_type='SELL_OUT'";
if($from && $to){
    $where .= " AND DATE(created_at) BETWEEN '$from' AND '$to'";
}

/* =========================
   KPI DATA
========================= */

/* TODAY */
$res = $conn->query("
SELECT 
SUM((selling_price - cost_price)*qty) as profit,
SUM(qty) as qty
FROM oem_stock_movements
WHERE movement_type='SELL_OUT'
AND DATE(created_at)=CURDATE()
");
$row = $res->fetch_assoc();
$profitToday = $row['profit'] ?? 0;
$partsToday  = $row['qty'] ?? 0;

/* THIS MONTH */
$res = $conn->query("
SELECT SUM((selling_price - cost_price)*qty) as total
FROM oem_stock_movements
WHERE movement_type='SELL_OUT'
AND MONTH(created_at)=MONTH(CURDATE())
AND YEAR(created_at)=YEAR(CURDATE())
");
$thisMonth = $res->fetch_assoc()['total'] ?? 0;

/* LAST MONTH */
$res = $conn->query("
SELECT SUM((selling_price - cost_price)*qty) as total
FROM oem_stock_movements
WHERE movement_type='SELL_OUT'
AND MONTH(created_at)=MONTH(CURDATE()-INTERVAL 1 MONTH)
AND YEAR(created_at)=YEAR(CURDATE()-INTERVAL 1 MONTH)
");
$lastMonth = $res->fetch_assoc()['total'] ?? 0;

/* CALCULATE */
$change = $thisMonth - $lastMonth;
$percent = ($lastMonth > 0) ? ($change / $lastMonth) * 100 : 0;

$trendIcon  = $change >= 0 ? "▲" : "▼";
$trendColor = $change >= 0 ? "#00ff99" : "#ff4d4d";

/* TOP PART */
$res = $conn->query("
SELECT p.part_name, SUM(m.qty) as total
FROM oem_stock_movements m
LEFT JOIN oem_parts p ON p.id=m.part_id
WHERE m.movement_type='SELL_OUT'
GROUP BY m.part_id
ORDER BY total DESC
LIMIT 1
");
$topPart = $res->fetch_assoc()['part_name'] ?? 'N/A';

/* =========================
   ALERTS ENGINE
========================= */
$alerts = [];

if($percent < -10){
    $alerts[] = "⚠️ Profit dropped more than 10% compared to last month";
}

if($partsToday == 0){
    $alerts[] = "⚠️ No parts sold today";
}

if($thisMonth < 1000){
    $alerts[] = "⚠️ Monthly profit is below target";
}

/* =========================
   AI SUGGESTIONS
========================= */
$suggestions = [];

if($percent < 0){
    $suggestions[] = "💡 Consider reviewing pricing or promotions to boost sales";
}

if($partsToday == 0){
    $suggestions[] = "💡 No sales today — check customer demand or marketing";
}

if($thisMonth < $lastMonth){
    $suggestions[] = "💡 Sales are lower than last month — review stock and pricing strategy";
}

if(empty($suggestions)){
    $suggestions[] = "✅ Business performing well — maintain current strategy";
}

/* =========================
   CHART DATA
========================= */

$monthlyLabels = [];
$monthlyProfit = [];

$res = $conn->query("
SELECT DATE_FORMAT(created_at,'%Y-%m') as label,
SUM((selling_price - cost_price)*qty) as profit
FROM oem_stock_movements
$where
GROUP BY label
ORDER BY label ASC
");

while($r = $res->fetch_assoc()){
    $monthlyLabels[] = $r['label'];
    $monthlyProfit[] = (float)$r['profit'];
}

$weeklyLabels = [];
$weeklyProfit = [];

$res = $conn->query("
SELECT YEARWEEK(created_at,1) as label,
SUM((selling_price - cost_price)*qty) as profit
FROM oem_stock_movements
$where
GROUP BY label
ORDER BY label ASC
");

while($r = $res->fetch_assoc()){
    $weeklyLabels[] = "Week ".$r['label'];
    $weeklyProfit[] = (float)$r['profit'];
}

$topLabels = [];
$topQty = [];

$res = $conn->query("
SELECT p.part_name, SUM(m.qty) as total_qty
FROM oem_stock_movements m
LEFT JOIN oem_parts p ON p.id = m.part_id
$where
GROUP BY m.part_id
ORDER BY total_qty DESC
LIMIT 5
");

while($r = $res->fetch_assoc()){
    $topLabels[] = $r['part_name'] ?? 'Unknown';
    $topQty[] = (int)$r['total_qty'];
}
?>

<style>

.wrap{width:95%;margin:10px auto 30px;}
h1{color:#ff3333;margin:5px 0 15px;}

.filter{margin-bottom:15px;}
.filter input{padding:6px;background:#111;border:1px solid #333;color:#fff;}
.filter button{background:#c00000;border:none;padding:6px 10px;color:#fff;cursor:pointer;}

.kpi-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:20px;
margin-bottom:20px;
}

.kpi{
background:#111;
border:2px solid #c00000;
padding:15px;
border-radius:8px;
text-align:center;
}

.kpi h4{margin:0;color:#ff3333;}
.kpi-value{margin-top:10px;font-size:22px;font-weight:bold;color:#00ff99;}
.kpi-sub{font-size:13px;margin-top:5px;}

/* ALERTS */
.alerts-box{
margin:15px 0;
padding:15px;
border:2px solid #ff4d4d;
background:#1a0000;
border-radius:8px;
}
.alert-item{color:#ff4d4d;font-weight:bold;}

/* AI */
.ai-box{
margin:15px 0;
padding:15px;
border:2px solid #00ff99;
background:#001a0f;
border-radius:8px;
}
.ai-item{color:#00ff99;font-weight:bold;}

.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
gap:20px;
}

.card{
background:#111;
border:2px solid #b00000;
padding:20px;
border-radius:8px;
}

.card h3{color:#ff3333;margin-bottom:10px;}

.card a{
display:block;
margin:6px 0;
color:#fff;
text-decoration:none;
}
.card a:hover{color:#ff3333;}

.chart-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
margin-top:30px;
max-width:1000px;
margin-left:auto;
margin-right:auto;
}

.chart-box{
background:#111;
border:2px solid #b00000;
padding:15px;
border-radius:8px;
}

.chart-box canvas{height:240px !important;}

.toggle{
display:flex;
gap:10px;
margin-bottom:10px;
}

.toggle button{
background:#222;
border:1px solid #444;
color:#fff;
padding:5px 10px;
cursor:pointer;
}

.toggle button.active{background:#c00000;}

</style>

<div class="wrap">

<h1>📊 Analytics Dashboard</h1>

<form class="filter">
From: <input type="date" name="from" value="<?= $from ?>">
To: <input type="date" name="to" value="<?= $to ?>">
<button type="submit">Apply</button>
</form>

<!-- KPI -->
<div class="kpi-grid">

<div class="kpi">
<h4>💰 Profit Today</h4>
<div class="kpi-value">R <?= number_format($profitToday,2) ?></div>
</div>

<div class="kpi">
<h4>📅 Profit This Month</h4>
<div class="kpi-value">R <?= number_format($thisMonth,2) ?></div>
<div class="kpi-sub" style="color:<?= $trendColor ?>">
<?= $trendIcon ?> <?= number_format($percent,1) ?>% vs last month
</div>
</div>

<div class="kpi">
<h4>📦 Parts Sold Today</h4>
<div class="kpi-value"><?= $partsToday ?></div>
</div>

<div class="kpi">
<h4>🏆 Top Part</h4>
<div class="kpi-value"><?= $topPart ?></div>
</div>

</div>

<!-- ALERTS -->
<?php if(!empty($alerts)): ?>
<div class="alerts-box">
<?php foreach($alerts as $a): ?>
<div class="alert-item"><?= $a ?></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- AI -->
<div class="ai-box">
<?php foreach($suggestions as $s): ?>
<div class="ai-item"><?= $s ?></div>
<?php endforeach; ?>
</div>

<!-- CARDS -->
<div class="grid">
<div class="card">
<h3>Sales Analytics</h3>
<a href="admin/category_revenue.php">Category Revenue</a>
<a href="admin/revenue_per_vehicle.php">Revenue per Vehicle</a>
<a href="admin/category_profitability.php">Category Profitability</a>
</div>

<div class="card">
<h3>Inventory Analytics</h3>
<a href="admin/dead_stock.php">Dead Stock Report</a>
<a href="admin/inventory_value.php">Inventory Value</a>
<a href="admin/part_movement_speed.php">Part Movement Speed</a>
</div>

<div class="card">
<h3>Pricing Analytics</h3>
<a href="admin/price_vs_actual.php">Price vs Actual</a>
<a href="admin/pricing_overview.php">Pricing Overview</a>
<a href="admin/pricing_history.php">Pricing History</a>
</div>

<div class="card">
<h3>Vehicle Analytics</h3>
<a href="admin/vehicle_profitability.php">Vehicle Profitability</a>
<a href="admin/vehicle_comparison.php">Vehicle Comparison</a>
</div>
</div>

<!-- CHARTS -->
<div class="chart-grid">

<div class="chart-box">
<h3>📈 Profit Trends</h3>
<div class="toggle">
<button class="active" onclick="setChart('monthly',this)">Monthly</button>
<button onclick="setChart('weekly',this)">Weekly</button>
</div>
<canvas id="profitChart"></canvas>
</div>

<div class="chart-box">
<h3>🏆 Top Selling Parts</h3>
<canvas id="topPartsChart"></canvas>
</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const monthlyLabels = <?= json_encode($monthlyLabels) ?>;
const monthlyData   = <?= json_encode($monthlyProfit) ?>;
const weeklyLabels  = <?= json_encode($weeklyLabels) ?>;
const weeklyData    = <?= json_encode($weeklyProfit) ?>;

let chart = new Chart(document.getElementById('profitChart'), {
type:'line',
data:{labels:monthlyLabels,datasets:[{label:'Profit',data:monthlyData}]}
});

function setChart(type,btn){
document.querySelectorAll('.toggle button').forEach(b=>b.classList.remove('active'));
btn.classList.add('active');

if(type==='monthly'){
chart.data.labels = monthlyLabels;
chart.data.datasets[0].data = monthlyData;
}else{
chart.data.labels = weeklyLabels;
chart.data.datasets[0].data = weeklyData;
}
chart.update();
}

new Chart(document.getElementById('topPartsChart'), {
type:'bar',
data:{
labels:<?= json_encode($topLabels) ?>,
datasets:[{label:'Qty Sold',data:<?= json_encode($topQty) ?>}]
}
});
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>