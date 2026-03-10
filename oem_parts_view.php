<?php
require_once "config/config.php";
include "includes/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "
    SELECT 
        p.*, 
        c.name AS category_name,
        sc.name AS subcategory_name,
        t.name AS type_name,
        comp.name AS component_name
    FROM oem_parts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
    LEFT JOIN types t ON p.type_id = t.id
    LEFT JOIN components comp ON p.component_id = comp.id
    WHERE p.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("<h2 style='color:white;text-align:center;'>OEM Part not found</h2>");
}

$row = $res->fetch_assoc();
?>

<style>
.wrap {
    width: 70%;
    margin: auto;
    background: #111;
    padding: 25px;
    border: 2px solid #b00000;
    border-radius: 10px;
    color: white;
}
h2 {
    color: #ff3333;
    text-align: center;
}
label {
    font-weight: bold;
    color: #ff3333;
}
.value {
    color: #fff;
    margin-bottom: 10px;
    display: block;
}
.btn {
    background: #b00000;
    color: #fff;
    padding: 8px 14px;
    border-radius: 5px;
    text-decoration: none;
}
.btn:hover {
    background: #ff3333;
}
</style>

<div class="wrap">
    <h2>OEM Part Details</h2>

    <label>OEM Number:</label>
    <div class="value"><?= htmlspecialchars($row['oem_number']) ?></div>

    <label>Part Name:</label>
    <div class="value"><?= htmlspecialchars($row['part_name']) ?></div>

    <label>Category:</label>
    <div class="value"><?= $row['category_name'] ?: "-" ?></div>

    <label>Subcategory:</label>
    <div class="value"><?= $row['subcategory_name'] ?: "-" ?></div>

    <label>Type:</label>
    <div class="value"><?= $row['type_name'] ?: "-" ?></div>

    <label>Component:</label>
    <div class="value"><?= $row['component_name'] ?: "-" ?></div>

    <label>Stock Quantity:</label>
    <div class="value"><?= $row['stock_qty'] ?></div>

    <label>Cost Price:</label>
    <div class="value">R <?= number_format($row['cost_price'], 2) ?></div>

    <label>Selling Price:</label>
    <div class="value">R <?= number_format($row['selling_price'], 2) ?></div>

    <a class="btn" href="oem_parts_edit.php?id=<?= $row['id'] ?>">Edit</a>
    <a class="btn" href="oem_parts_list.php">Back</a>
</div>
