<?php
require_once __DIR__ . '/config/config.php';
$page_title = "Add Vehicle";
include __DIR__ . '/includes/header.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];

/* =========================
   SAVE VEHICLE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['stock_code'])) $errors[] = "Stock Code is required";
    if (empty($_POST['make'])) $errors[] = "Make is required";
    if (empty($_POST['model'])) $errors[] = "Model is required";

    if (empty($errors)) {

        $stock_code = trim($_POST['stock_code']);
        $vin_number = trim($_POST['vin_number']);
        $engine_number = trim($_POST['engine_number']);
        $engine_capacity = trim($_POST['engine_capacity']);
        $engine = trim($_POST['engine']);

        $make = strtoupper(trim($_POST['make']));
        $model = strtoupper(trim($_POST['model']));
        $brand_name = trim($_POST['brand_name']);
        $variant = trim($_POST['variant']);
        $year = !empty($_POST['year']) ? (int)$_POST['year'] : NULL;

        $colour = trim($_POST['colour']);
        $mileage = !empty($_POST['mileage']) ? (int)$_POST['mileage'] : NULL;

        $fuel_type = $_POST['fuel_type'] ?: NULL;
        $transmission = $_POST['transmission'] ?: NULL;
        $number_doors = !empty($_POST['number_doors']) ? (int)$_POST['number_doors'] : NULL;

        $purchase_use = $_POST['purchase_use'] ?: NULL;
        $notes = trim($_POST['notes']);

        /* PHOTO */
        $photo_main = NULL;

        if (!empty($_FILES['photos']['name'][0])) {

            $upload_dir = __DIR__ . "/uploads/vehicles/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = time() . "_" . basename($_FILES['photos']['name'][0]);
            $target = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['photos']['tmp_name'][0], $target)) {
                $photo_main = $filename;
            }
        }

        /* INSERT */
        $stmt = $conn->prepare("
            INSERT INTO vehicles (
                stock_code, vin_number, engine_number, engine_capacity, engine,
                make, model, brand_name, variant, year,
                fuel_type, transmission, number_doors,
                colour, mileage,
                purchase_use, notes, photo_main
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssiississss",
            $stock_code,
            $vin_number,
            $engine_number,
            $engine_capacity,
            $engine,
            $make,
            $model,
            $brand_name,
            $variant,
            $year,
            $fuel_type,
            $transmission,
            $number_doors,
            $colour,
            $mileage,
            $purchase_use,
            $notes,
            $photo_main
        );

        if ($stmt->execute()) {
            header("Location: vehicles_list.php?success=1");
            exit;
        } else {
            $errors[] = "DB Error: " . $stmt->error;
        }
    }
}
?>

<style>
.page-wrap { width: 92%; margin: 20px auto; }
.page-title { color:#ff3333; }
.card { background:#151515; border:1px solid #b00000; padding:15px; margin-bottom:15px; }
.form-grid { display:flex; flex-wrap:wrap; gap:20px; }
.form-col { flex:1 1 280px; }
label { display:block; margin-top:8px; }
input, textarea, select {
    width:100%;
    padding:7px;
    background:#000;
    color:#fff;
    border:1px solid #444;
}
.btn-main {
    background:#b00000;
    color:#fff;
    padding:8px 16px;
    border:none;
    border-radius:6px;
}
.error-box {
    background:#330000;
    padding:10px;
    border:1px solid red;
    margin-bottom:10px;
}
</style>

<div class="page-wrap">

<h1 class="page-title">Add Vehicle</h1>

<?php if (!empty($errors)): ?>
<div class="error-box">
    <?php foreach ($errors as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<!-- CUSTOMER -->
<div class="card">
<h2>Customer / Seller Details</h2>

<div class="form-grid">
<div class="form-col">
<label>Customer Full Name</label>
<input type="text" name="customer_name">

<label>Customer Address</label>
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

<!-- VEHICLE -->
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

<label>Mileage</label>
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

</form>

</div>

<!-- ✅ ENTER KEY FIX -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    const inputs = form.querySelectorAll("input, select, textarea");

    inputs.forEach((input, index) => {

        input.addEventListener("keydown", function (e) {

            if (e.key === "Enter" && input.tagName !== "TEXTAREA") {
                e.preventDefault();

                let next = inputs[index + 1];
                if (next) {
                    next.focus();
                }
            }

        });

    });

});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
