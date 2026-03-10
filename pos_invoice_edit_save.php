<?php

require_once __DIR__ . '/config/config.php';

/* ==========================
READ JSON INPUT
========================== */

$data = json_decode(file_get_contents("php://input"), true);

$invoice_id = (int)$data['invoice_id'];
$items = $data['items'];

if($invoice_id<=0){
die("Invalid invoice");
}

if(!is_array($items)){
die("Invalid items");
}

/* ==========================
START TRANSACTION
========================== */

$conn->begin_transaction();

try{

$total = 0;

/* ==========================
UPDATE EACH ITEM
========================== */

foreach($items as $item){

$item_id = (int)$item['id'];
$qty = (int)$item['qty'];

if($qty<=0){
continue;
}

/* GET ITEM PRICE */

$stmt = $conn->prepare("
SELECT price
FROM pos_invoice_items
WHERE id=?
AND invoice_id=?
");

$stmt->bind_param("ii",$item_id,$invoice_id);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();

if(!$row){
throw new Exception("Item not found");
}

$price = (float)$row['price'];

$subtotal = $price * $qty;

$total += $subtotal;

/* UPDATE ITEM */

$stmtUpdate = $conn->prepare("
UPDATE pos_invoice_items
SET quantity=?, subtotal=?
WHERE id=?
");

$stmtUpdate->bind_param("idi",$qty,$subtotal,$item_id);
$stmtUpdate->execute();

}

/* ==========================
UPDATE INVOICE TOTAL
========================== */

$stmtTotal = $conn->prepare("
UPDATE pos_invoices
SET total_amount=?
WHERE id=?
");

$stmtTotal->bind_param("di",$total,$invoice_id);
$stmtTotal->execute();

/* ==========================
COMMIT
========================== */

$conn->commit();

echo "Invoice updated successfully";

}
catch(Exception $e){

$conn->rollback();

echo "Error: ".$e->getMessage();

}

?>