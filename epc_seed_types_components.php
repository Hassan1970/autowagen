<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * EPC TYPE + COMPONENT FULL RESEED
 * --------------------------------------------
 * - Keeps existing categories + subcategories
 * - TRUNCATES types + components
 * - Inserts category-specific types & components
 *
 * RUN ONCE from browser:
 *   http://localhost/autowagen_master_clean/epc_seed_types_components.php
 */

// 1) Load subcategories with their category
$sql = "
    SELECT s.id AS sub_id, s.name AS sub_name, c.name AS cat_name
    FROM subcategories s
    JOIN categories c ON c.id = s.category_id
    ORDER BY c.id, s.id
";
$res = $conn->query($sql);
if (!$res) {
    die("Error loading subcategories: " . $conn->error);
}

// 2) TYPE templates per CATEGORY
$typeTemplatesByCat = [
    'Engine' => [
        'Short Block Assembly',
        'Cylinder Head Assembly',
        'Valve Train',
        'Timing Drive & Covers',
        'Lubrication Components',
        'Cooling Components',
        'Fuel & Injection System',
        'Air Intake & Manifolds',
        'Exhaust Manifolds & Pipes',
        'Engine Mounts & Brackets',
    ],
    'Transmission' => [
        'Gearbox Assembly',
        'Gear Sets & Shafts',
        'Clutch & Flywheel',
        'Torque Converter',
        'Synchros & Hubs',
        'Shift Linkages',
        'Seals & Gaskets',
        'Mounts & Brackets',
        'Oil Pumps & Cooling',
        'Sensors & Switches',
    ],
    'Front Suspension & Steering' => [
        'Front Axle & Subframe',
        'Control Arms',
        'Ball Joints & Links',
        'Steering Rack & Box',
        'Tie Rods & Ends',
        'Front Springs',
        'Front Shock Absorbers',
        'Strut Mounts & Bearings',
        'Anti-Roll Bar & Links',
        'Wheel Hubs & Bearings',
    ],
    'Rear Suspension & Axle' => [
        'Rear Axle & Subframe',
        'Trailing Arms',
        'Control Arms',
        'Rear Springs',
        'Rear Shock Absorbers',
        'Bushes & Mounts',
        'Anti-Roll Bar & Links',
        'Wheel Hubs & Bearings',
        'Differential Assembly',
        'Driveshafts & CV Joints',
    ],
    'Brakes' => [
        'Front Brake System',
        'Rear Brake System',
        'Brake Discs & Drums',
        'Calipers & Carriers',
        'Brake Pads & Shoes',
        'Hydraulic Lines & Hoses',
        'Master Cylinder & Booster',
        'ABS Unit & Sensors',
        'Handbrake Cables & Levers',
        'Fittings & Hardware',
    ],
    'Body & Interior' => [
        'Outer Panels & Skins',
        'Bumper Assemblies',
        'Reinforcements & Beams',
        'Mountings & Brackets',
        'Grilles & Inserts',
        'Trims & Mouldings',
        'Glass & Window Hardware',
        'Seats & Seatbelts',
        'Dashboard & Console',
        'Interior Trim & Linings',
    ],
    'Electrical & Electronics' => [
        'Battery & Power Distribution',
        'Charging System',
        'Starting System',
        'Engine Management',
        'Lighting Front',
        'Lighting Rear',
        'Interior Lighting',
        'Sensors & Switches',
        'Infotainment & Audio',
        'Wiring Harness & Connectors',
    ],
];

// 3) COMPONENT templates (generic but sensible for any type)
$componentTemplates = [
    'Main Assembly',
    'Left Side Assembly',
    'Right Side Assembly',
    'Upper Section',
    'Lower Section',
    'Mounting Bracket',
    'Reinforcement / Beam',
    'Clips / Fasteners Set',
    'Seal / Gasket Kit',
    'Service / Repair Kit',
];

// 4) TRUNCATE types + components
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE components");
$conn->query("TRUNCATE TABLE types");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// 5) Reseed types + components
$insertedTypes = 0;
$insertedComponents = 0;

while ($row = $res->fetch_assoc()) {
    $subId   = (int)$row['sub_id'];
    $subName = $row['sub_name'];
    $catName = $row['cat_name'];

    // Find template for this category, or fall back to a generic set
    $templates = $typeTemplatesByCat[$catName] ?? [
        'General Assembly',
        'Housing & Case',
        'Internal Components',
        'Wear Parts',
        'Seals & Gaskets',
        'Mountings & Brackets',
        'Sensors & Switches',
        'Lines & Hoses / Pipes',
        'Electrical Items',
        'Service Kits & Small Parts',
    ];

    foreach ($templates as $tName) {
        // Insert type
        $stmt = $conn->prepare("INSERT INTO types (subcategory_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $subId, $tName);
        if (!$stmt->execute()) {
            die("Error inserting type '$tName' for sub '$subName': " . $stmt->error);
        }
        $typeId = $stmt->insert_id;
        $stmt->close();
        $insertedTypes++;

        // Insert components for this type
        foreach ($componentTemplates as $cName) {
            $stmt = $conn->prepare("INSERT INTO components (type_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $typeId, $cName);
            if (!$stmt->execute()) {
                die("Error inserting component '$cName' for type '$tName': " . $stmt->error);
            }
            $stmt->close();
            $insertedComponents++;
        }
    }
}

echo "<h2 style='color:green;'>Type + Component Reseed Complete</h2>";
echo "<p>Types inserted: {$insertedTypes}</p>";
echo "<p>Components inserted: {$insertedComponents}</p>";
echo "<p>You can now delete epc_seed_types_components.php.</p>";
