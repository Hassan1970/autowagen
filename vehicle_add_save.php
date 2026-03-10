<?php
require_once __DIR__ . '/config/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vehicle_add.php');
    exit;
}

$conn->begin_transaction();

try {

    /* ============================
       1️⃣ CUSTOMER INSERT
       ============================ */

    $customer_name    = trim($_POST['customer_name'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');

    if ($customer_name === '' || $customer_address === '') {
        throw new Exception('Customer name and address are required.');
    }

    // Upload directories
    $baseDir = __DIR__ . '/uploads/customers/';
    $idDir   = $baseDir . 'id_docs/';
    $resDir  = $baseDir . 'proof_residence/';

    foreach ([$idDir, $resDir] as $d) {
        if (!is_dir($d)) mkdir($d, 0777, true);
    }

    $id_document_file = null;
    $proof_residence_file = null;

    // ID document
    if (!empty($_FILES['id_document']['name'])) {
        $ext = strtolower(pathinfo($_FILES['id_document']['name'], PATHINFO_EXTENSION));
        $id_document_file = 'id_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['id_document']['tmp_name'], $idDir . $id_document_file);
    }

    // Proof of residence
    if (!empty($_FILES['proof_of_residence']['name'])) {
        $ext = strtolower(pathinfo($_FILES['proof_of_residence']['name'], PATHINFO_EXTENSION));
        $proof_residence_file = 'res_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['proof_of_residence']['tmp_name'], $resDir . $proof_residence_file);
    }

    $stmt = $conn->prepare("
        INSERT INTO customers (name, address, id_document_file, proof_residence_file)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'ssss',
        $customer_name,
        $customer_address,
        $id_document_file,
        $proof_residence_file
    );
    $stmt->execute();
    $customer_id = $stmt->insert_id;
    $stmt->close();


    /* ============================
       2️⃣ VEHICLE INSERT
       ============================ */

    $stmt = $conn->prepare("
        INSERT INTO vehicles (
            stock_code, make, model, brand_name, variant,
            year, fuel_type, transmission, number_doors,
            engine, engine_number, engine_capacity,
            vin_number, colour, mileage, notes, purchase_use, photo_main
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $photo_main = null;

    // Main photo
    if (!empty($_FILES['photo_main']['name'])) {
        $vehDir = __DIR__ . '/uploads/vehicles/main/';
        if (!is_dir($vehDir)) mkdir($vehDir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['photo_main']['name'], PATHINFO_EXTENSION));
        $photo_main = time() . '_' . $_FILES['photo_main']['name'];
        move_uploaded_file($_FILES['photo_main']['tmp_name'], $vehDir . $photo_main);
    }

    $stmt->bind_param(
        'ssssssssisssssisss',
        $_POST['stock_code'],
        $_POST['make'],
        $_POST['model'],
        $_POST['brand_name'],
        $_POST['variant'],
        $_POST['year'],
        $_POST['fuel_type'],
        $_POST['transmission'],
        $_POST['number_doors'],
        $_POST['engine'],
        $_POST['engine_number'],
        $_POST['engine_capacity'],
        $_POST['vin_number'],
        $_POST['colour'],
        $_POST['mileage'],
        $_POST['notes'],
        $_POST['purchase_use'],
        $photo_main
    );

    $stmt->execute();
    $vehicle_id = $stmt->insert_id;
    $stmt->close();


    /* ============================
       3️⃣ VEHICLE PURCHASE LINK
       ============================ */

    $stmt = $conn->prepare("
        INSERT INTO vehicle_purchases
        (vehicle_id, customer_id, receipt_no, date_purchased, notes)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        'iisss',
        $vehicle_id,
        $customer_id,
        $_POST['customer_receipt_no'],
        $_POST['date_purchased'],
        $_POST['notes']
    );

    $stmt->execute();
    $stmt->close();


    /* ============================
       4️⃣ COMMIT & REDIRECT
       ============================ */

    $conn->commit();
    header('Location: vehicles_list.php?added=1');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    echo '<pre style="color:red;font-size:16px;">STAGE 4 ERROR: ' . $e->getMessage() . '</pre>';
}
