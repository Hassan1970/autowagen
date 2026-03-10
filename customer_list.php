<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

$result = $conn->query("
    SELECT id, name, phone, id_number, is_active, created_at
    FROM customers
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers</title>
</head>
<body>

<h2>Customers</h2>

<a href="customer_add.php">➕ Add New Customer</a>

<br><br>

<table border="1" width="100%" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Phone</th>
        <th>ID Number</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['id_number']) ?></td>
            <td>
                <?= $row['is_active'] ? 'Active' : 'Deactivated' ?>
            </td>
            <td>
                <a href="customer_edit.php?id=<?= $row['id'] ?>">Edit</a>
                |
                <?php if ($row['is_active']): ?>
                    <a href="customer_toggle.php?id=<?= $row['id'] ?>&action=deactivate"
                       onclick="return confirm('Deactivate this customer?')">
                       Deactivate
                    </a>
                <?php else: ?>
                    <a href="customer_toggle.php?id=<?= $row['id'] ?>&action=activate"
                       onclick="return confirm('Activate this customer?')">
                       Activate
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6">No customers found</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
