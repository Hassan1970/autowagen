<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('h')) {
    function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

// --------------------------------------------
// LOAD FILTERS
// --------------------------------------------
$q            = trim($_GET['q']            ?? '');
$filterCat    = (int)($_GET['category_id'] ?? 0);
$filterCond   = trim($_GET['condition']    ?? '');
$filterStock  = trim($_GET['stock_code']   ?? '');
$filterSold   = trim($_GET['sold_status']  ?? '');

// LOAD CATEGORIES
$cats = [];
$resCats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($row = $resCats->fetch_assoc()) { $cats[] = $row; }

// -------------------------------------------------
// BASE QUERY
// -------------------------------------------------
$sql = "
    SELECT 
        si.*,
        v.make,
        v.model,
        v.year,
        c.name AS category_name,
        sc.name AS subcategory_name,
        t.name AS type_name,
        comp.name AS component_name
    FROM stripped_inventory si
    LEFT JOIN vehicles v ON si.vehicle_id = v.id
    LEFT JOIN categories c ON si.category_id = c.id
    LEFT JOIN subcategories sc ON si.subcategory_id = sc.id
    LEFT JOIN types t ON si.type_id = t.id
    LEFT JOIN components comp ON si.component_id = comp.id
    WHERE 1 = 1
";

$where = [];
$params = [];
$types = "";

// SEARCH
if ($q !== '') {
    $where[] = "(si.part_name LIKE ? OR v.stock_code LIKE ? OR v.make LIKE ? OR v.model LIKE ?)";
    $like = "%{$q}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= "ssss";
}

// STOCK CODE FILTER
if ($filterStock !== '') {
    $where[] = "v.stock_code = ?";
    $params[] = $filterStock;
    $types .= "s";
}

// CATEGORY
if ($filterCat > 0) {
    $where[] = "si.category_id = ?";
    $params[] = $filterCat;
    $types .= "i";
}

// CONDITION
if ($filterCond !== '') {
    $where[] = "si.part_condition = ?";
    $params[] = $filterCond;
    $types .= "s";
}

// SOLD / AVAILABLE FILTER
if ($filterSold === 'AVAILABLE' || $filterSold === 'SOLD') {
    $where[] = "si.sold_status = ?";
    $params[] = $filterSold;
    $types .= "s";
}

// APPLY WHERE
if ($where) { $sql .= " AND " . implode(" AND ", $where); }

$sql .= " ORDER BY si.id DESC LIMIT 500";

// PREPARE QUERY
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stripped Parts Inventory</title>
    <style>
        body { background:#000; color:#fff; font-family:Arial; margin:0; padding:0; }
        .wrap { width:90%; margin:25px auto; }

        h2 { margin-top:0; color:#ff3333; }

        .card {
            background:#111;
            border:2px solid #b00000;
            border-radius:10px;
            padding:15px 20px;
            margin-bottom:20px;
        }

        .filters { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:15px; }
        .filters label { font-size:11px; color:#ccc; }
        .filters input, .filters select {
            background:#000; color:#fff; border:1px solid #444;
            padding:6px 8px; border-radius:6px; font-size:12px;
        }

        table.table { width:100%; border-collapse:collapse; font-size:12px; margin-top:10px; }
        table.table th, table.table td { border:1px solid #b00000; padding:7px; }
        table.table th { background:#1b1b1b; color:#ff3333; }

        .badge { padding:2px 6px; border-radius:4px; font-size:10px; }
        .badge-loc { background:#333; color:#fff; }

        .status-available { background:#064f1c; color:#c8ffc8; }
        .status-sold { background:#b00000; color:#fff; }

        .btn-small {
            padding:4px 7px;
            font-size:11px;
            border-radius:5px;
            text-decoration:none;
            color:#fff;
            border:1px solid #555;
            margin-right:4px;
        }
        .btn-secondary { background:#222; }
        .btn-danger { background:#b00000; }
    </style>
</head>
<body>

<div class="wrap">

    <h2>Stripped Inventory</h2>

    <?php if (isset($_GET['sold'])): ?>
        <p style="color:#00ff00;">✔ Item marked as SOLD</p>
    <?php endif; ?>

    <?php if (isset($_GET['undo'])): ?>
        <p style="color:#00ff00;">✔ Item restored to AVAILABLE</p>
    <?php endif; ?>

    <div class="card">
        <form class="filters" method="get">

            <div>
                <label>Search</label>
                <input type="text" name="q" value="<?= h($q) ?>" placeholder="search part / stock">
            </div>

            <div>
                <label>Stock Code</label>
                <input type="text" name="stock_code" value="<?= h($filterStock) ?>">
            </div>

            <div>
                <label>Category</label>
                <select name="category_id">
                    <option value="0">All</option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filterCat == $c['id'] ? 'selected' : '' ?>>
                            <?= h($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Status</label>
                <select name="sold_status">
                    <option value="">All</option>
                    <option value="AVAILABLE" <?= $filterSold==='AVAILABLE'?'selected':'' ?>>Available</option>
                    <option value="SOLD" <?= $filterSold==='SOLD'?'selected':'' ?>>Sold</option>
                </select>
            </div>

            <div><button class="btn-small btn-secondary">Filter</button></div>
            <div><a href="stripped_inventory_list.php" class="btn-small btn-secondary">Reset</a></div>

        </form>
    </div>

    <div class="card">

        <?php if ($res->num_rows == 0): ?>
            <p>No inventory items found.</p>
        <?php else: ?>

        <table class="table">
            <tr>
                <th>ID</th>
                <th>Stock</th>
                <th>Vehicle</th>
                <th>EPC</th>
                <th>Part</th>
                <th>Status</th>
                <th>Qty</th>
                <th>Position</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>

            <?php while($row = $res->fetch_assoc()): ?>
                <?php
                    $status = strtoupper($row['sold_status']);
                    $statusClass = $status === 'SOLD' ? "badge status-sold" : "badge status-available";
                ?>

                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= h($row['stock_code']) ?></td>
                    <td><?= h($row['make']." ".$row['model']." ".$row['year']) ?></td>

                    <td>
                        <?= h($row['category_name']) ?><br>
                        <?= h($row['subcategory_name']) ?><br>
                        <?= h($row['type_name']) ?><br>
                        <?= h($row['component_name']) ?>
                    </td>

                    <td><?= h($row['part_name']) ?></td>

                    <td><span class="<?= $statusClass ?>"><?= $status ?></span></td>

                    <td><?= $row['qty'] ?></td>

                    <td><?= h($row['position_code']) ?></td>

                    <td>
                        <?php if ($row['location']): ?>
                            <span class="badge-loc"><?= h($row['location']) ?></span>
                        <?php endif; ?>
                    </td>

                    <td>

                        <!-- EDIT -->
                        <a href="inventory_edit.php?id=<?= $row['id'] ?>" class="btn-small btn-secondary">Edit</a>

                        <!-- PHOTOS -->
                        <a href="inventory_photos.php?id=<?= $row['id'] ?>" class="btn-small btn-secondary">Photos</a>

                        <!-- BARCODE -->
                        <a href="barcode_label.php?id=<?= $row['id'] ?>" 
                           target="_blank"
                           class="btn-small btn-secondary">
                           Barcode
                        </a>

                        <!-- MARK SOLD -->
                        <?php if ($status === 'AVAILABLE'): ?>
                            <a href="mark_as_sold.php?id=<?= $row['id'] ?>"
                               class="btn-small btn-secondary"
                               onclick="return confirm('Mark this part as SOLD?');">
                               Mark SOLD
                            </a>
                        <?php endif; ?>

                        <!-- UNDO SOLD -->
                        <?php if ($status === 'SOLD'): ?>
                            <a href="unmark_sold.php?id=<?= $row['id'] ?>"
                               class="btn-small btn-secondary"
                               onclick="return confirm('Undo SOLD status?');">
                               Undo SOLD
                            </a>
                        <?php endif; ?>

                        <!-- DELETE -->
                        <a href="delete_inventory.php?id=<?= $row['id'] ?>"
                           class="btn-small btn-danger"
                           onclick="return confirm('Delete this item?');">
                           Delete
                        </a>

                        <!-- VEHICLE -->
                        <a href="vehicle_profile.php?vehicle_id=<?= $row['vehicle_id'] ?>"
                           class="btn-small btn-secondary">
                           Vehicle
                        </a>

                    </td>
                </tr>

            <?php endwhile; ?>

        </table>

        <?php endif; ?>

    </div>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
