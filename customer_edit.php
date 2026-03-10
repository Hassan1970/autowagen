<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Invalid customer');
}

$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    die('Customer not found');
}

$c = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Customer</title>
</head>
<body>

<h2>Edit Customer</h2>

<form method="POST" action="customer_edit_save.php" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?= $c['id'] ?>">

    <label>Name</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" required><br><br>

    <label>Phone</label><br>
    <input type="text" name="phone" value="<?= htmlspecialchars($c['phone']) ?>" required><br><br>

    <label>ID Number</label><br>
    <input type="text" name="id_number" value="<?= htmlspecialchars($c['id_number']) ?>"><br><br>

    <label>Address</label><br>
    <textarea name="address" rows="4"><?= htmlspecialchars($c['address']) ?></textarea><br><br>

    <label>ID Document</label><br>
    <?php if ($c['id_document_file']): ?>
        <a href="<?= $c['id_document_file'] ?>" target="_blank">View current</a><br>
    <?php endif; ?>
    <input type="file" name="id_document"><br><br>

    <label>Proof of Residence</label><br>
    <?php if ($c['proof_residence_file']): ?>
        <a href="<?= $c['proof_residence_file'] ?>" target="_blank">View current</a><br>
    <?php endif; ?>
    <input type="file" name="proof_residence"><br><br>

    <button type="submit">Update Customer</button>

</form>

</body>
</html>
