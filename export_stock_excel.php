<?php
require 'vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

/* HEADERS */
$headers = ['ID','Part','Movement','Qty','Current Stock','Profit','Reference','User','Date','Invoice'];

$col = 'A';
foreach($headers as $h){
    $sheet->setCellValue($col.'1', $h);
    $col++;
}

/* STYLE HEADER */
$sheet->getStyle('A1:J1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb'=>'FFFFFF']],
    'fill' => [
        'fillType'=>Fill::FILL_SOLID,
        'startColor'=>['rgb'=>'C00000']
    ],
    'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]
]);

/* DATE FILTER */
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$where = "";
if($from && $to){
    $where = "WHERE DATE(m.created_at) BETWEEN '$from' AND '$to'";
}

/* FETCH DATA */
$result = $conn->query("
SELECT m.*, p.part_name, p.stock_qty
FROM oem_stock_movements m
LEFT JOIN oem_parts p ON p.id = m.part_id
$where
ORDER BY m.id DESC
");

$row = 2;
$totalQty = 0;
$totalProfit = 0;

while($r = $result->fetch_assoc()){

    /* ✅ REAL PROFIT (SAFE) */
    $sell = isset($r['selling_price']) ? (float)$r['selling_price'] : 0;
    $cost = isset($r['cost_price']) ? (float)$r['cost_price'] : 0;
    $profit = ($sell - $cost) * $r['qty'];

    /* ✅ INVOICE FROM REFERENCE */
    $invoice = '';
    if(strpos($r['reference'], 'POS#') === 0){
        $invoice = str_replace('POS#','',$r['reference']);
    }

    $sheet->setCellValue("A$row", $r['id']);
    $sheet->setCellValue("B$row", $r['part_name']);
    $sheet->setCellValue("C$row", $r['movement_type']);
    $sheet->setCellValue("D$row", $r['qty']);
    $sheet->setCellValue("E$row", $r['stock_qty']);
    $sheet->setCellValue("F$row", $profit);
    $sheet->setCellValue("G$row", $r['reference']);
    $sheet->setCellValue("H$row", $r['created_by']);
    $sheet->setCellValue("I$row", $r['created_at']);
    $sheet->setCellValue("J$row", $invoice);

    /* ✅ COLOR PROFIT GREEN */
    if($profit > 0){
        $sheet->getStyle("F$row")->getFont()->getColor()->setRGB('00AA00');
    }

    $totalQty += $r['qty'];
    $totalProfit += $profit;

    $row++;
}

/* TOTAL ROW */
$sheet->setCellValue("C$row", "TOTAL");
$sheet->setCellValue("D$row", $totalQty);
$sheet->setCellValue("F$row", $totalProfit);

/* STYLE TOTAL */
$sheet->getStyle("C$row:F$row")->applyFromArray([
    'font'=>['bold'=>true]
]);

/* AUTO SIZE */
foreach(range('A','J') as $col){
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

/* BORDERS */
$sheet->getStyle('A1:J'.$row)->applyFromArray([
    'borders'=>[
        'allBorders'=>[
            'borderStyle'=>Border::BORDER_THIN
        ]
    ]
]);

/* FREEZE HEADER */
$sheet->freezePane('A2');

/* DOWNLOAD */
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="stock_history.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;