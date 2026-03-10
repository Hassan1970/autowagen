<?php
require_once "config/config.php";

// INVOICE HEADER
$supplier_id   = $_POST['supplier_id'];
$invoice_no    = $_POST['invoice_number'];
$invoice_date  = $_POST['invoice_date'];

// INSERT INVOICE
$conn->query("
    INSERT INTO supplier_oem_invoices (supplier_id, invoice_number, invoice_date)
    VALUES ($supplier_id, '$invoice_no', '$invoice_date')
");

$invoice_id = $conn->insert_id;

// LOAD ARRAYS
$oem_numbers  = $_POST['oem_number'];
$part_names   = $_POST['part_name'];
$category_ids = $_POST['category_id'];
$sub_ids      = $_POST['subcategory_id'];
$type_ids     = $_POST['type_id'];
$comp_ids     = $_POST['component_id'];
$qtys         = $_POST['qty'];
$costs        = $_POST['cost_price'];

for ($i = 0; $i < count($oem_numbers); $i++) {

    if (empty($part_names[$i])) continue;

    $oem   = $conn->real_escape_string($oem_numbers[$i]);
    $pname = $conn->real_escape_string($part_names[$i]);

    $cat  = (int)$category_ids[$i];
    $sub  = (int)$sub_ids[$i];
    $type = (int)$type_ids[$i];
    $comp = (int)$comp_ids[$i];

    $qty  = (int)$qtys[$i];
    $cost = (float)$costs[$i];

    // CHECK IF OEM PART EXISTS
    $check = $conn->query("SELECT id FROM oem_parts WHERE oem_number='$oem'");
    if ($check->num_rows > 0) {
        $part_id = $check->fetch_assoc()['id'];
    } else {
        // CREATE OEM PART
        $conn->query("
            INSERT INTO oem_parts 
            (oem_number, part_name, category_id, subcategory_id, type_id, component_id, stock_qty, cost_price, selling_price)
            VALUES ('$oem', '$pname', $cat, $sub, $type, $comp, 0, $cost, $cost)
        ");
        $part_id = $conn->insert_id;
    }

    // INSERT INVOICE ITEM
    $conn->query("
        INSERT INTO supplier_oem_invoice_items
        (invoice_id, part_id, part_name, qty, cost_price)
        VALUES ($invoice_id, $part_id, '$pname', $qty, $cost)
    ");

    // UPDATE STOCK
    $conn->query("UPDATE oem_parts SET stock_qty = stock_qty + $qty WHERE id = $part_id");

    // INSERT STOCK MOVEMENT
    $conn->query("
        INSERT INTO stock_movements
        (part_id, movement_type, qty, ref_table, ref_id)
        VALUES ($part_id, 'PURCHASE', $qty, 'supplier_oem_invoices', $invoice_id)
    ");
}

echo "<script>
alert('OEM Purchase saved successfully');
window.location = 'oem_purchase_items_list.php?invoice_id=$invoice_id';
</script>";
exit;
