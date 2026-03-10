<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$rows = $conn->query("
    SELECT id, stock_code, make, model, variant, year, colour, mileage
    FROM vehicles
    WHERE purchase_use = 'Stripping'
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vehicles</title>

<style>
/* 🔒 FORCE OLD WORKING LAYOUT */
body {
    background:#000;
    color:#fff;
    margin:0;
    padding:0;
}

/* ISOLATE FROM GLOBAL CSS */
.wrap {
    width:90%;
    margin:30px auto;
    padding:20px;
    background:#111;
    border:2px solid #b00000;
    border-radius:10px;
}

/* HEADINGS */
.wrap h2 {
    color:#ff3333;
    margin-bottom:10px;
}

/* ADD LINK */
.add-link {
    display:inline-block;
    margin-bottom:15px;
    color:#ff3333;
    font-size:16px;
    font-weight:bold;
    text-decoration:none;
}

/* TABLE */
.wrap table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.wrap th,
.wrap td {
    border:1px solid #b00000;
    padding:10px;
    font-size:14px;
    text-align:left;
}

.wrap th {
    background:#b00000;
    color:#fff;
}

/* ACTION LINKS */
.action-btn {
    margin-right:10px;
    text-decoration:none;
    color:#00aaff;
    font-weight:bold;
}

.strip-btn {
    color:#ff6600;
    font-weight:bold;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="wrap">
    <h2>Vehicles</h2>
    <a href="vehicle_add.php" class="add-link">+ Add Vehicle</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Photo</th>
            <th>Stock Code</th>
            <th>Make</th>
            <th>Model</th>
            <th>Variant</th>
            <th>Year</th>
            <th>Colour</th>
            <th>Mileage</th>
            <th>Actions</th>
        </tr>

        <?php if (!$rows || $rows->num_rows === 0): ?>
            <tr>
                <td colspan="10" style="text-align:center;color:#ff3333;">
                    No vehicles found.
                </td>
            </tr>
        <?php else: ?>
            <?php while ($v = $rows->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$v['id'] ?></td>
                <td>No photo</td>
                <td><?= htmlspecialchars($v['stock_code'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['make'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['model'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['variant'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['year'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['colour'] ?? '') ?></td>
                <td><?= htmlspecialchars($v['mileage'] ?? '') ?></td>
                <td>
                    <a class="action-btn" href="vehicle_profile.php?vehicle_id=<?= $v['id'] ?>">View</a>
                    <a class="action-btn" href="vehicle_edit.php?id=<?= $v['id'] ?>">Edit</a>
                    <a class="action-btn"
                       href="vehicle_delete.php?id=<?= $v['id'] ?>"
                       onclick="return confirm('Delete this vehicle?');">
                       Delete
                    </a>
                    <a class="strip-btn"
                       href="vehicle_stripping_entry.php?vehicle_id=<?= $v['id'] ?>">
                       ✂ Strip
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
