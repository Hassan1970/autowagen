<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/vendor/autoload.php";

use Dompdf\Dompdf;

$sql = "
    SELECT 
        s.*, 
        v.make, v.model, v.year,
        c.name AS category_name,
        sc.name AS subcategory_name,
        t.name AS type_name,
        comp.name AS component_name
    FROM sold_inventory s
    LEFT JOIN stripped_inventory si ON si.id = s.inventory_id
    LEFT JOIN vehicles v ON s.vehicle_id = v.id
    LEFT JOIN categories c ON si.category_id = c.id
    LEFT JOIN subcategories sc ON si.subcategory_id = sc.id
    LEFT JOIN types t ON si.type_id = t.id
    LEFT JOIN components comp ON si.component_id = comp.id
    ORDER BY s.sold_date DESC
";
$res = $conn->query($sql);

$html = "
<h2 style='text-align:center;'>Sold Parts Report</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='5' style='font-size:12px;'>
<tr>
    <th>ID</th>
    <th>Date</th>
    <th>Stock</th>
    <th>Vehicle</th>
    <th>Part</th>
    <th>Qty</th>
    <th>Condition</th>
</tr>
";

while ($row = $res->fetch_assoc()) {
    $html .= "
    <tr>
        <td>{$row['id']}</td>
        <td>{$row['sold_date']}</td>
        <td>{$row['stock_code']}</td>
        <td>{$row['make']} {$row['model']} {$row['year']}</td>
        <td>{$row['part_name']}</td>
        <td>{$row['qty']}</td>
        <td>{$row['part_condition']}</td>
    </tr>";
}

$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("sold_parts_report.pdf", ["Attachment" => false]);
