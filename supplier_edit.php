<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die("Invalid supplier ID."); }

$msg = "";

/* LOAD SUPPLIER */
$stmt = $conn->prepare("SELECT * FROM third_party_suppliers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$s = $res->fetch_assoc();
$stmt->close();

if (!$s) { die("Supplier not found."); }

/* SAVE CHANGES */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $supplier_name       = $_POST['supplier_name'];
    $supplier_type       = $_POST['supplier_type'];
    $contact_person      = $_POST['contact_person'];
    $phone               = $_POST['phone'];
    $email               = $_POST['email'];
    $address             = $_POST['address'];
    $vat_number          = $_POST['vat_number'];
    $company_reg_number  = $_POST['company_reg_number'];

    $sql = "UPDATE third_party_suppliers SET
                supplier_name = ?, supplier_type = ?, contact_person = ?, phone = ?, email = ?, 
                address = ?, vat_number = ?, company_reg_number = ?
            WHERE id = ? LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi",
        $supplier_name, $supplier_type, $contact_person, $phone,
        $email, $address, $vat_number, $company_reg_number, $id
    );

    if ($stmt->execute()) {
        $msg = "Supplier updated successfully.";
    } else {
        $msg = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Supplier</title>
<style>
body { background:#000; color:#fff; font-family:Arial; }
.wrap { width:95%; margin:25px auto; background:#111; padding:25px; border:1px solid #b00000; border-radius:8px; }
input, textarea {
    width:100%; padding:10px; margin-bottom:10px;
    background:#1a1a1a; border:1px solid #333; color:#fff;
}
label { color:#ff3333; }
.btn { background:#b00000; padding:10px 20px; border:none; color:#fff; border-radius:4px; cursor:pointer; }
.btn:hover { background:#ff0000; }
</style>
</head>
<body>

<div class="wrap">

<h1 style="color:#ff3333;">Edit Supplier</h1>

<?php if ($msg): ?>
<div style="background:#062; padding:10px; margin-bottom:10px; border:1px solid #0f0;"><?php echo $msg; ?></div>
<?php endif; ?>

<form method="POST">

<label>Supplier Name</label>
<input type="text" name="supplier_name" value="<?php echo h($s['supplier_name']); ?>" required>

<label>Supplier Type</label>
<input type="text" name="supplier_type" value="<?php echo h($s['supplier_type']); ?>">

<label>Contact Person</label>
<input type="text" name="contact_person" value="<?php echo h($s['contact_person']); ?>">

<label>Phone</label>
<input type="text" name="phone" value="<?php echo h($s['phone']); ?>">

<label>Email</label>
<input type="text" name="email" value="<?php echo h($s['email']); ?>">

<label>Address</label>
<textarea name="address"><?php echo h($s['address']); ?></textarea>

<label>VAT Number</label>
<input type="text" name="vat_number" value="<?php echo h($s['vat_number']); ?>">

<label>Company Registration Number</label>
<input type="text" name="company_reg_number" value="<?php echo h($s['company_reg_number']); ?>">

<button class="btn">Save Changes</button>
<a href="suppliers_list.php" class="btn" style="background:#444;">Back</a>

</form>

</div>
</body>
</html>
