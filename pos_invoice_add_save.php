<?php
require_once __DIR__ . '/config/config.php';

/* DEBUG */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* METHOD CHECK */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

/* INPUT */
$items = json_decode($_POST['items_json'] ?? '', true);

if (!$items) {
    die("JSON ERROR: " . json_last_error_msg());
}

$grandTotal = (float)($_POST['grand_total'] ?? 0);
$amountPaid = (float)($_POST['amount_paid'] ?? 0);

if (!is_array($items) || count($items) === 0) {
    die('Invalid invoice items');
}

if ($grandTotal <= 0) {
    die('Invalid invoice total');
}

/* STATUS */
$status = ($amountPaid >= $grandTotal) ? 'paid' : 'unpaid';

/* CUSTOMER */
$customer_id = null;
$customer_name = null;
$customer_phone = null;

if (!empty($_POST['customer_id'])) {

    $stmt = $conn->prepare("SELECT id, name, phone FROM customers WHERE id=?");
    $stmt->bind_param("i", $_POST['customer_id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($c = $res->fetch_assoc()) {
        $customer_id = $c['id'];
        $customer_name = $c['name'];
        $customer_phone = $c['phone'];
    }

} else {
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
}

/* START TX */
$conn->begin_transaction();

try {

/* INSERT INVOICE */
$stmt = $conn->prepare("
INSERT INTO pos_invoices
(total_amount, customer_id, customer_name, customer_phone, status, created_at)
VALUES (?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param("disss",
    $grandTotal,
    $customer_id,
    $customer_name,
    $customer_phone,
    $status
);

$stmt->execute();
$invoiceId = $stmt->insert_id;

/* PREPARE ITEM */
$stmtItem = $conn->prepare("
INSERT INTO pos_invoice_items
(invoice_id, part_type, part_id, part_name, vehicle_stock_code, vehicle_name, quantity, price, cost_price, subtotal)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

/* LOOP ITEMS */
foreach ($items as $item) {

    $type = strtoupper($item['source'] ?? '');

    if ($type === 'SRC_STRIP') $type = 'STRIP';
    if ($type === 'SRC_OEM')   $type = 'OEM';
    if ($type === 'SRC_TP')    $type = 'TP';
    if ($type === 'SRC_NEW')   $type = 'NEW';

    $id = (int)$item['id'];
    $qty = (int)$item['qty'];
    $price = (float)$item['price'];
    $name = $item['name'];
    $vehicleCode = $item['vehicle_stock_code'] ?? '';
    $vehicleName = $item['vehicle_name'] ?? '';

    if ($id <= 0 || $qty <= 0) {
        throw new Exception("Invalid item data");
    }

    $subtotal = $qty * $price;

    /* MAP TABLE */
    switch ($type) {

        case 'OEM':
            $table = 'oem_parts';
            $stockField = 'stock_qty';
            $costField = 'cost_price';
        break;

        case 'NEW':
            $table = 'replacement_parts';
            $stockField = 'stock_qty';
            $costField = 'cost_price';
        break;

        case 'TP':
            $table = 'third_party_parts';
            $stockField = 'stock_status';
            $costField = 'cost_excl';
        break;

        case 'STRIP':
            $table = 'vehicle_stripped_parts';
            $stockField = 'qty';
            $costField = null;
        break;

        default:
            throw new Exception("Invalid part type");
    }

    /* FETCH STOCK */
    if ($costField) {

        $stmtStock = $conn->prepare("
        SELECT $stockField, $costField
        FROM $table
        WHERE id=?
        FOR UPDATE
        ");

    } else {

        $stmtStock = $conn->prepare("
        SELECT $stockField
        FROM $table
        WHERE id=?
        FOR UPDATE
        ");
    }

    $stmtStock->bind_param("i", $id);
    $stmtStock->execute();
    $resStock = $stmtStock->get_result();

    if (!$row = $resStock->fetch_assoc()) {
        throw new Exception("Part not found");
    }

    /* CHECK STOCK */
    if ($type == 'TP') {

        $statusCheck = strtoupper(trim($row[$stockField] ?? ''));

        if ($statusCheck !== 'IN_STOCK') {
            throw new Exception("Third party sold");
        }

    } else {

        if ((int)$row[$stockField] < $qty) {
            throw new Exception("Insufficient stock");
        }
    }

    /* COST */
    $cost = 0;
    if ($costField) {
        $cost = (float)$row[$costField];
    }

    /* INSERT ITEM */
    $stmtItem->bind_param("isisssiddd",
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

    /* UPDATE STOCK */
    if ($type == 'TP') {

        $u = $conn->prepare("UPDATE $table SET $stockField='SOLD' WHERE id=?");
        $u->bind_param("i", $id);
        $u->execute();

    } elseif ($type == 'STRIP') {

        $u = $conn->prepare("UPDATE vehicle_stripped_parts SET qty = qty - ? WHERE id=?");
        $u->bind_param("ii", $qty, $id);
        $u->execute();

    } else {

        $u = $conn->prepare("UPDATE $table SET $stockField = $stockField - ? WHERE id=?");
        $u->bind_param("ii", $qty, $id);
        $u->execute();

        /* ✅ STOCK LOG WITH PROFIT DATA */
        if ($type === 'OEM') {

            $ref = 'POS#'.$invoiceId;

            $log = $conn->prepare("
            INSERT INTO oem_stock_movements
            (part_id, movement_type, qty, reference, created_by, selling_price, cost_price)
            VALUES (?, 'SELL_OUT', ?, ?, 'system', ?, ?)
            ");

            $log->bind_param("iisdd", $id, $qty, $ref, $price, $cost);
            $log->execute();
        }
    }
}

/* COMMIT */
$conn->commit();

/* REDIRECT */
header("Location: print_invoice.php?invoice_id=" . $invoiceId);
exit;

} catch (Exception $e) {

$conn->rollback();
die("SYSTEM ERROR:<br>" . $e->getMessage());

}
?>