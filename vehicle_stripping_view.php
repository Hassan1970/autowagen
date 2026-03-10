<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

// -------------------------------
// LOAD STRIPPED VEHICLES
// -------------------------------
$sql = "
    SELECT v.id, v.stock_code, v.make, v.model, v.year, v.mileage,
           COUNT(sp.id) AS part_count
    FROM vehicles v
    LEFT JOIN vehicle_stripped_parts sp ON sp.vehicle_id = v.id
    GROUP BY v.id
    HAVING part_count > 0
    ORDER BY v.id DESC
";
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Stripped Vehicles</title>

<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, sans-serif;
}

.page-title {
    text-align:center;
    font-size:28px;
    margin:25px 0;
    color:#ff3333;
    font-weight:bold;
}

/* CARD GRID */
.grid {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));
    gap:25px;
    width:90%;
    margin:0 auto;
}

/* VEHICLE CARD */
.card {
    background:#111;
    border:1px solid #b00000;
    border-radius:10px;
    padding:15px;
    transition:0.3s;
}
.card:hover {
    transform:scale(1.03);
}

/* IMAGE */
.card img {
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:8px;
    border:1px solid #222;
}

/* TITLE */
.card-title {
    font-size:20px;
    margin:10px 0;
    color:#ff3333;
}

/* ACTION BUTTONS */
.btn {
    display:inline-block;
    padding:8px 12px;
    margin:4px 2px;
    border-radius:6px;
    text-decoration:none;
    font-size:14px;
}
.btn-view {
    background:#b00000;
    color:#fff;
}
.btn-view:hover {
    background:#ff3333;
}
</style>
</head>

<body>

<h1 class="page-title">Stripped Vehicles</h1>

<div class="grid">

<?php
if ($res && $res->num_rows > 0) {
    while ($v = $res->fetch_assoc()) {

        // -------------------------------
        // GET FIRST VEHICLE PHOTO
        // -------------------------------
        $img = "https://via.placeholder.com/280x200?text=No+Photo"; // default

        $imgQuery = $conn->query("
            SELECT file_name FROM vehicle_photos
            WHERE vehicle_id = {$v['id']}
            ORDER BY id ASC LIMIT 1
        ");

        if ($imgQuery && $imgQuery->num_rows > 0) {
            $imgRow = $imgQuery->fetch_assoc();
            $img = "uploads/vehicles/" . $imgRow['file_name'];
        }

        // Vehicle title
        $title = "{$v['stock_code']} ({$v['make']} {$v['model']} {$v['year']})";

        echo "
        <div class='card'>
            <img src='{$img}' alt='Vehicle Photo'>

            <div class='card-title'>{$title}</div>

            <p><b>Mileage:</b> {$v['mileage']}</p>
            <p><b>Total Parts Stripped:</b> {$v['part_count']}</p>

            <a class='btn btn-view' href='vehicle_stripped_list.php?vehicle_id={$v['id']}'>
                View Stripped Parts
            </a>
        </div>";
    }
} else {
    echo "<p style='text-align:center;color:#ff3333;font-size:18px;'>No stripped vehicles found.</p>";
}
?>

</div>
</body>
</html>
