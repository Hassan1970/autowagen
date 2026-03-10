<?php
require_once __DIR__ . '/config/config.php';

echo "<pre>";

// 1) Build a quick dictionary of EPC components (lowercase name => ids)
$epc = [];

$res = $conn->query("
    SELECT 
        comp.id AS component_id,
        comp.name AS component_name,
        t.id    AS type_id,
        t.name  AS type_name,
        s.id    AS sub_id,
        s.name  AS sub_name,
        c.id    AS cat_id,
        c.name  AS cat_name
    FROM components comp
    JOIN types t ON t.id = comp.type_id
    JOIN subcategories s ON s.id = t.subcategory_id
    JOIN categories c ON c.id = s.category_id
");
while ($r = $res->fetch_assoc()) {
    $key = strtolower($r['component_name']);
    $epc[$key] = $r;
}

// 2) Look at supplier_invoice_items with NO EPC yet
$q = $conn->query("
    SELECT id, part_name, category_id, subcategory_id, type_id, component_id
    FROM supplier_invoice_items
    ORDER BY id ASC
    LIMIT 100
");

while ($row = $q->fetch_assoc()) {
    $original = $row['part_name'];
    $key = strtolower(trim($original));

    echo "Item #{$row['id']} : {$original}\n";

    // Skip if already fully mapped
    if ($row['component_id']) {
        echo "  -> Already linked (component_id={$row['component_id']})\n\n";
        continue;
    }

    // Very simple exact match first
    $matched = null;
    foreach ($epc as $compName => $info) {
        if (strpos($key, strtolower($info['component_name'])) !== false ||
            strpos(strtolower($info['component_name']), $key) !== false) {
            $matched = $info;
            break;
        }
    }

    if ($matched) {
        echo "  -> Suggest EPC mapping:\n";
        echo "     Category    : {$matched['cat_name']} (ID {$matched['cat_id']})\n";
        echo "     Subcategory : {$matched['sub_name']} (ID {$matched['sub_id']})\n";
        echo "     Type        : {$matched['type_name']} (ID {$matched['type_id']})\n";
        echo "     Component   : {$matched['component_name']} (ID {$matched['component_id']})\n\n";

        // If you are happy later, you can enable an UPDATE like:
        // $stmt = $conn->prepare("
        //     UPDATE supplier_invoice_items
        //     SET category_id = ?, subcategory_id = ?, type_id = ?, component_id = ?
        //     WHERE id = ?
        // ");
        // $stmt->bind_param(
        //     'iiiii',
        //     $matched['cat_id'],
        //     $matched['sub_id'],
        //     $matched['type_id'],
        //     $matched['component_id'],
        //     $row['id']
        // );
        // $stmt->execute();
        // $stmt->close();
    } else {
        echo "  -> No auto EPC match found.\n\n";
    }
}

echo "</pre>";
