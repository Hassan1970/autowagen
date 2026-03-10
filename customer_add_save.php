<?php
require_once __DIR__ . '/config/config.php';

$name      = $_POST['name'] ?? '';
$phone     = $_POST['phone'] ?? '';
$id_number = $_POST['id_number'] ?? '';
$address   = $_POST['address'] ?? '';
$return    = $_POST['return'] ?? 'customer_list.php';

if(trim($name) === ''){
    die("Customer name is required.");
}

/* INSERT CUSTOMER */

$stmt = $conn->prepare("
    INSERT INTO customers 
    (name, phone, id_number, address) 
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param("ssss", $name, $phone, $id_number, $address);
$stmt->execute();

$newCustomerId = $stmt->insert_id;

$stmt->close();

/* IF OPENED FROM INVOICE */

if($return === 'invoice'){
?>
<script>
window.opener.postMessage({
    type: "CUSTOMER_SELECTED",
    id: <?= $newCustomerId ?>,
    full_name: "<?= addslashes($name) ?>",
    phone: "<?= addslashes($phone) ?>",
    address: "<?= addslashes($address) ?>",
    id_number: "<?= addslashes($id_number) ?>"
}, "*");

window.close();
</script>
<?php
exit;
}

/* NORMAL REDIRECT */

header("Location: customer_list.php");
exit;