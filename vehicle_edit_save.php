<?php
require_once __DIR__ . '/config/config.php';

$id = (int)$_POST['id'];

$stmt = $conn->prepare("
UPDATE vehicles SET
    stock_code=?,
    make=?,
    model=?,
    variant=?,
    year=?,
    colour=?,
    mileage=?,
    vin_number=?,
    engine=?,
    purchase_use=?
WHERE id=?
");

$stmt->bind_param(
    "ssssisssssi",
    $_POST['stock_code'],
    $_POST['make'],
    $_POST['model'],
    $_POST['variant'],
    $_POST['year'],
    $_POST['colour'],
    $_POST['mileage'],
    $_POST['vin_number'],
    $_POST['engine'],
    $_POST['purchase_use'],
    $id
);

$stmt->execute();

header("Location: vehicle_view.php?id=$id");
exit;
