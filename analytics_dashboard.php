<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";
?>

<style>

.wrap{
width:90%;
margin:30px auto;
}

h1{
color:#ff3333;
margin-bottom:25px;
}

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

.card h3{
margin-top:0;
color:#ff3333;
}

.card a{
display:block;
margin:6px 0;
color:#fff;
text-decoration:none;
}

.card a:hover{
color:#ff3333;
}

</style>

<div class="wrap">

<h1>Analytics Dashboard</h1>

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

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>