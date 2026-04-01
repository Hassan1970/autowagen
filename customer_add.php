<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $id_number = $_POST['id_number'] ?? '';
    $address = $_POST['address'] ?? '';

    if ($name != '') {

        $stmt = $conn->prepare("INSERT INTO customers (full_name, phone, id_number, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $phone, $id_number, $address);
        $stmt->execute();

        $new_id = $stmt->insert_id;

        echo "<script>
        window.opener.postMessage({
            type: 'CUSTOMER_SELECTED',
            customer: {
                id: '$new_id',
                full_name: '$name',
                phone: '$phone',
                id_number: '$id_number',
                address: '$address'
            }
        }, '*');
        window.close();
        </script>";

        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Customer</title>

<style>
body{
    background:#000;
    color:#fff;
    font-family:Arial;
    margin:0;
    padding:20px;
}

/* MAIN CARD */
.container{
    border:1px solid #ff0000;
    padding:20px;
    border-radius:10px;
    max-width:600px;
    margin:auto;
    background:#050505;
}

/* TITLE */
h2{
    margin-bottom:20px;
}

/* GRID */
.grid2{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

/* INPUTS */
input,textarea{
    width:100%;
    padding:10px;
    background:#0a0a0a;
    border:1px solid #333;
    color:#fff;
    margin-top:5px;
}

/* BUTTON */
button{
    margin-top:15px;
    padding:12px;
    background:#c00000;
    border:none;
    color:#fff;
    cursor:pointer;
    border-radius:6px;
}

button:hover{
    background:#ff0000;
}

/* LABEL */
label{
    font-size:14px;
}
</style>
</head>

<body>

<div class="container">

<h2>Add Customer</h2>

<form method="POST">

<div class="grid2">
<div>
<label>Name *</label>
<input type="text" name="name" required>
</div>

<div>
<label>Phone</label>
<input type="text" name="phone">
</div>
</div>

<label>ID Number</label>
<input type="text" name="id_number">

<label>Address</label>
<textarea name="address"></textarea>

<label>ID Document</label>
<input type="file">

<label>Proof of Residence</label>
<input type="file">

<button type="submit">Save Customer</button>

</form>

</div>

</body>
</html>