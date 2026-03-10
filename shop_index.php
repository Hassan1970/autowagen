<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple loads for now – no filters yet
// OEM Parts
$sql_oem = "
    SELECT op.*, c.name AS category_name
    FROM oem_parts op
    LEFT JOIN categories c ON op.category_id = c.id
    ORDER BY op.part_name ASC
";
$res_oem = $conn->query($sql_oem);

// Replacement Parts
$sql_rep = "
    SELECT rp.*, c.name AS category_name
    FROM replacement_parts rp
    LEFT JOIN categories c ON rp.category_id = c.id
    ORDER BY rp.part_name ASC
";
$res_rep = $conn->query($sql_rep);

function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Online Shop - Parts Catalogue</title>
    <style>
        body {
            background:#000;
            color:#fff;
            font-family:Arial, sans-serif;
            margin:0;
            padding:0;
        }

        .shop-wrap {
            width:95%;
            max-width:1200px;
            margin:25px auto 40px;
        }

        .shop-header {
            text-align:center;
            margin-bottom:20px;
        }

        .shop-header h1 {
            margin:0;
            font-size:24px;
            color:#ff3333;
        }

        .shop-header p {
            margin:5px 0 0 0;
            color:#ccc;
            font-size:13px;
        }

        .shop-card {
            background:#111;
            border:2px solid #b00000;
            border-radius:10px;
            padding:15px 20px;
            margin-bottom:25px;
        }

        .shop-card h2 {
            margin:0 0 10px 0;
            font-size:18px;
            color:#ff3333;
            text-align:left;
        }

        .shop-card small {
            color:#aaa;
            font-size:11px;
        }

        table.shop-table {
            width:100%;
            border-collapse:collapse;
            font-size:13px;
            margin-top:8px;
        }

        table.shop-table th,
        table.shop-table td {
            border:1px solid #b00000;
            padding:6px 8px;
            vertical-align:top;
        }

        table.shop-table th {
            background:#1b1b1b;
            color:#ff3333;
            text-align:left;
        }

        .price {
            font-weight:bold;
        }

        .price-oem {
            color:#ffd633;
        }

        .price-rep {
            color:#66ff66;
        }

        .stock-ok {
            color:#66ff66;
            font-weight:bold;
        }

        .stock-zero {
            color:#ff6666;
            font-weight:bold;
        }

        .badge {
            display:inline-block;
            padding:2px 6px;
            border-radius:4px;
            font-size:11px;
            background:#222;
            color:#ccc;
        }

        .badge-oem {
            background:#333366;
            color:#dde4ff;
        }

        .badge-rep {
            background:#224422;
            color:#d4ffd4;
        }

        .btn-view {
            display:inline-block;
            padding:4px 8px;
            font-size:11px;
            border-radius:4px;
            text-decoration:none;
            background:#b00000;
            color:#fff;
        }

        .btn-view:hover {
            background:#ff1a1a;
        }

        .shop-grid {
            display:flex;
            flex-wrap:wrap;
            gap:20px;
        }

        .shop-grid .col {
            flex:1 1 0;
            min-width:300px;
        }

        .top-nav-link {
            display:inline-block;
            margin-bottom:10px;
            color:#ff3333;
            text-decoration:none;
            font-size:13px;
        }
        .top-nav-link:hover {
            text-decoration:underline;
        }

    </style>
</head>
<body>

<div class="shop-wrap">

    <a href="index.php" class="top-nav-link">&laquo; Back to main system</a>

    <div class="shop-header">
        <h1>Autowagen Online Shop</h1>
        <p>Browse OEM and Replacement parts available in your stock database.</p>
    </div>

    <div class="shop-grid">

        <!-- OEM PARTS -->
        <div class="col">
            <div class="shop-card">
                <h2>OEM Parts <span class="badge badge-oem">Original</span></h2>
                <small>These parts are stored in your <strong>oem_parts</strong> table.</small>

                <?php if ($res_oem && $res_oem->num_rows > 0): ?>
                    <table class="shop-table">
                        <tr>
                            <th>#</th>
                            <th>Part</th>
                            <th>Category</th>
                            <th>OEM No.</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>View</th>
                        </tr>
                        <?php while ($row = $res_oem->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= h($row['part_name']) ?></td>
                                <td><?= h($row['category_name']) ?></td>
                                <td><?= h($row['oem_number']) ?></td>
                                <td>
                                    <?php if ((int)$row['stock_qty'] > 0): ?>
                                        <span class="stock-ok"><?= (int)$row['stock_qty'] ?></span>
                                    <?php else: ?>
                                        <span class="stock-zero">Out of stock</span>
                                    <?php endif; ?>
                                </td>
                                <td class="price price-oem">
                                    R <?= number_format((float)$row['selling_price'], 2) ?>
                                </td>
                                <td>
                                    <!-- placeholder for a future product page -->
                                    <a href="#"
                                       class="btn-view"
                                       title="Product page coming soon">
                                       View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p style="margin-top:10px;color:#ccc;">No OEM parts found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- REPLACEMENT PARTS -->
        <div class="col">
            <div class="shop-card">
                <h2>Replacement Parts <span class="badge badge-rep">Aftermarket</span></h2>
                <small>These parts are stored in your <strong>replacement_parts</strong> table.</small>

                <?php if ($res_rep && $res_rep->num_rows > 0): ?>
                    <table class="shop-table">
                        <tr>
                            <th>#</th>
                            <th>Part</th>
                            <th>Category</th>
                            <th>Part No.</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>View</th>
                        </tr>
                        <?php while ($row = $res_rep->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= h($row['part_name']) ?></td>
                                <td><?= h($row['category_name']) ?></td>
                                <td><?= h($row['part_number']) ?></td>
                                <td>
                                    <?php if ((int)$row['stock_qty'] > 0): ?>
                                        <span class="stock-ok"><?= (int)$row['stock_qty'] ?></span>
                                    <?php else: ?>
                                        <span class="stock-zero">Out of stock</span>
                                    <?php endif; ?>
                                </td>
                                <td class="price price-rep">
                                    R <?= number_format((float)$row['selling_price'], 2) ?>
                                </td>
                                <td>
                                    <!-- placeholder for a future product page -->
                                    <a href="#"
                                       class="btn-view"
                                       title="Product page coming soon">
                                       View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p style="margin-top:10px;color:#ccc;">No replacement parts found.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
</body>
</html>
