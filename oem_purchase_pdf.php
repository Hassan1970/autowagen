<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/vendor/autoload.php";

use Dompdf\Dompdf;

$invoice_id = (int)$_GET['invoice_id'];
$inv = $conn->query("
    SELECT i.*, s.supplier_name
    FROM supplier_oem_invoices i
    LEFT JOIN suppliers s ON s.id = i.supplier_id
    WHERE i.id=$invoice_id
")->fetch_assoc();

$items = $conn->query("SELECT * FROM supplier_oem_invoice_items WHERE invoice_id=$invoice_id");

$html = "
<h2>OEM Purchase Invoice #{$inv['invoice_number']}</h2>
<p><b>Supplier:</b> {$inv['supplier_name']}<br>
<b>Date:</b> {$inv['invoice_date']}<br>
<b>Total:</b> R ".number_format($inv['total_amount'],2)."</p>

<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<tr>
<th>Part</th><th>Qty</th><th>Cost</th><th>Total</th>
</tr>
";

while($r=$items->fetch_assoc()) {
    $t = $r['qty'] * $r['cost_price'];
    $html .= "
    <tr>
        <td>{$r['part_name']}</td>
        <td>{$r['qty']}</td>
        <td>R {$r['cost_price']}</td>
        <td>R $t</td>
    </tr>";
}

$html .= "</table>";

$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();
$pdf->stream("Invoice-$invoice_id.pdf", ["Attachment"=>false]);
