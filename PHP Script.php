<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Define categories, subcategories, and components
$vehicleParts = [
    "Engine & Transmission" => [
        "Engine Assembly" => [
            "Cylinder head", "Intake valves", "Exhaust valves", "Valve springs",
            "Rocker arms", "Crankshaft", "Pistons", "Connecting rods"
        ],
        "Fuel System" => [
            "Fuel tank", "Fuel pump", "Fuel injectors", "Throttle body"
        ],
        "Cooling System" => [
            "Radiator", "Water pump", "Thermostat", "Cooling fan"
        ],
        "Transmission" => [
            "Gearbox", "Clutch assembly", "Pressure plate", "Driveshafts", "Differential"
        ]
    ],
    "Body & Exterior" => [
        "Front Section" => [
            "Bonnet outer panel", "Hood latch mechanism", "Front bumper cover",
            "Fog lights (L/R)", "Headlight assembly"
        ],
        "Rear Section" => [
            "Boot lid", "Tail light assembly", "Rear bumper cover"
        ],
        "Glass" => [
            "Windshield", "Rear window", "Door glass (L/R)"
        ]
    ],
    "Interior & Electrical" => [
        "Seats & Trim" => [
            "Front seat frame", "Dashboard shell", "Glove box", "Roof lining"
        ],
        "Controls" => [
            "Steering wheel", "Ignition switch", "Gear lever", "Pedals"
        ],
        "Electrical" => [
            "Battery", "Alternator", "Starter motor", "Fuse box", "ECU", "Wiring harness"
        ],
        "Comfort" => [
            "Air conditioning unit", "Heater core", "Audio system", "Interior lights"
        ]
    ],
    "Suspension, Brakes & Wheels" => [
        "Suspension" => [
            "Front strut assembly", "Coil spring", "Control arms", "Ball joints"
        ],
        "Brakes" => [
            "Brake discs", "Brake calipers", "Brake master cylinder", "Brake booster"
        ],
        "Steering" => [
            "Rack & pinion", "Tie rods", "Power steering pump"
        ],
        "Wheels" => [
            "Rims", "Tyres", "Wheel bearings", "Hub assemblies"
        ]
    ]
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Checklist");

// Headers
$headers = ["Part", "Available", "Condition", "Price"];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col.'1', $header);
    $col++;
}
$sheet->getStyle('A1:D1')->getFont()->setBold(true);

$row = 2;
foreach ($vehicleParts as $category => $subcategories) {
    // Category header
    $sheet->setCellValue("A$row", $category);
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $row++;

    foreach ($subcategories as $subcat => $parts) {
        // Subcategory header
        $sheet->setCellValue("A$row", $subcat);
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $row++;

        foreach ($parts as $part) {
            $sheet->setCellValue("A$row", "- " . $part); // indent with dash
            $sheet->setCellValue("B$row", "☐"); // Checkbox placeholder
            $sheet->setCellValue("C$row", "");  // Condition
            $sheet->setCellValue("D$row", "");  // Price
            $row++;
        }
    }
}

$writer = new Xlsx($spreadsheet);
$filePath = __DIR__ . '/vehicle_dismantling_checklist_style.xlsx';
$writer->save($filePath);

echo "Checklist-style Excel file created: " . $filePath;
