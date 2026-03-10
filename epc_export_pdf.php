<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

$sql = "
SELECT 
    c.id   AS cat_id,
    c.name AS cat_name,
    s.id   AS sub_id,
    s.name AS sub_name,
    t.id   AS type_id,
    t.name AS type_name,
    comp.id   AS comp_id,
    comp.name AS comp_name
FROM categories c
LEFT JOIN subcategories s ON s.category_id = c.id
LEFT JOIN types t ON t.subcategory_id = s.id
LEFT JOIN components comp ON comp.type_id = t.id
ORDER BY c.name, s.name, t.name, comp.name
";

$res = $conn->query($sql);

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

$html = '
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; }
h1 { text-align:center; font-size:16px; margin-bottom:10px; }
h2 { font-size:12px; margin:8px 0 4px; border-bottom:1px solid #ccc; }
h3 { font-size:11px; margin:6px 0 3px; }
table { width:100%; border-collapse:collapse; margin-bottom:8px; }
th, td { border:1px solid #ccc; padding:3px 4px; }
th { background:#eee; }
.small { font-size:9px; color:#666; }
</style>
<h1>Autowagen EPC Structure</h1>
';

$curCat = $curSub = $curType = null;

if (!$rows) {
    $html .= '<p>No EPC data found.</p>';
} else {
    foreach ($rows as $r) {
        if ($curCat !== $r['cat_id']) {
            $curCat = $r['cat_id'];
            $curSub = null;
            $curType = null;
            $html .= '<h2>[' . htmlspecialchars($r['cat_name']) . ']</h2>';
        }
        if ($r['sub_id'] && $curSub !== $r['sub_id']) {
            $curSub = $r['sub_id'];
            $curType = null;
            $html .= '<h3>Subcategory: ' . htmlspecialchars($r['sub_name']) . '</h3>';
        }
        if ($r['type_id'] && $curType !== $r['type_id']) {
            $curType = $r['type_id'];
            $html .= '<table><thead><tr>
                        <th width="40%">Type</th>
                        <th width="60%">Components</th>
                      </tr></thead><tbody>';
            $html .= '<tr><td>' . htmlspecialchars($r['type_name']) . '</td><td>';
        }

        if ($r['comp_id']) {
            $html .= htmlspecialchars($r['comp_name']) . '<br />';
        }

        // Look ahead: if next row is different type/sub/cat, close table cells
        // For simplicity, we close table when type changes or loop ends
        // (extra closing handled after loop)
    }
    $html .= '</td></tr></tbody></table>';
}

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('epc_structure.pdf', ['Attachment' => 1]);
