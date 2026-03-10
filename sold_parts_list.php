<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

if (!function_exists('h')) {
    function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

$search     = $_GET['search'] ?? '';
$stock_code = $_GET['stock_code'] ?? '';
$date_from  = $_GET['date_from'] ?? '';
$date_to    = $_GET['date_to'] ?? '';

$sql = "
    SELECT 
        s.*, 
        v.make, v.model, v.year,
        c.name AS category_name,
        sc.name AS subcategory_name,
        t.name AS type_name,
        comp.name AS component_name
    FROM sold_inventory s
    LEFT JOIN stripped_inventory si ON si.id = s.inventory_id
    LEFT JOIN vehicles v ON s.vehicle_id = v.id
    LEFT JOIN categories c ON si.category_id = c.id
    LEFT JOIN subcategories sc ON si.subcategory_id = sc.id
    LEFT JOIN types t ON si.type_id = t.id
    LEFT JOIN components comp ON si.component_id = comp.id
    WHERE 1
";

if ($search !== '') {
    $like = "%".$conn->real_escape_string($search)."%";
    $sql .= " AND (
                s.part_name LIKE '$like'
                OR v.make LIKE '$like'
                OR v.model LIKE '$like'
                OR v.year LIKE '$like'
                OR s.stock_code LIKE '$like'
            )";
}

if ($stock_code !== '') {
    $sc = $conn->real_escape_string($stock_code);
    $sql .= " AND s.stock_code = '$sc' ";
}

if ($date_from !== '') {
    $df = $conn->real_escape_string($date_from);
    $sql .= " AND s.sold_date >= '$df' ";
}

if ($date_to !== '') {
    $dt = $conn->real_escape_string($date_to);
    $sql .= " AND s.sold_date <= '$dt' ";
}

$sql .= " ORDER BY s.sold_date DESC, s.id DESC ";

$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sold Parts History</title>

<style>
body { background:#000; color:#fff; font-family:Arial; }

.wrap {
    width: 92%;
    margin: 25px auto;
    padding: 20px;
    background:#111;
    border:2px solid #b00000;
    border-radius:10px;
}

h2 { margin-top:0; color:#ff3333; }

.filter-row {
    display:flex;
    gap:10px;
    margin-bottom:15px;
    align-items:center;
}

.filter-row input {
    background:#222; border:1px solid #444; color:white;
    padding:7px; border-radius:6px;
}

.btn-small {
    padding:7px 14px;
    background:#222;
    border:1px solid #555;
    border-radius:6px;
    text-decoration:none;
    color:white;
    font-size:12px;
}
.btn-small:hover { background:#333; }

.btn-red {
    background:#b00000 !important;
    border-color:#ff5555 !important;
    font-weight:bold;
}

table {
    width:100%; border-collapse:collapse;
    margin-top:15px;
}

th, td {
    border:1px solid #b00000;
    padding:8px; font-size:13px;
}
th { background:#1f1f1f; color:#ff3333; }
</style>
</head>

<body>
<div class="wrap">

    <h2>Sold Parts History</h2>

    <!-- FILTER BAR -->
    <form method="get">
        <div class="filter-row">

            <input type="text" name="search" placeholder="Part / Make / Model / Stock"
                   value="<?= h($search) ?>" style="flex:1;">

            <input type="text" name="stock_code" placeholder="Stock Code"
                   value="<?= h($stock_code) ?>" style="width:130px;">

            <input type="date" name="date_from" value="<?= h($date_from) ?>">
            <input type="date" name="date_to" value="<?= h($date_to) ?>">

            <button class="btn-small">Filter</button>

            <a href="sold_parts_list.php" class="btn-small">Reset</a>

            <!-- PDF BUTTON -->
            <a href="sold_parts_export_pdf.php" target="_blank"
               class="btn-small btn-red">
               ⬇ PDF
            </a>

        </div>
    </form>

<?php if ($res->num_rows == 0): ?>
    <p>No sold parts found.</p>

<?php else: ?>

<table>
<tr>
    <th>ID</th>
    <th>Sold Date</th>
    <th>Stock</th>
    <th>Vehicle</th>
    <th>EPC</th>
    <th>Part</th>
    <th>Qty</th>
    <th>Condition</th>
    <th>Position</th>
    <th>Location</th>
    <th>Notes</th>
    <th>Actions</th>
</tr>

<?php while($row = $res->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= h($row['sold_date']) ?></td>
    <td><?= h($row['stock_code']) ?></td>
    <td><?= h($row['make']." ".$row['model']." ".$row['year']) ?></td>

    <td>
        <?= h($row['category_name']) ?><br>
        <?= h($row['subcategory_name']) ?><br>
        <?= h($row['type_name']) ?><br>
        <?= h($row['component_name']) ?>
    </td>

    <td><?= h($row['part_name']) ?></td>
    <td><?= h($row['qty']) ?></td>
    <td><?= h($row['part_condition']) ?></td>
    <td><?= h($row['position_code']) ?></td>
    <td><?= h($row['location']) ?></td>
    <td><?= h($row['sold_notes']) ?></td>

    <td>
        <a href="invoice.php?sold_id=<?= $row['id'] ?>" class="btn-small btn-red">Invoice</a>
        <a href="vehicle_profile.php?vehicle_id=<?= $row['vehicle_id'] ?>" class="btn-small">Vehicle</a>
        <a href="inventory_edit.php?id=<?= $row['inventory_id'] ?>" class="btn-small">Inventory</a>
    </td>
</tr>
<?php endwhile; ?>

</table>
<?php endif; ?>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
