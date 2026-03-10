<?php
require_once __DIR__ . '/config/config.php';

$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);
if ($vehicle_id <= 0) {
    die("Invalid vehicle");
}

/* Load vehicle */
$v = $conn->query("SELECT stock_code FROM vehicles WHERE id = $vehicle_id")->fetch_assoc();
$stock = $v['stock_code'] ?? "V-$vehicle_id";

/* Load customers — USE CORRECT COLUMN */
$customers = $conn->query("
    SELECT id, name
    FROM customers
    ORDER BY name
");

/* Save link */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)$_POST['customer_id'];

    $conn->query("
        INSERT INTO vehicle_purchases (vehicle_id, customer_id)
        VALUES ($vehicle_id, $customer_id)
        ON DUPLICATE KEY UPDATE customer_id = $customer_id
    ");

    header("Location: vehicle_view.php?id=$vehicle_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Link Customer</title>
<style>
body { background:#000; color:white; font-family:Arial; }
.box { width:400px; margin:100px auto; padding:20px; border:2px solid red; background:#111; }
select,button { width:100%; padding:10px; margin-top:10px; }
button { background:red; color:white; border:none; }
</style>
</head>
<body>

<div class="box">
<h2>Link Customer to <?= htmlspecialchars($stock) ?></h2>

<form method="post">
    <select name="customer_id" required>
        <option value="">Select customer</option>
        <?php while($c = $customers->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Link Customer</button>
</form>
</div>

</body>
</html>
