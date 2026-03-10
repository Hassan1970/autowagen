<?php
require_once __DIR__ . '/config/config.php';

// DOMPDF (installed via Composer)
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid vehicle ID");
}

// VEHICLE
$vRes = $conn->query("SELECT * FROM vehicles WHERE id = $id LIMIT 1");
$vehicle = $vRes ? $vRes->fetch_assoc() : null;
if (!$vehicle) {
    die("Vehicle not found");
}

// PURCHASE
$pRes = $conn->query("SELECT * FROM vehicle_purchases WHERE vehicle_id = $id LIMIT 1");
$purchase = $pRes ? $pRes->fetch_assoc() : null;

// CUSTOMER
$customer = null;
if ($purchase && !empty($purchase['customer_id'])) {
    $cid = (int)$purchase['customer_id'];
    $cRes = $conn->query("SELECT * FROM customers WHERE id = $cid LIMIT 1");
    $customer = $cRes ? $cRes->fetch_assoc() : null;
}

// PHOTOS (show first 4)
$photosRes = $conn->query("SELECT * FROM vehicle_photos WHERE vehicle_id = $id ORDER BY sort_order ASC, id ASC LIMIT 4");

$stockCode = $vehicle['stock_code'] ?? ('V-' . $id);

// Build HTML
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vehicle Profile PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        h2 {
            font-size: 14px;
            margin:15px 0 5px;
        }
        table {
            width:100%;
            border-collapse:collapse;
            margin-bottom:5px;
        }
        table td {
            border:1px solid #ccc;
            padding:4px 6px;
            vertical-align:top;
        }
        .section {
            margin-bottom:10px;
        }
        .small {
            color:#666;
        }
        .photos-row {
            display:flex;
            flex-wrap:wrap;
            gap:5px;
        }
        .photos-row img {
            width:120px;
            height:80px;
            object-fit:cover;
            border:1px solid #ccc;
        }
    </style>
</head>
<body>
<h1>Vehicle Profile — <?php echo h($stockCode); ?></h1>
<p class="small">Generated on <?php echo date('Y-m-d H:i'); ?></p>

<div class="section">
    <h2>Vehicle Details</h2>
    <table>
        <tr><td>Stock Code</td><td><?php echo h($vehicle['stock_code']); ?></td></tr>
        <tr><td>Make</td><td><?php echo h($vehicle['make']); ?></td></tr>
        <tr><td>Model</td><td><?php echo h($vehicle['model']); ?></td></tr>
        <tr><td>Brand Name</td><td><?php echo h($vehicle['brand_name']); ?></td></tr>
        <tr><td>Variant</td><td><?php echo h($vehicle['variant']); ?></td></tr>
        <tr><td>Year</td><td><?php echo h($vehicle['year']); ?></td></tr>
        <tr><td>Colour</td><td><?php echo h($vehicle['colour']); ?></td></tr>
        <tr><td>Number of Doors</td><td><?php echo h($vehicle['number_doors']); ?></td></tr>
        <tr><td>VIN / Chassis</td><td><?php echo h($vehicle['vin_number']); ?></td></tr>
        <tr><td>Engine</td><td><?php echo h($vehicle['engine']); ?></td></tr>
        <tr><td>Engine Number</td><td><?php echo h($vehicle['engine_number']); ?></td></tr>
        <tr><td>Engine Capacity</td><td><?php echo h($vehicle['engine_capacity']); ?></td></tr>
        <tr><td>Fuel Type</td><td><?php echo h($vehicle['fuel_type']); ?></td></tr>
        <tr><td>Transmission</td><td><?php echo h($vehicle['transmission']); ?></td></tr>
        <tr><td>Mileage (km)</td><td><?php echo h($vehicle['mileage']); ?></td></tr>
        <tr><td>Purchase Use</td><td><?php echo h($vehicle['purchase_use']); ?></td></tr>
    </table>
</div>

<div class="section">
    <h2>Customer / Seller Details</h2>
    <?php if (!$customer): ?>
        <p>No customer record linked.</p>
    <?php else: ?>
        <table>
            <tr><td>Full Name / Purchase From</td><td><?php echo h($customer['full_name']); ?></td></tr>
            <tr><td>Address</td><td><?php echo nl2br(h($customer['address'])); ?></td></tr>
        </table>
    <?php endif; ?>
</div>

<div class="section">
    <h2>Purchase Details</h2>
    <?php if (!$purchase): ?>
        <p>No purchase record found.</p>
    <?php else: ?>
        <table>
            <tr><td>Receipt No</td><td><?php echo h($purchase['receipt_no']); ?></td></tr>
            <tr><td>Date Purchased</td><td><?php echo h($purchase['date_purchased']); ?></td></tr>
            <tr><td>Notes</td><td><?php echo nl2br(h($purchase['notes'])); ?></td></tr>
        </table>
    <?php endif; ?>
</div>

<div class="section">
    <h2>Vehicle Photos</h2>
    <?php if (!$photosRes || $photosRes->num_rows == 0): ?>
        <p>No photos uploaded.</p>
    <?php else: ?>
        <div class="photos-row">
            <?php while ($ph = $photosRes->fetch_assoc()):
                $path = __DIR__ . '/uploads/vehicles/photos/' . $ph['file_name'];
                if (!file_exists($path)) continue;
                $data = base64_encode(file_get_contents($path));
                $mime = 'image/jpeg';
            ?>
                <img src="data:<?php echo $mime; ?>;base64,<?php echo $data; ?>" />
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream('vehicle_'.$id.'_profile.pdf', ['Attachment' => true]);
exit;
