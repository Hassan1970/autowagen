<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * EPC FULL SEEDER
 * --------------------------------------------
 * - Uses existing categories table (7 rows)
 * - Rebuilds subcategories, types, components
 * - Creates ~70 subcategories, 700 types, 7000 components
 *
 * RUN ONCE from browser:
 *   http://localhost/autowagen_master_clean/epc_seed_full.php
 */

// 1) Load categories (id => name)
$catMap = [];
$res = $conn->query("SELECT id, name FROM categories ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
    $catMap[$row['name']] = (int)$row['id'];
}

if (count($catMap) < 7) {
    die("Error: Expected 7 categories. Found ".count($catMap).". Check categories table.");
}

// 2) Define subcategories per category (names only)
$subcats = [
    'Engine' => [
        'Short Block', 'Cylinder Head', 'Valve Train', 'Timing System',
        'Lubrication System', 'Cooling System', 'Fuel System', 'Air Intake',
        'Exhaust System', 'Engine Mountings'
    ],
    'Transmission' => [
        'Gearbox Assembly', 'Clutch & Flywheel', 'Torque Converter', 'Gear Sets',
        'Synchronisers', 'Shift Mechanism', 'Seals & Gaskets', 'Mountings',
        'Sensors & Switches', 'Oil Cooling & Lines'
    ],
    'Front Suspension & Steering' => [
        'Front Axle', 'Control Arms', 'Ball Joints', 'Steering Rack',
        'Tie Rods', 'Shock Absorbers', 'Springs', 'Wheel Hubs & Bearings',
        'Anti-Roll Bar', 'Mountings & Bushes'
    ],
    'Rear Suspension & Axle' => [
        'Rear Axle', 'Shock Absorbers', 'Springs', 'Control Arms',
        'Trailing Arms', 'Wheel Hubs & Bearings', 'Anti-Roll Bar',
        'Bushes & Mountings', 'Differential', 'Driveline & Shafts'
    ],
    'Brakes' => [
        'Front Brakes', 'Rear Brakes', 'Brake Discs', 'Brake Drums',
        'Calipers', 'Brake Pads & Shoes', 'Brake Lines & Hoses',
        'Master Cylinder', 'ABS Components', 'Handbrake System'
    ],
    'Body & Interior' => [
        'Front Bumper', 'Rear Bumper', 'Fenders & Wings', 'Bonnet & Bootlid',
        'Doors & Sliding Doors', 'Glass & Windows', 'Mirrors',
        'Seats & Seatbelts', 'Dashboard & Console', 'Interior Trim & Panels'
    ],
    'Electrical & Electronics' => [
        'Battery & Cables', 'Charging System', 'Starting System',
        'Lighting Front', 'Lighting Rear', 'Interior Lighting',
        'Engine Management', 'Sensors & Switches', 'Infotainment',
        'Wiring Harness & Connectors'
    ],
];

// 3) Generic types (reused for each subcategory)
$typeTemplates = [
    'General Assembly',
    'Housing & Case',
    'Internal Components',
    'Wear Parts',
    'Seals & Gaskets',
    'Bearings & Bushes',
    'Sensors & Switches',
    'Lines & Hoses / Pipes',
    'Mounting Hardware',
    'Service Kits & Small Parts'
];

// 4) Generic components (reused for each type)
$componentTemplates = [
    'Main Assembly',
    'Left Side Assembly',
    'Right Side Assembly',
    'Upper Assembly',
    'Lower Assembly',
    'Bracket / Mount',
    'Bolt / Nut / Washer Set',
    'Seal / Gasket',
    'Sensor / Switch',
    'Service / Repair Kit'
];

// 5) TRUNCATE existing EPC tables
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE components");
$conn->query("TRUNCATE TABLE types");
$conn->query("TRUNCATE TABLE subcategories");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// 6) Insert new EPC structure
$insertedSub = 0;
$insertedTypes = 0;
$insertedComponents = 0;

foreach ($subcats as $catName => $subList) {
    if (!isset($catMap[$catName])) {
        echo "Warning: Category '$catName' not found in categories table. Skipping.<br>";
        continue;
    }
    $catId = $catMap[$catName];

    foreach ($subList as $subName) {
        // Insert subcategory
        $stmt = $conn->prepare("INSERT INTO subcategories (category_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $catId, $subName);
        if (!$stmt->execute()) {
            die("Error inserting subcategory '$subName': " . $stmt->error);
        }
        $subId = $stmt->insert_id;
        $stmt->close();
        $insertedSub++;

        // For each subcategory, add all generic types
        foreach ($typeTemplates as $tName) {
            $stmt = $conn->prepare("INSERT INTO types (subcategory_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $subId, $tName);
            if (!$stmt->execute()) {
                die("Error inserting type '$tName': " . $stmt->error);
            }
            $typeId = $stmt->insert_id;
            $stmt->close();
            $insertedTypes++;

            // For each type, add all generic components
            foreach ($componentTemplates as $cName) {
                $stmt = $conn->prepare("INSERT INTO components (type_id, name) VALUES (?, ?)");
                $stmt->bind_param("is", $typeId, $cName);
                if (!$stmt->execute()) {
                    die("Error inserting component '$cName': " . $stmt->error);
                }
                $stmt->close();
                $insertedComponents++;
            }
        }
    }
}

echo "<h2 style='color:#0f0;'>EPC Seed Complete</h2>";
echo "<p>Subcategories inserted: {$insertedSub}</p>";
echo "<p>Types inserted: {$insertedTypes}</p>";
echo "<p>Components inserted: {$insertedComponents}</p>";
echo "<p>You can now delete epc_seed_full.php.</p>";
