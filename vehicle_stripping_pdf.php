<?php
require_once "config/config.php";

require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);
if ($vehicle_id <= 0) {
    die("Invalid vehicle ID.");
}

// vehicle
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehicle) die("Vehicle not found.");

// parts
$sql = "
SELECT sp.*, 
    c.name AS category_name,
    sc.name AS subcategory_name,
    t.name AS type_name,
    comp.name AS component_name
FROM vehicle_stripped_parts sp
LEFT JOIN categories c ON sp.category_id = c.id
LEFT JOIN subcategories sc ON sp.subcategory_id = sc.id
LEFT JOIN types t ON sp.type_id = t.id
LEFT JOIN components comp ON sp.component_id = comp.id
WHERE sp.vehicle_id = ?
ORDER BY sp.id ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$parts = $stmt->get_result();

// build HTML
ob_start();
?>
<html>
<head>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size:11px; }
h1 { font-size:18px; color:#900; margin-bottom:5px; }
h2 { font-size:14px; color:#900; margin-top:15px; }
table { width:100%; border-collapse:collapse; margin-top:8px; }
th, td { border:1px solid #555; padding:4px; }
th { background:#eee; }
.small { font-size:10px; }
</style>
</head>
<body>

<h1>Vehicle Stripping Report</h1>
<p class="small">Generated: <?= date('Y-m-d H:i') ?></p>

<h2>Vehicle Details</h2>
<table>
<tr><th>Stock Code</th><td><?= htmlspecialchars($vehicle['stock_code']) ?></td></tr>
<tr><th>Year</th><td><?= htmlspecialchars($vehicle['year']) ?></td></tr>
<tr><th>Make</th><td><?= htmlspecialchars($vehicle['make']) ?></td></tr>
<tr><th>Model</th><td><?= htmlspecialchars($vehicle['model']) ?></td></tr>
<tr><th>VIN</th><td><?= htmlspecialchars($vehicle['vin_number']) ?></td></tr>
<tr><th>Engine No</th><td><?= htmlspecialchars($vehicle['engine_number']) ?></td></tr>
<tr><th>Mileage</th><td><?= htmlspecialchars($vehicle['mileage']) ?></td></tr>
</table>

<h2>Stripped Parts</h2>
<?php if ($parts->num_rows == 0): ?>
<p>No stripped parts recorded.</p>
<?php else: ?>
<table>
    <tr>
        <th>#</th>
        <th>Category</th>
        <th>Subcategory</th>
        <th>Type</th>
        <th>Component</th>
        <th>Part Name</th>
        <th>Qty</th>
        <th>Condition</th>
        <th>Location</th>
        <th>Notes</th>
    </tr>
    <?php while ($p = $parts->fetch_assoc()): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['category_name']) ?></td>
        <td><?= htmlspecialchars($p['subcategory_name']) ?></td>
        <td><?= htmlspecialchars($p['type_name']) ?></td>
        <td><?= htmlspecialchars($p['component_name']) ?></td>
        <td><?= htmlspecialchars($p['part_name']) ?></td>
        <td><?= (int)$p['qty'] ?></td>
        <td><?= htmlspecialchars($p['part_condition']) ?></td>
        <td><?= htmlspecialchars($p['location']) ?></td>
        <td><?= htmlspecialchars($p['notes']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("vehicle_strip_{$vehicle_id}.pdf", ["Attachment" => false]);
exit;
