<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

// Same filters as other scripts
function gp($n,$d=''){return $_GET[$n] ?? $d;}

$f_supplier = (int)gp('supplier_id',0);
$f_invoice  = trim(gp('invoice_number',""));
$f_type     = trim(gp('part_type',""));
$f_cat      = (int)gp('category_id',0);
$f_from     = trim(gp('date_from',""));
$f_to       = trim(gp('date_to',""));

$where=[];$types="";$params=[];
if($f_supplier>0){$where[]="si.supplier_id=?";$types.="i";$params[]=$f_supplier;}
if($f_invoice!==""){$where[]="si.invoice_number LIKE ?";$types.="s";$params[]="%$f_invoice%";}
if($f_type!==""){$where[]="sii.part_type=?";$types.="s";$params[]=$f_type;}
if($f_cat>0){$where[]="sii.category_id=?";$types.="i";$params[]=$f_cat;}
if($f_from!==""){$where[]="si.invoice_date>=?";$types.="s";$params[]=$f_from;}
if($f_to!==""){$where[]="si.invoice_date<=?";$types.="s";$params[]=$f_to;}

$whereSql = $where ? "WHERE ".implode(" AND ",$where) : "";

$sql = "
SELECT 
    sii.id AS item_id,
    sii.part_name,
    sii.part_type,
    sii.cost_price,
    sii.qty,
    si.invoice_number,
    si.invoice_date,
    s.supplier_name,
    c.name AS category_name
FROM supplier_invoice_items sii
LEFT JOIN supplier_invoices si ON si.id = sii.invoice_id
LEFT JOIN suppliers s ON s.id = si.supplier_id
LEFT JOIN categories c ON c.id = sii.category_id
$whereSql
ORDER BY sii.id DESC
";

$stmt=$conn->prepare($sql);
if($types!=="") $stmt->bind_param($types,...$params);
$stmt->execute();
$res=$stmt->get_result();

// Build HTML
$html = '<h2 style="font-family:Arial;">Supplier Invoice Items</h2>';
$html .= '<table width="100%" cellspacing="0" cellpadding="4" border="1" style="font-size:11px;border-collapse:collapse;">';
$html .= '<tr style="background:#eee;">
            <th>ID</th><th>Invoice #</th><th>Date</th><th>Supplier</th>
            <th>Type</th><th>Category</th><th>Part</th>
            <th>Cost</th><th>Qty</th><th>Total</th>
          </tr>';

$grand = 0;
while($row=$res->fetch_assoc()){
    $total = $row['cost_price'] * $row['qty'];
    $grand += $total;
    $html .= '<tr>
        <td>'.$row['item_id'].'</td>
        <td>'.htmlspecialchars($row['invoice_number']).'</td>
        <td>'.htmlspecialchars($row['invoice_date']).'</td>
        <td>'.htmlspecialchars($row['supplier_name']).'</td>
        <td>'.htmlspecialchars($row['part_type']).'</td>
        <td>'.htmlspecialchars($row['category_name']).'</td>
        <td>'.htmlspecialchars($row['part_name']).'</td>
        <td>'.number_format($row['cost_price'],2).'</td>
        <td>'.$row['qty'].'</td>
        <td>'.number_format($total,2).'</td>
    </tr>';
}
$html .= '<tr>
            <td colspan="9" style="text-align:right;font-weight:bold;">Grand Total</td>
            <td>'.number_format($grand,2).'</td>
          </tr>';
$html .= '</table>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','landscape');
$dompdf->render();

// stream inline (view in browser)
$dompdf->stream("supplier_invoice_items.pdf", ["Attachment" => false]);
exit;

