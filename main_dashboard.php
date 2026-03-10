<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

// Quick counts
$cntVehicles = $conn->query("SELECT COUNT(*) AS c FROM vehicles")->fetch_assoc()['c'] ?? 0;
$cntOEMParts = $conn->query("SELECT COUNT(*) AS c FROM parts")->fetch_assoc()['c'] ?? 0;
$cntThird    = $conn->query("SELECT COUNT(*) AS c FROM third_party_parts")->fetch_assoc()['c'] ?? 0;
$cntSup      = $conn->query("SELECT COUNT(*) AS c FROM suppliers")->fetch_assoc()['c'] ?? 0;
$cntRepl     = $conn->query("SELECT COUNT(*) AS c FROM replacement_parts")->fetch_assoc()['c'] ?? 0;
$cntStrip    = $conn->query("SELECT COUNT(*) AS c FROM vehicle_stripped_parts")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Main Dashboard</title>

<style>
    body {
        background:#000;
        color:#fff;
        font-family:Arial, sans-serif;
    }

    .dashboard-wrap {
        width:95%;
        margin:30px auto;
    }

    /* GRID SYSTEM */
    .box-row {
        display:grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap:25px;
        margin-bottom:35px;
    }

    /* BOX DESIGN */
    .info-box {
        background:#111;
        border:2px solid #b00000;
        border-radius:10px;
        padding:20px;
        min-height:150px;
        transition:0.2s;
    }

    .info-box:hover {
        border-color:#ff3333;
    }

    .info-box h3 {
        color:#ff3333;
        margin:0 0 15px;
        font-size:20px;
    }

    .info-box .count {
        font-size:40px;
        margin-bottom:20px;
        color:#ffffff;
    }

    /* Center action links */
    .links {
        text-align:center;
        margin-top:10px;
    }

    .links a {
        color:white !important;
        font-size:14px;
        text-decoration:none;
        margin:0 10px;
    }

    .links a:hover {
        color:#ff3333 !important;
        text-decoration:underline;
    }
</style>

</head>
<body>

<div class="dashboard-wrap">

    <h1>Main Dashboard</h1>
    <p>Clean rebuild version. If you see this, the project is connected correctly.</p>

    <!-- ===================== FIRST ROW ===================== -->
    <div class="box-row">

        <!-- Vehicles -->
        <div class="info-box">
            <h3>Vehicles</h3>
            <div class="count"><?= $cntVehicles ?></div>
            <div class="links">
                <a href="vehicles_list.php">View Vehicles</a> |
                <a href="vehicle_add.php">+ Add Vehicle</a>
            </div>
        </div>

        <!-- OEM Parts -->
        <div class="info-box">
            <h3>OEM Parts</h3>
            <div class="count"><?= $cntOEMParts ?></div>
            <div class="links">
                <a href="oem_parts_list.php">View OEM Parts</a> |
                <a href="oem_purchase_add.php">+ Add OEM Purchase</a> |
                <a href="oem_purchases_list.php">OEM Purchases</a>
            </div>
        </div>

        <!-- 3rd Party Parts -->
        <div class="info-box">
            <h3>3rd Party Parts</h3>
            <div class="count"><?= $cntThird ?></div>
            <div class="links">
                <a href="third_party_list.php">View 3rd Party Parts</a>
            </div>
        </div>

        <!-- Suppliers -->
        <div class="info-box">
            <h3>Suppliers</h3>
            <div class="count"><?= $cntSup ?></div>
            <div class="links">
                <a href="suppliers_list.php">View Suppliers</a> |
                <a href="supplier_add.php">+ Add Supplier</a>
            </div>
        </div>

    </div>

    <!-- ===================== SECOND ROW ===================== -->
    <div class="box-row">

        <!-- Replacement Parts -->
        <div class="info-box">
            <h3>Replacement Parts</h3>
            <div class="count"><?= $cntRepl ?></div>
            <div class="links">
                <a href="replacement_parts_list.php">View Replacement Parts</a> |
                <a href="add_replacement_part.php">+ Add Replacement Part</a>
            </div>
        </div>

        <!-- Stripping A Vehicle -->
        <div class="info-box">
            <h3>Stripping a Vehicle</h3>
            <div class="count"><?= $cntStrip ?></div>
            <div class="links">
                <a href="vehicle_stripping_view.php">Stripped Parts</a> |
                <a href="vehicle_stripped_entry.php">+ Strip Vehicle</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>
