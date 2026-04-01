<?php
require_once __DIR__ . '/config/config.php';

$page_title = "Vehicle Dashboard";
include __DIR__ . '/includes/header.php';

/* ================= MAIN QUERY ================= */
$sql = "
SELECT 
    v.id,
    v.stock_code,
    v.make,
    v.model,
    IFNULL(v.purchase_price,0) AS purchase_price,

    COUNT(DISTINCT p.id) AS total_parts,
    IFNULL(SUM(p.qty),0) AS total_qty,

    IFNULL(SUM(ii.qty * ii.selling_price),0) AS revenue

FROM vehicles v

LEFT JOIN vehicle_stripped_parts p 
    ON p.vehicle_id = v.id

LEFT JOIN invoice_items ii 
    ON ii.part_name = p.part_name

GROUP BY v.id
ORDER BY v.id DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("SQL Error: " . $conn->error);
}

/* ================= SUMMARY ================= */
$summary = $conn->query("
SELECT 
    COUNT(DISTINCT v.id) AS vehicles,
    IFNULL(SUM(p.qty),0) AS total_qty
FROM vehicles v
LEFT JOIN vehicle_stripped_parts p ON p.vehicle_id = v.id
")->fetch_assoc();

?>

<style>
.page {
    padding:25px;
    color:#fff;
    font-family:Arial;
}

/* KPI */
.kpi-row {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.kpi {
    background:#111;
    border:1px solid #b00000;
    padding:20px;
    border-radius:10px;
    text-align:center;
}

.kpi h2 {
    margin:0;
    font-size:28px;
    color:#ff3333;
}

.kpi span {
    font-size:12px;
    color:#aaa;
}

/* VEHICLE CARD */
.vehicle-card {
    background:#0d0d0d;
    border:1px solid #b00000;
    border-radius:10px;
    padding:18px;
    margin-bottom:15px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.vehicle-info strong {
    font-size:18px;
}

/* STATS */
.stats {
    display:flex;
    gap:25px;
    align-items:center;
}

.stat {
    text-align:center;
}

.stat .num {
    font-size:16px;
    font-weight:bold;
}

.stat .label {
    font-size:11px;
    color:#888;
}

/* PROFIT */
.profit { color:#4cff88; }
.loss { color:#ff4c4c; }

/* STATUS */
.badge {
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
}

.in-stock {
    background:#143d2b;
    color:#4cff88;
}

.empty {
    background:#3d1414;
    color:#ff4c4c;
}

/* BUTTON */
.btn {
    background:#b00000;
    color:#fff;
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
}
</style>

<div class="page">

<h1>Vehicle Dashboard</h1>

<!-- KPI -->
<div class="kpi-row">

<div class="kpi">
<h2><?= $summary['vehicles'] ?></h2>
<span>Total Vehicles</span>
</div>

<div class="kpi">
<h2><?= $summary['total_qty'] ?></h2>
<span>Total Stock Qty</span>
</div>

</div>

<!-- VEHICLE LIST -->
<?php while($row = $result->fetch_assoc()): 

$profit = $row['revenue'] - $row['purchase_price'];
?>

<div class="vehicle-card">

<div class="vehicle-info">
<strong><?= htmlspecialchars($row['stock_code']) ?></strong><br>
<?= htmlspecialchars($row['make']) ?> <?= htmlspecialchars($row['model']) ?>
</div>

<div class="stats">

<div class="stat">
<div class="num"><?= $row['total_parts'] ?></div>
<div class="label">Parts</div>
</div>

<div class="stat">
<div class="num"><?= $row['total_qty'] ?></div>
<div class="label">Qty</div>
</div>

<div class="stat">
<div class="num">R <?= number_format($row['revenue'],2) ?></div>
<div class="label">Revenue</div>
</div>

<div class="stat">
<div class="num <?= $profit >= 0 ? 'profit' : 'loss' ?>">
R <?= number_format($profit,2) ?>
</div>
<div class="label">Profit</div>
</div>

<div>
<span class="badge <?= $row['total_qty'] > 0 ? 'in-stock' : 'empty' ?>">
<?= $row['total_qty'] > 0 ? 'IN STOCK' : 'EMPTY' ?>
</span>
</div>

<div>
<a class="btn" href="vehicle_view.php?id=<?= $row['id'] ?>">
VIEW
</a>
</div>

</div>

</div>

<?php endwhile; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>