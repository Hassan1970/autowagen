<?php
// Correct path: header.php is inside /includes so go UP one folder
require_once __DIR__ . "/../config/config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Autowagen Master</title>

<style>
body { background:#000; color:white; margin:0; font-family:Arial; }
.topnav { display:flex; background:#111; padding:10px; border-bottom:2px solid red; }
.topnav a, .dropdown-btn { color:white; padding:10px 16px; text-decoration:none; font-weight:bold; cursor:pointer; }
.topnav a:hover, .dropdown-btn:hover { background:red; }
.dropdown { position:relative; }
.dropdown-content { display:none; position:absolute; background:#111; border:1px solid red; min-width:220px; z-index:9999; }
.dropdown-content a { display:block; padding:10px; color:white; }
.dropdown-content a:hover { background:red; }
.dropdown:hover .dropdown-content { display:block; }
</style>
</head>
<body>

<div class="topnav">
    <a href="dashboard.php">🏠 Dashboard</a>

    <div class="dropdown">
        <div class="dropdown-btn">🚗 Vehicles</div>
        <div class="dropdown-content">
            <a href="vehicle_add.php">Add Vehicle</a>
            <a href="vehicles_list.php">Vehicle List</a>
        </div>
    </div>

    <div class="dropdown">
        <div class="dropdown-btn">🔧 OEM Parts</div>
        <div class="dropdown-content">
            <a href="oem_part_add.php">Add OEM Part</a>
            <a href="oem_parts_list.php">OEM Parts List</a>
            <a href="oem_purchase_add.php">Add Purchase Invoice</a>
            <a href="oem_purchase_list.php">Purchase List</a>
            <a href="supplier_add.php">Add Supplier</a>
            <a href="suppliers_list.php">Supplier List</a>
        </div>
    </div>

    <div class="dropdown">
        <div class="dropdown-btn">🛠 3rd Party Parts</div>
        <div class="dropdown-content">
            <a href="third_party_entry.php">Add 3rd Party Part</a>
            <a href="third_party_list.php">3rd Party Parts List</a>
        </div>
    </div>

    <a href="suppliers_list.php">📦 Suppliers</a>

    <div class="dropdown">
        <div class="dropdown-btn">🧰 Stripped Inventory</div>
        <div class="dropdown-content">
            <a href="vehicle_stripping_select.php">Strip a Vehicle</a>
            <a href="stripped_inventory_list.php">Stripped Parts Inventory</a>
            <a href="vehicle_stripping_list.php">Stripped Vehicles</a>
        </div>
    </div>

    <div class="dropdown">
        <div class="dropdown-btn">🛒 POS / Sales</div>
        <div class="dropdown-content">
            <a href="pos_invoice_add.php">Create Invoice</a>
            <a href="pos_invoice_list.php">Invoice List</a>
        </div>
    </div>

    <!-- Sales Report Link -->
    <a href="sales_report.php" class="sales-report">📊 Sales Report</a>

</div>
</body>
</html>
