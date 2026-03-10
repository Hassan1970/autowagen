<?php
require_once __DIR__ . '/config/config.php';

// Same filter logic as list page
function getParam($name, $default=''){ return $_GET[$name] ?? $default; }

$f_supplier = (int)getParam('supplier_id', 0);
$f_invoice  = trim(getParam('invoice_number', ""));
$f_type     = trim(getParam('part_type', ""));
$f_cat      = (int)getParam('category_id', 0);
$f_from     = trim(getParam('date_from', ""));
$f_to       = trim(getParam('date_to', ""));

$where = [];
$types = "";
$params = [];

if ($f_supplier > 0) { $where[]="si.supplier_id=?"; $types.="i"; $params[]=$f_supplier; }
if ($f_invoice !== ""){ $where[]="si.invoice_number LIKE ?"; $types.="s"; $params[]="%$f_invoice%"; }
if ($f_type !== "")   { $where[]="sii.part_type=?"; $types.="s"; $params[]=$f_type; }
if ($f_cat > 0)       { $where[]="sii.category_id=?"; $types.="i"; $params[]=$f_cat; }
if ($f_from !== "")   { $where[]="si.invoice_date>=?"; $types.="s"; $params[]=$f_from; }
if ($f_to !== "")     { $where[]="si.invoice_date<=?"; $types.="s"; $params[]=$f_to; }

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

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

$stmt = $conn->prepare($sql);
if ($types!=="") $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// Headers
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"supplier_invoice_items.xls\"");

// Simple HTML table (Excel can open)
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Invoice #</th>
        <th>Date</th>
        <th>Supplier</th>
        <th>Part Type</th>
        <th>Category</th>
        <th>Part Name</th>
        <th>Cost</th>
        <th>Qty</th>
        <th>Total</th>
      </tr>";

while ($row = $res->fetch_assoc()) {
    $total = $row['cost_price'] * $row['qty'];
    echo "<tr>";
    echo "<td>{$row['item_id']}</td>";
    echo "<td>".htmlspecialchars($row['invoice_number'])."</td>";
    echo "<td>".htmlspecialchars($row['invoice_date'])."</td>";
    echo "<td>".htmlspecialchars($row['supplier_name'])."</td>";
    echo "<td>".htmlspecialchars($row['part_type'])."</td>";
    echo "<td>".htmlspecialchars($row['category_name'])."</td>";
    echo "<td>".htmlspecialchars($row['part_name'])."</td>";
    echo "<td>".number_format($row['cost_price'],2)."</td>";
    echo "<td>{$row['qty']}</td>";
    echo "<td>".number_format($total,2)."</td>";
    echo "</tr>";
}
echo "</table>";
exit;
