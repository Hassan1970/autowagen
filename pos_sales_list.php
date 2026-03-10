<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

$result = $conn->query("
SELECT 
    id,
    customer_name,
    total_amount,
    status,
    created_at
FROM pos_invoices
ORDER BY id DESC
LIMIT 200
");
?>

<!DOCTYPE html>
<html>
<head>
<title>POS / Sales</title>

<style>

body{
background:#000;
color:#fff;
font-family:Arial;
}

h2{
margin-bottom:20px;
}

table{
width:100%;
border-collapse:collapse;
}

th,td{
border:1px solid #333;
padding:10px;
text-align:left;
}

th{
background:#111;
}

tr:hover{
background:#1a1a1a;
}

.view-btn{
background:#0066cc;
color:#fff;
padding:6px 10px;
text-decoration:none;
border-radius:4px;
}

.edit-btn{
background:#b00000;
color:#fff;
padding:6px 10px;
text-decoration:none;
border-radius:4px;
}

.status-paid{
color:#00ff88;
}

.status-unpaid{
color:#ff4444;
}

</style>
</head>

<body>

<h2>POS / Sales — Invoice History</h2>

<table>

<tr>
<th>Invoice #</th>
<th>Date</th>
<th>Customer</th>
<th>Status</th>
<th>Total</th>
<th>View</th>
<th>Edit</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td>
INV-<?= str_pad($row['id'],6,'0',STR_PAD_LEFT) ?>
</td>

<td>
<?= $row['created_at'] ?>
</td>

<td>
<?= htmlspecialchars($row['customer_name'] ?? '') ?>
</td>

<td>
<span class="status-<?= $row['status'] ?>">
<?= strtoupper($row['status']) ?>
</span>
</td>

<td>
R <?= number_format($row['total_amount'],2) ?>
</td>

<td>
<a class="view-btn"
href="print_invoice.php?invoice_id=<?= $row['id'] ?>">
View
</a>
</td>

<td>
<a class="edit-btn"
href="pos_invoice_edit.php?invoice_id=<?= $row['id'] ?>">
Edit
</a>
</td>

</tr>

<?php endwhile; ?>

</table>

</body>
</html>