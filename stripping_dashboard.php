<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$sql = "
    SELECT 
        v.id,
        v.stock_code,
        v.make,
        v.model,
        v.year,
        v.colour,
        v.mileage,
        COUNT(sp.id) AS parts_count
    FROM vehicles v
    LEFT JOIN vehicle_stripped_parts sp ON sp.vehicle_id = v.id
    WHERE v.purchase_use = 'Stripping'
    GROUP BY v.id
    ORDER BY v.id DESC
";
$rows = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Stripping Dashboard</title>
<style>
body { background:#000; color:#fff; font-family:Arial; }
.wrap {
    width:90%; margin:20px auto; padding:20px;
    background:#111; border-radius:10px; border:2px solid #b00000;
}
h2 { color:#ff3333; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th, td { border:1px solid #b00000; padding:8px; font-size:13px; }
th { background:#b00000; text-align:left; }
.action-btn {
    padding:6px 10px; border-radius:5px;
    text-decoration:none; color:#fff; font-size:12px; margin-right:5px;
}
.strip { background:#ff6600; }
.view  { background:#0055ff; }
</style>
</head>
<body>
<div class="wrap">
    <h2>Stripping Dashboard</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Stock Code</th>
            <th>Vehicle</th>
            <th>Year</th>
            <th>Colour</th>
            <th>Mileage</th>
            <th>Stripped Parts</th>
            <th>Actions</th>
        </tr>
        <?php if ($rows->num_rows == 0): ?>
            <tr>
                <td colspan="8" style="text-align:center;color:#ff3333;">
                    No stripping vehicles found.
                </td>
            </tr>
        <?php else: ?>
            <?php while ($v = $rows->fetch_assoc()): ?>
                <tr>
                    <td><?= $v['id'] ?></td>
                    <td><?= htmlspecialchars($v['stock_code']) ?></td>
                    <td><?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?></td>
                    <td><?= htmlspecialchars($v['year']) ?></td>
                    <td><?= htmlspecialchars($v['colour']) ?></td>
                    <td><?= htmlspecialchars($v['mileage']) ?></td>
                    <td><?= (int)$v['parts_count'] ?></td>
                    <td>
                        <a class="action-btn strip"
                           href="vehicle_stripped_entry.php?vehicle_id=<?= $v['id'] ?>">
                           Strip Parts
                        </a>
                        <a class="action-btn view"
                           href="stripped_parts_list.php?vehicle_id=<?= $v['id'] ?>">
                           View Parts
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
