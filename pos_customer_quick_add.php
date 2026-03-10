<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json');

try{

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
echo json_encode(['status'=>'error','msg'=>'Invalid request']);
exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

if($name === ''){
echo json_encode(['status'=>'error','msg'=>'Customer name required']);
exit;
}

/* FIXED COLUMN NAME */
$stmt = $conn->prepare("
INSERT INTO customers (name, phone, address)
VALUES (?, ?, ?)
");

$stmt->bind_param("sss", $name, $phone, $address);
$stmt->execute();

$id = $stmt->insert_id;

echo json_encode([
'status'=>'ok',
'id'=>$id,
'name'=>$name,
'phone'=>$phone,
'address'=>$address
]);

}catch(Throwable $e){

echo json_encode([
'status'=>'error',
'msg'=>'Server error: '.$e->getMessage()
]);

}