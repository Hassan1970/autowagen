<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $supplier_name       = $_POST['supplier_name'] ?? '';
    $supplier_type       = $_POST['supplier_type'] ?? '';
    $contact_person      = $_POST['contact_person'] ?? '';
    $phone               = $_POST['phone'] ?? '';
    $email               = $_POST['email'] ?? '';
    $address             = $_POST['address'] ?? '';
    $vat_number          = $_POST['vat_number'] ?? '';
    $company_reg_number  = $_POST['company_reg_number'] ?? '';

    $sql = "INSERT INTO third_party_suppliers 
            (supplier_name, supplier_type, contact_person, phone, email, address, vat_number, company_reg_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss",
        $supplier_name, $supplier_type, $contact_person, $phone,
        $email, $address, $vat_number, $company_reg_number
    );

    if ($stmt->execute()) {
        $msg = "Supplier added successfully.";
    } else {
        $msg = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Supplier</title>
<style>
body { background:#000; color:#fff; font-family:Arial, sans-serif; }
.wrap { width:95%; margin:25px auto; background:#111; padding:25px; border:1px solid #b00000; border-radius:8px; }
input, textarea, select {
    width:100%; padding:10px; margin-bottom:12px; 
    background:#1a1a1a; border:1px solid #333; color:#fff; border-radius:4px;
}
label { color:#ff3333; font-size:14px; }
.btn { background:#b00000; padding:10px 20px; border-radius:4px; color:#fff; border:none; cursor:pointer; }
.btn:hover { background:#ff0000; }
</style>
</head>
<body>

<div class="wrap">
<h1 style="color:#ff3333;">Add Supplier</h1>

<?php if ($msg): ?>
<div style="background:#062; padding:10px; margin-bottom:10px; border:1px solid #0f0;"><?php echo $msg; ?></div>
<?php endif; ?>

<form method="POST">

<label>Supplier Name</label>
<input type="text" name="supplier_name" required>

<label>Supplier Type</label>
<input type="text" name="supplier_type">

<label>Contact Person</label>
<input type="text" name="contact_person">

<label>Phone</label>
<input type="text" name="phone">

<label>Email</label>
<input type="text" name="email">

<label>Address</label>
<textarea name="address"></textarea>

<label>VAT Number</label>
<input type="text" name="vat_number">

<label>Company Registration Number</label>
<input type="text" name="company_reg_number">

<button class="btn">Save Supplier</button>
<a href="suppliers_list.php" class="btn" style="background:#444;">Back</a>

</form>
</div>
</body>
</html>
