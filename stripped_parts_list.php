<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

/* -----------------------------
   SEARCH
----------------------------- */
$search = trim($_GET['q'] ?? '');
$where  = '1=1';
$params = [];
$types  = '';

if ($search !== '') {
    $where .= " AND (
        v.stock_code LIKE ?
        OR v.make LIKE ?
        OR v.model LIKE ?
        OR sp.part_name LIKE ?
    )";
    $like   = "%{$search}%";
    $params = [$like, $like, $like, $like];
    $types  = 'ssss';
}

/* -----------------------------
   QUERY
----------------------------- */
$sql = "
SELECT
    sp.id,
    sp.vehicle_id,
    sp.part_name,
    sp.location,
    sp.photo,
    sp.date_stripped,
    v.stock_code
FROM vehicle_stripped_parts sp
JOIN vehicles v ON v.id = sp.vehicle_id
WHERE {$where}
ORDER BY sp.date_stripped DESC
";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Stripped Parts</title>

<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, sans-serif;
}

.wrap {
    width:95%;
    margin:25px auto;
}

table {
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

th, td {
    border:1px solid #b00000;
    padding:8px;
}

th {
    background:#1a1a1a;
    color:#ff3333;
}

.thumb {
    width:70px;
    height:50px;
    object-fit:cover;
    border:1px solid #b00000;
    border-radius:4px;
}

.no-photo {
    color:#999;
    font-size:12px;
}
</style>
</head>

<body>

<div class="wrap">

<h1 style="color:#ff3333;">Stripped Parts</h1>

<table>
<tr>
    <th>#</th>
    <th>Vehicle</th>
    <th>Part</th>
    <th>Location</th>
    <th>Photo</th>
    <th>Date</th>
</tr>

<?php if ($result->num_rows === 0): ?>
<tr>
    <td colspan="6" style="text-align:center;color:#aaa;">No stripped parts found</td>
</tr>
<?php endif; ?>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= (int)$row['id'] ?></td>
    <td><?= h($row['stock_code']) ?></td>
    <td><?= h($row['part_name']) ?></td>
    <td><?= h($row['location']) ?></td>

    <td>
        <?php if (!empty($row['photo']) && file_exists(__DIR__ . '/' . $row['photo'])): ?>
            <a href="<?= h($row['photo']) ?>" target="_blank">
                <img src="<?= h($row['photo']) ?>" class="thumb">
            </a>
        <?php else: ?>
            <span class="no-photo">No photo</span>
        <?php endif; ?>
    </td>

    <td><?= h($row['date_stripped']) ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>

</body>
</html>

<?php include __DIR__ . "/includes/footer.php"; ?>
