<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die("Invalid ID."); }

$stmt = $conn->prepare("SELECT * FROM third_party_suppliers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$s = $res->fetch_assoc();
$stmt->close();

if (!$s) { die("Supplier not found."); }

function row($title, $value) {
    echo "<tr><th>$title</th><td>$value</td></tr>";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>View Supplier</title>
<style>
body { background:#000; color:#fff; font-family:Arial; }
.wrap { width:90%; margin:25px auto; background:#111; padding:25px; border:1px solid #b00000; border-radius:8px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:10px; border-bottom:1px solid #333; }
th { width:220px; text-align:left; color:#ff3333; }
.btn { padding:10px 20px; background:#444; color:#fff; border-radius:4px; text-decoration:none; }
.btn-red { background:#b00000; }
</style>
</head>
<body>

<div class="wrap">

<h1 style="color:#ff3333;">Supplier Details</h1>

<table>
<?php
row("Supplier Name", h($s['supplier_name']));
row("Supplier Type", h($s['supplier_type']));
row("Contact Person", h($s['contact_person']));
row("Phone", h($s['phone']));
row("Email", h($s['email']));
row("Address", nl2br(h($s['address'])));
row("VAT Number", h($s['vat_number']));
row("Company Registration #", h($s['company_reg_number']));
row("Created", h($s['created_at']));
?>
</table>

<br>

<a href="supplier_edit.php?id=<?php echo $s['id']; ?>" class="btn btn-red">Edit Supplier</a>
<a href="suppliers_list.php" class="btn">Back to List</a>

</div>

</body>
</html>
