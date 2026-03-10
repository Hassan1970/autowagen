<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid part.");
}

$sql = "
    SELECT
        sp.*,
        v.stock_code,
        v.make,
        v.model,
        v.year,
        v.colour,
        c.name  AS category_name,
        sc.name AS subcategory_name,
        t.name  AS type_name,
        comp.name AS component_name
    FROM vehicle_stripped_parts sp
    LEFT JOIN vehicles v ON v.id = sp.vehicle_id
    LEFT JOIN categories c ON c.id = sp.category_id
    LEFT JOIN subcategories sc ON sc.id = sp.subcategory_id
    LEFT JOIN types t ON t.id = sp.type_id
    LEFT JOIN components comp ON comp.id = sp.component_id
    WHERE sp.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$part = $stmt->get_result()->fetch_assoc();

if (!$part) {
    die("Part not found.");
}

$vehicle_id = (int)$part['vehicle_id'];
?>
<!DOCTYPE html>
<html>
<head>
<title>View Stripped Part</title>
<style>
body { background:#000; color:#fff; font-family:Arial; }
.wrap {
    width:70%; margin:20px auto; padding:20px;
    background:#111; border-radius:10px; border:2px solid #b00000;
}
h2 { color:#ff3333; }
label { color:#ffcccc; font-weight:bold; display:block; margin-top:10px; }
.value { margin-bottom:5px; }
a.back { color:#ff3333; }
.photo-box {
    margin-top:10px;
    border:1px solid #b00000;
    padding:10px;
    border-radius:8px;
    text-align:center;
    background:#000;
}
.photo-box img {
    max-width:100%;
    max-height:350px;
}
</style>
</head>
<body>
<div class="wrap">
    <a href="stripped_parts_list.php?vehicle_id=<?= $vehicle_id ?>" class="back">
        ← Back to Stripped Parts
    </a>

    <h2>Stripped Part Details</h2>

    <label>Vehicle</label>
    <div class="value">
        <?= htmlspecialchars($part['stock_code']) ?> —
        <?= htmlspecialchars($part['make'] . ' ' . $part['model']) ?>
        (<?= htmlspecialchars($part['year']) ?>, <?= htmlspecialchars($part['colour']) ?>)
    </div>

    <label>Category / Sub / Type / Component</label>
    <div class="value">
        <?= htmlspecialchars($part['category_name']) ?> /
        <?= htmlspecialchars($part['subcategory_name']) ?> /
        <?= htmlspecialchars($part['type_name']) ?> /
        <?= htmlspecialchars($part['component_name']) ?>
    </div>

    <label>Part Name</label>
    <div class="value"><?= htmlspecialchars($part['part_name']) ?></div>

    <label>Quantity</label>
    <div class="value"><?= (int)$part['qty'] ?></div>

    <label>Condition</label>
    <div class="value"><?= htmlspecialchars($part['part_condition']) ?></div>

    <label>Location</label>
    <div class="value"><?= htmlspecialchars($part['location']) ?></div>

    <label>Notes</label>
    <div class="value"><?= nl2br(htmlspecialchars($part['notes'])) ?></div>

    <label>Date Stripped</label>
    <div class="value"><?= htmlspecialchars($part['date_stripped']) ?></div>

    <label>Photo</label>
    <div class="photo-box">
        <?php if (!empty($part['photo'])): ?>
            <img src="uploads/stripped_parts/<?= htmlspecialchars($part['photo']) ?>" alt="Part photo">
        <?php else: ?>
            <span style="color:#888;">No photo uploaded for this part.</span>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
