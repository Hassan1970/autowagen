<?php
// DB CONNECTION
$conn = new mysqli("localhost", "root", "", "autowagen");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SET HEADER FOR DOWNLOAD
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="body_exterior_epc.txt"');

// STEP 1 — GET BODY EXTERIOR CATEGORY
$categoryQuery = "SELECT id, name FROM categories WHERE name = 'Body Exterior'";
$categoryResult = $conn->query($categoryQuery);

if ($categoryResult->num_rows == 0) {
    echo "Body Exterior category not found.";
    exit;
}

$category = $categoryResult->fetch_assoc();
echo "CATEGORY: " . $category['name'] . "\n\n";

// STEP 2 — GET SUBCATEGORIES
$subQuery = "SELECT id, name FROM subcategories WHERE category_id = " . $category['id'] . " ORDER BY id";
$subResult = $conn->query($subQuery);

while ($sub = $subResult->fetch_assoc()) {

    echo "▶ Subcategory [ID: {$sub['id']}] {$sub['name']}\n";

    // STEP 3 — GET TYPES
    $typeQuery = "SELECT id, name FROM types WHERE subcategory_id = " . $sub['id'] . " ORDER BY id";
    $typeResult = $conn->query($typeQuery);

    while ($type = $typeResult->fetch_assoc()) {

        echo "  ▶ Type [ID: {$type['id']}] {$type['name']}\n";

        // STEP 4 — GET COMPONENTS
        $compQuery = "SELECT id, name FROM components WHERE type_id = " . $type['id'] . " ORDER BY id";
        $compResult = $conn->query($compQuery);

        if ($compResult->num_rows == 0) {
            echo "    (No components)\n";
        } else {
            while ($comp = $compResult->fetch_assoc()) {
                echo "    • Component [ID: {$comp['id']}] {$comp['name']}\n";
            }
        }
    }

    echo "\n";
}

$conn->close();
?>