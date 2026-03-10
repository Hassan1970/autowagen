<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$sup = $conn->query("
    SELECT id, supplier_name, contact_person, phone, email, address
    FROM third_party_suppliers
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>3rd Party Suppliers</title>

<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, sans-serif;
    margin:0;
    padding:0;
}

.page-wrap {
    width:95%;
    margin:40px auto;
}

/* Title */
.page-wrap h1 {
    font-size:32px;
    color:#ff3333;
    margin-bottom:10px;
}

/* ADD BUTTON */
.add-btn {
    color:#ff3333;
    font-size:16px;
    text-decoration:none;
}
.add-btn:hover {
    text-decoration:underline;
}

/* TABLE */
.table-box {
    background:#111;
    border:2px solid #b00000;
    border-radius:10px;
    margin-top:20px;
    overflow-x:auto;
}

table {
    width:100%;
    border-collapse:collapse;
}

th {
    background:#0d0d0d;
    color:#ff3333;
    padding:12px;
    font-size:15px;
    border-bottom:1px solid #b00000;
    text-align:left;
}

td {
    padding:12px;
    border-bottom:1px solid #222;
    color:#fff;
    font-size:14px;
}

.actions a {
    margin-right:15px;
    color:#ff3333;
    text-decoration:none;
    font-weight:bold;
}

.actions a:hover {
    text-decoration:underline;
}
</style>
</head>

<body>

<div class="page-wrap">

    <h1>3rd Party Suppliers</h1>
    <p>List of all 3rd party suppliers used for 3rd party parts.</p>

    <a class="add-btn" href="third_party_supplier_add.php">+ Add Supplier</a>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supplier Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($row = $sup->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['supplier_name'] ?></td>
                    <td><?= $row['contact_person'] ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['address'] ?></td>

                    <td class="actions" style="text-align:center;">
                        <a href="third_party_supplier_view.php?id=<?= $row['id'] ?>">View</a>
                        <a href="third_party_supplier_edit.php?id=<?= $row['id'] ?>">Edit</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>

        </table>
    </div>

</div>

</body>
</html>
