<?php
require_once __DIR__ . '/config/config.php';

/* =====================================================
   DEBUG MODE (TURN OFF IN PRODUCTION)
===================================================== */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* =====================================================
   VALIDATE REQUEST METHOD
===================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

/* =====================================================
   READ INPUT
===================================================== */
$itemsJson = $_POST['items_json'] ?? '';
$items = json_decode($itemsJson, true);

if (!$items) {
    die("JSON ERROR: " . json_last_error_msg());
}

$grandTotal = isset($_POST['grand_total']) ? (float)$_POST['grand_total'] : 0;
$amountPaid = isset($_POST['amount_paid']) ? (float)$_POST['amount_paid'] : 0;

if (!is_array($items) || count($items) === 0) {
    die('Invalid invoice items');
}

if ($grandTotal <= 0) {
    die('Invalid invoice total');
}

/* =====================================================
   PAYMENT STATUS
===================================================== */
$status = ($amountPaid >= $grandTotal) ? 'paid' : 'unpaid';

/* =====================================================
   CUSTOMER DATA
===================================================== */
$customer_id = null;
$customer_name = null;
$customer_phone = null;

if (!empty($_POST['customer_id'])) {

    $stmtCust = $conn->prepare("
        SELECT id, name, phone
        FROM customers
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmtCust) {
        die("Customer prepare failed: " . $conn->error);
    }

    $stmtCust->bind_param("i", $_POST['customer_id']);
    $stmtCust->execute();

    $resCust = $stmtCust->get_result();

    if ($rowCust = $resCust->fetch_assoc()) {

        $customer_id = (int)$rowCust['id'];
        $customer_name = $rowCust['name'];
        $customer_phone = $rowCust['phone'];
    }

} else {

    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
}

/* =====================================================
   START TRANSACTION
===================================================== */
$conn->begin_transaction();

try {

/* =====================================================
   INSERT INVOICE HEADER
===================================================== */
$stmtInvoice = $conn->prepare("
    INSERT INTO pos_invoices
    (
        total_amount,
        customer_id,
        customer_name,
        customer_phone,
        status,
        created_at
    )
    VALUES (?, ?, ?, ?, ?, NOW())
");

if (!$stmtInvoice) {
    throw new Exception("Invoice prepare failed: " . $conn->error);
}

$stmtInvoice->bind_param(
    "disss",
    $grandTotal,
    $customer_id,
    $customer_name,
    $customer_phone,
    $status
);

if (!$stmtInvoice->execute()) {
    throw new Exception("Invoice insert failed: " . $stmtInvoice->error);
}

$invoiceId = $stmtInvoice->insert_id;


/* =====================================================
   PREPARE ITEM INSERT
===================================================== */
$stmtItem = $conn->prepare("
    INSERT INTO pos_invoice_items
    (
        invoice_id,
        part_type,
        part_id,
        part_name,
        vehicle_stock_code,
        vehicle_name,
        quantity,
        price,
        cost_price,
        subtotal
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmtItem) {
    throw new Exception("Item prepare failed: " . $conn->error);
}


/* =====================================================
   PROCESS ITEMS
===================================================== */
foreach ($items as $item) {

    /* NORMALIZE TYPE */
    $type = strtoupper(trim($item['source'] ?? ''));

    if ($type === 'SRC_STRIP') $type = 'STRIP';
    if ($type === 'SRC_OEM')   $type = 'OEM';
    if ($type === 'SRC_TP')    $type = 'TP';
    if ($type === 'SRC_NEW')   $type = 'NEW';


    $name  = $item['name'] ?? 'Item';
    $id    = (int)($item['id'] ?? 0);
    $qty   = (int)($item['qty'] ?? 0);
    $price = (float)($item['price'] ?? 0);

    /* NEW FIELDS */
    $vehicleCode = $item['vehicle_stock_code'] ?? '';
    $vehicleName = $item['vehicle_name'] ?? '';

    if ($id <= 0 || $qty <= 0) {
        throw new Exception("Invalid item data");
    }

    $subtotal = $qty * $price;


    /* MAP TABLE */
    switch ($type) {

        case 'OEM':
            $table='oem_parts';
            $stockField='stock_qty';
            $costField='cost_price';
        break;

        case 'NEW':
            $table='replacement_parts';
            $stockField='stock_qty';
            $costField='cost_price';
        break;

        case 'TP':
            $table='third_party_parts';
            $stockField='stock_status';
            $costField='cost_excl';
        break;

        case 'STRIP':
            $table='vehicle_stripped_parts';
            $stockField='qty';
            $costField=null;
        break;

        default:
            throw new Exception("Invalid part type: ".$type);
    }


    /* LOCK STOCK */
    if ($costField) {

        $stmtStock=$conn->prepare("
            SELECT $stockField,$costField
            FROM $table
            WHERE id=?
            FOR UPDATE
        ");

    } else {

        $stmtStock=$conn->prepare("
            SELECT $stockField
            FROM $table
            WHERE id=?
            FOR UPDATE
        ");
    }

    $stmtStock->bind_param("i",$id);
    $stmtStock->execute();

    $resStock=$stmtStock->get_result();

    if(!$rowStock=$resStock->fetch_assoc())
        throw new Exception("Part not found");


    /* STOCK VALIDATION */
    if($type=='TP'){

        if($rowStock[$stockField]!='IN_STOCK')
            throw new Exception("Third party sold");

    }else{

        if((int)$rowStock[$stockField]<$qty)
            throw new Exception("Insufficient stock");
    }


    /* COST CHECK */
    $cost=0;

    if($costField){

        $cost=(float)$rowStock[$costField];

        if($price<$cost)
            throw new Exception("Cannot sell below cost");
    }


    /* INSERT ITEM */
    $stmtItem->bind_param(
        "isisssiddd",
        $invoiceId,
        $type,
        $id,
        $name,
        $vehicleCode,
        $vehicleName,
        $qty,
        $price,
        $cost,
        $subtotal
    );

    $stmtItem->execute();


    /* =====================================================
       STOCK UPDATE SECTION
       UPDATED: 2026-03-05
       Prevent selling same stripped part twice and
       improve stripped inventory logic
    ===================================================== */

    if($type=='TP'){

        $stmtUpdate=$conn->prepare("
            UPDATE $table
            SET $stockField='SOLD'
            WHERE id=?
        ");

        $stmtUpdate->bind_param("i",$id);
        $stmtUpdate->execute();

    }
    elseif($type=='STRIP'){

        /* reduce stripped qty */

        $stmtUpdate=$conn->prepare("
            UPDATE vehicle_stripped_parts
            SET qty = qty - ?
            WHERE id = ?
        ");

        $stmtUpdate->bind_param("ii",$qty,$id);
        $stmtUpdate->execute();

    }
    else{

        /* OEM + Replacement */

        $stmtUpdate=$conn->prepare("
            UPDATE $table
            SET $stockField = $stockField - ?
            WHERE id = ?
        ");

        $stmtUpdate->bind_param("ii",$qty,$id);
        $stmtUpdate->execute();

    }

}


/* =====================================================
   COMMIT
===================================================== */
$conn->commit();


/* =====================================================
   REDIRECT TO PRINT PAGE
===================================================== */
header("Location: print_invoice.php?invoice_id=".$invoiceId);
exit;


}
catch(Exception $e){

$conn->rollback();

die("SYSTEM ERROR:<br>".$e->getMessage());

}
?>