<?php
require_once __DIR__ . '/config/config.php';

$page_title = "Vehicles";
include __DIR__ . '/includes/header.php';

/* Query */
$sql = "
    SELECT
        v.id,
        v.stock_code,
        v.make,
        v.model,
        v.year,
        v.purchase_use,
        v.photo_main,
        c.name AS customer_name
    FROM vehicles v
    LEFT JOIN vehicle_purchases vp ON vp.vehicle_id = v.id
    LEFT JOIN customers c ON c.id = vp.customer_id
    ORDER BY v.id DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<style>
.page-container { padding:20px; color:white; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th,.data-table td { padding:10px; border-bottom:1px solid #333; }
.data-table th { background:#111; }
.thumb { width:60px; border:1px solid #333; }
.no-photo { color:#777; }
.actions a { padding:6px 10px; margin-right:5px; text-decoration:none; border-radius:4px; }
.btn-view { background:#444; color:white; }
.btn-edit { background:#b00000; color:white; }
</style>

<div class="page-container">
<h1>Vehicles</h1>

<table class="data-table">
<thead>
<tr>
<th>Photo</th>
<th>Stock Code</th>
<th>Make / Model</th>
<th>Year</th>
<th>Use</th>
<th>Customer</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php if ($result->num_rows === 0): ?>
<tr><td colspan="7">No vehicles found</td></tr>
<?php endif; ?>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td>
<?php if ($row['photo_main']): ?>
<img src="uploads/vehicles/<?= h($row['photo_main']) ?>" class="thumb">
<?php else: ?>
<span class="no-photo">No photo</span>
<?php endif; ?>
</td>
<td><?= h($row['stock_code']) ?></td>
<td><?= h($row['make'] . ' ' . $row['model']) ?></td>
<td><?= h($row['year']) ?></td>
<td><?= h($row['purchase_use']) ?></td>
<td><?= h($row['customer_name'] ?? '-') ?></td>
<td class="actions">
<a class="btn-view" href="vehicle_view.php?id=<?= $row['id'] ?>">View</a>
<a class="btn-edit" href="vehicle_edit.php?id=<?= $row['id'] ?>">Edit</a>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
