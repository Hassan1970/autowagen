<?php
if (!isset($page_title)) {
    $page_title = 'Autowagen Master';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($page_title ?? '') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>

body{
margin:0;
font-family:Arial,Helvetica,sans-serif;
background:#000;
color:#fff;
}

header{
background:#000;
border-bottom:2px solid red;
}

nav{
display:flex;
gap:18px;
padding:12px 20px;
align-items:center;
}

nav a{
color:#fff;
text-decoration:none;
font-weight:bold;
padding:6px 10px;
cursor:pointer;
}

nav a:hover{
background:#b00000;
border-radius:4px;
}

/* DROPDOWN */

.dropdown{
position:relative;
}

.dropdown-menu{
display:none;
position:absolute;
top:38px;
left:0;
background:#111;
border:1px solid red;
min-width:230px;
z-index:9999;
}

.dropdown-menu a{
display:block;
padding:10px 14px;
}

.dropdown-menu a:hover{
background:#b00000;
}

.dropdown.open .dropdown-menu{
display:block;
}

</style>

<script>

document.addEventListener('DOMContentLoaded', function () {

document.querySelectorAll('.dropdown > a').forEach(function (trigger) {

trigger.addEventListener('click', function (e) {

e.preventDefault();

let parent = this.parentElement;

document.querySelectorAll('.dropdown').forEach(d => {
if (d !== parent) d.classList.remove('open');
});

parent.classList.toggle('open');

});

});

document.addEventListener('click', function (e) {

if (!e.target.closest('.dropdown')) {
document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));
}

});

});

</script>

</head>

<body>

<header>

<nav>

<a href="dashboard.php">Dashboard</a>

<a href="vehicles_list.php">Vehicles</a>

<a href="oem_purchase_add.php">OEM Parts</a>

<!-- 3RD PARTY DROPDOWN -->

<div class="dropdown">

<a>3rd Party Parts ▾</a>

<div class="dropdown-menu">

<a href="third_party_entry.php">Add 3rd Party Part</a>

<a href="third_party_list.php">3rd Party Parts List</a>

</div>

</div>

<a href="suppliers_list.php">Suppliers</a>

<!-- STRIPPED INVENTORY -->

<div class="dropdown">

<a>Stripped Inventory ▾</a>

<div class="dropdown-menu">

<a href="stripped_list.php">Inventory List</a>

<a href="inventory.php">Master Inventory</a>

<a href="vehicles_parts_tree.php">Vehicle Tree</a>

<a href="low_stock_alerts.php">Low Stock Alerts</a>

</div>

</div>

<!-- YARD LOCATIONS -->

<div class="dropdown">

<a>Yard Locations ▾</a>

<div class="dropdown-menu">

<a href="yard_locations.php">View Locations</a>

<a href="yard_locations_manage.php">Manage Locations</a>

<a href="yard_map.php">Yard Map</a>

</div>

</div>

<a href="pos_dashboard.php">POS / Sales</a>

<a href="analytics_dashboard.php">Analytics</a>

<a href="sales_report.php">Sales Report</a>

<a href="inventory_dashboard.php">Inventory Dashboard</a>

</nav>

</header>