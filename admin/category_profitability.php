<?php
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../includes/header.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Category Profitability</title>

    <style>
        body {
            background:#000;
            color:#fff;
            font-family:Arial, sans-serif;
        }

        .wrap {
            width:90%;
            margin:30px auto;
        }

        h1 {
            color:#ff3333;
            margin-bottom:15px;
        }

        table {
            width:100%;
            border-collapse:collapse;
            font-size:14px;
        }

        th, td {
            border:1px solid #b00000;
            padding:8px;
            text-align:left;
        }

        th {
            background:#1b1b1b;
            color:#ff3333;
        }

        td.amount {
            text-align:right;
            font-weight:bold;
        }

        .note {
            margin-top:10px;
            font-size:12px;
            color:#bbb;
        }
    </style>
</head>

<body>

<div class="wrap">

    <h1>Category Profitability (Stripped Parts Revenue)</h1>

    <?php
    $sql = "
        SELECT
            c.id AS category_id,
            c.name AS category_name,
            COALESCE(SUM(pii.subtotal), 0) AS total_revenue
        FROM categories c
        LEFT JOIN vehicle_stripped_parts vsp
            ON vsp.category_id = c.id
        LEFT JOIN stripped_inventory si
            ON si.stripped_part_id = vsp.id
        LEFT JOIN pos_invoice_items pii
            ON pii.part_type = 'STRIPPED'
           AND pii.part_id = si.id
        GROUP BY c.id, c.name
        ORDER BY total_revenue DESC
    ";

    $res = $conn->query($sql);
    ?>

    <table>
        <thead>
            <tr>
                <th>Category ID</th>
                <th>Category</th>
                <th>Total Revenue (R)</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($res && $res->num_rows > 0): ?>
            <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$row['category_id'] ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td class="amount">
                        R <?= number_format($row['total_revenue'], 2) ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No data found.</td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>

    <div class="note">
        Revenue shown is based only on <strong>sold stripped parts</strong> (POS invoices).  
        No cost is deducted here by design.
    </div>

</div>

</body>
</html>
