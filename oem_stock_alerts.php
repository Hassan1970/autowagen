<?php
require_once "config/config.php";
include "includes/header.php";

if (!function_exists('h')) {
    function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

$low_threshold = 2;

// Out of stock
$sqlOut = "
    SELECT id, oem_number, part_name, stock_qty, selling_price
    FROM oem_parts
    WHERE stock_qty <= 0
    ORDER BY part_name ASC
";
$outRes = $conn->query($sqlOut);

// Low stock
$sqlLow = "
    SELECT id, oem_number, part_name, stock_qty, selling_price
    FROM oem_parts
    WHERE stock_qty > 0 AND stock_qty <= {$low_threshold}
    ORDER BY stock_qty ASC, part_name ASC
";
$lowRes = $conn->query($sqlLow);
?>
<style>
.page {
    width:95%;
    margin:20px auto;
    background:#111;
    border:2px solid #b00000;
    color:#fff;
    padding:20px;
    border-radius:8px;
}
.page h2 {
    text-align:center;
    color:#ff3333;
    margin-bottom:20px;
}
.section-title {
    margin-top:10px;
    font-size:16px;
    color:#ff6666;
}
table.report {
    width:100%;
    border-collapse:collapse;
    margin-top:8px;
    font-size:13px;
}
table.report th, table.report td {
    border:1px solid #333;
    padding:6px 8px;
}
table.report th {
    background:#222;
    color:#ffdddd;
}
table.report tr:nth-child(even) { background:#181818; }
table.report tr:nth-child(odd) { background:#151515; }
td.num { text-align:right; }
.badge-out {
    background:#5a0505;
    color:#ffc2c2;
    padding:2px 6px;
    border-radius:4px;
    font-size:11px;
}
.badge-low {
    background:#5a3b05;
    color:#ffe7a9;
    padding:2px 6px;
    border-radius:4px;
    font-size:11px;
}
.note {
    font-size:12px;
    color:#999;
    margin-top:8px;
}
</style>

<div class="page">
    <h2>OEM Stock Alerts</h2>

    <div class="note">
        Threshold: low stock is &le; <?php echo (int)$low_threshold; ?> units.
        You can adjust this in <code>$low_threshold</code> at the top of this file.
    </div>

    <div class="section-title">🚨 Out of Stock</div>
    <?php if ($outRes->num_rows === 0): ?>
        <p>No OEM parts are completely out of stock.</p>
    <?php else: ?>
        <table class="report">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>OEM #</th>
                    <th>Part Name</th>
                    <th>Stock Qty</th>
                    <th>Sell Price</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $outRes->fetch_assoc()): ?>
                <tr>
                    <td><span class="badge-out">Out of stock</span></td>
                    <td><?php echo h($p['oem_number']); ?></td>
                    <td><?php echo h($p['part_name']); ?></td>
                    <td class="num"><?php echo (int)$p['stock_qty']; ?></td>
                    <td class="num">R <?php echo number_format($p['selling_price'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="section-title">⚠ Low Stock</div>
    <?php if ($lowRes->num_rows === 0): ?>
        <p>No OEM parts are below the low-stock threshold.</p>
    <?php else: ?>
        <table class="report">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>OEM #</th>
                    <th>Part Name</th>
                    <th>Stock Qty</th>
                    <th>Sell Price</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $lowRes->fetch_assoc()): ?>
                <tr>
                    <td><span class="badge-low">Low</span></td>
                    <td><?php echo h($p['oem_number']); ?></td>
                    <td><?php echo h($p['part_name']); ?></td>
                    <td class="num"><?php echo (int)$p['stock_qty']; ?></td>
                    <td class="num">R <?php echo number_format($p['selling_price'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php
$conn->close();
