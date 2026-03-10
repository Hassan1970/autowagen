<?php
require_once __DIR__ . '/config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ---------------- LOAD VEHICLES ---------------- */

$vehicles = [];
$res = $conn->query("
    SELECT 
        v.id,
        v.year,
        v.make,
        v.model,
        (
            SELECT COUNT(*) 
            FROM stripped_inventory si 
            WHERE si.vehicle_id = v.id
        ) AS stripped_count
    FROM vehicles v
    ORDER BY v.id DESC
");

while ($row = $res->fetch_assoc()) {
    $vehicles[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Select Vehicle to Strip</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background: #000;
        color: #fff;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        border: 1px solid #444;
        padding: 10px;
        text-align: center;
    }
    th {
        background: #111;
    }
    .btn {
        background: #d10000;
        color: #fff;
        padding: 8px 14px;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        display: inline-block;
    }
</style>
</head>

<body>

<h2 style="color:red;text-align:center;">Select a Vehicle to Strip</h2>

<table>
<thead>
<tr>
    <th>Year</th>
    <th>Make</th>
    <th>Model</th>
    <th>Already Stripped</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php foreach ($vehicles as $row): ?>

<?php
// 🔑 AUTO-DETECT MOBILE vs DESKTOP
$isMobile = preg_match('/Mobile|Android|iPhone|iPad/i', $_SERVER['HTTP_USER_AGENT']);

$link = $isMobile
    ? "/autowagen_master_clean/mobile/vehicle_stripping_mobile.php?vehicle_id={$row['id']}"
    : "/autowagen_master_clean/vehicle_stripping_entry.php?vehicle_id={$row['id']}";
?>

<tr>
    <td><?= htmlspecialchars($row['year']) ?></td>
    <td><?= htmlspecialchars($row['make']) ?></td>
    <td><?= htmlspecialchars($row['model']) ?></td>
    <td><?= (int)$row['stripped_count'] ?> parts</td>
    <td>
        <a href="<?= $link ?>" class="btn">
            Strip Vehicle
        </a>
    </td>
</tr>

<?php endforeach; ?>
</tbody>
</table>

</body>
</html>
