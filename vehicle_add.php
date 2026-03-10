<?php
require_once __DIR__ . '/config/config.php';
$page_title = "Add Vehicle";
include __DIR__ . '/includes/header.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors  = [];
$success = "";

// helper for sticky values using global h()
function f($name) {
    return h($_POST[$name] ?? '');
}
?>

<style>
.page-wrap {
    width: 92%;
    margin: 20px auto 40px;
}
.page-title {
    margin: 10px 0 5px 0;
    color: #ff3333;
}
.page-subtitle {
    color:#ccc;
    font-size:13px;
    margin-bottom:15px;
}
.card {
    background:#151515;
    border:1px solid #b00000;
    border-radius:8px;
    padding:15px 18px;
    margin-bottom:18px;
}
.card h2 {
    margin:0 0 10px 0;
    color:#ff5555;
    font-size:16px;
}
.card small {
    color:#aaa;
}
.form-grid {
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}
.form-col {
    flex:1 1 280px;
}
label {
    display:block;
    margin-top:8px;
    margin-bottom:3px;
    font-size:13px;
}
input[type=text],
input[type=number],
input[type=date],
textarea,
select {
    width:100%;
    padding:7px 8px;
    border-radius:5px;
    border:1px solid #444;
    background:#000;
    color:#fff;
    font-size:13px;
    box-sizing:border-box;
}
textarea {
    min-height:70px;
    resize:vertical;
}
.hint {
    font-size:11px;
    color:#aaa;
}
.btn-main {
    background:#b00000;
    color:#fff;
    border:none;
    padding:8px 16px;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
    font-size:13px;
}
.btn-main:hover {
    background:#ff1a1a;
}
.btn-secondary-small {
    background:#222;
    color:#fff;
    border:1px solid #555;
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:12px;
}
@media (max-width: 900px) {
    .form-col {
        flex:1 1 100%;
    }
}
</style>

<div class="page-wrap">

    <h1 class="page-title">Add Vehicle</h1>
    <p class="page-subtitle">
        Capture customer / seller details, vehicle info, photos and papers on a single page.
    </p>

    <form method="post" enctype="multipart/form-data">

        <!-- CUSTOMER CARD -->
        <div class="card">
            <h2>Customer / Seller Details</h2>
            <small>Required for second-hand vehicle purchase records.</small>

            <div class="form-grid">
                <div class="form-col">
                    <label>Customer Full Name *</label>
                    <input type="text" name="customer_name">

                    <label>Customer Address *</label>
                    <textarea name="customer_address"></textarea>
                </div>

                <div class="form-col">
                    <label>ID Document</label>
                    <input type="file" name="customer_id_doc">

                    <label>Proof of Residence</label>
                    <input type="file" name="customer_proof_res">
                </div>

                <div class="form-col">
                    <label>Date Purchased</label>
                    <input type="date" name="date_purchased">

                    <label>Customer Receipt No</label>
                    <input type="text" name="receipt_no">
                </div>
            </div>
        </div>

        <!-- VEHICLE CARD -->
        <div class="card">
            <h2>Vehicle Details</h2>

            <div class="form-grid">
                <div class="form-col">
                    <label>Stock Code *</label>
                    <input type="text" name="stock_code">

                    <label>VIN / Chassis Number</label>
                    <input type="text" name="vin_number">

                    <label>Engine Number</label>
                    <input type="text" name="engine_number">

                    <label>Engine Capacity</label>
                    <input type="text" name="engine_capacity">

                    <label>Engine</label>
                    <input type="text" name="engine">
                </div>

                <div class="form-col">
                    <label>Make *</label>
                    <input type="text" name="make">

                    <label>Model *</label>
                    <input type="text" name="model">

                    <label>Brand / Name</label>
                    <input type="text" name="brand_name">

                    <label>Variant</label>
                    <input type="text" name="variant">

                    <label>Year</label>
                    <input type="number" name="year">
                </div>

                <div class="form-col">
                    <label>Colour</label>
                    <input type="text" name="colour">

                    <label>Mileage (km)</label>
                    <input type="number" name="mileage">

                    <label>Fuel Type</label>
                    <select name="fuel_type">
                        <option value="">-- Select --</option>
                        <option>Petrol</option>
                        <option>Diesel</option>
                        <option>Hybrid</option>
                        <option>Electric</option>
                    </select>

                    <label>Transmission</label>
                    <select name="transmission">
                        <option value="">-- Select --</option>
                        <option>Manual</option>
                        <option>Automatic</option>
                        <option>CVT</option>
                    </select>

                    <label>Number of Doors</label>
                    <input type="number" name="number_doors">

                    <label>Purchase of Vehicle</label>
                    <select name="purchase_use">
                        <option value="">-- Select --</option>
                        <option>Selling</option>
                        <option>Stripping</option>
                        <option>Other</option>
                    </select>
                </div>
            </div>

            <label>Notes</label>
            <textarea name="notes"></textarea>
        </div>

        <!-- PHOTOS -->
        <div class="card">
            <h2>Vehicle Photos (up to 7)</h2>
            <input type="file" name="photos[]" multiple>
        </div>

        <!-- PAPERS -->
        <div class="card">
            <h2>Vehicle Papers (up to 5)</h2>
            <input type="file" name="papers[]" multiple>
        </div>

        <button type="submit" class="btn-main">Save Vehicle</button>
        <a href="vehicles_list.php" class="btn-secondary-small">Cancel</a>

    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
