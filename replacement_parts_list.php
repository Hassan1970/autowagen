<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set("display_errors", 1);

// SAFE query (works with old + new structure)
$q = $conn->query("
    SELECT 
        p.id,
        p.part_number,
        p.part_name,
        p.selling_price,
        p.cost_price,
        p.stock_qty,

        -- If category_id is NULL, show p.category
        COALESCE(c.name, p.category) AS category_name,
        COALESCE(s.name, p.subcategory) AS subcat_name,
        COALESCE(t.name, p.type) AS type_name,
        COALESCE(comp.name, p.component) AS comp_name

    FROM replacement_parts p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN subcategories s ON s.id = p.subcategory_id
    LEFT JOIN types t ON t.id = p.type_id
    LEFT JOIN components comp ON comp.id = p.component_id
    ORDER BY p.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Replacement Parts List</title>
    <meta charset="utf-8">
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: Arial;
        }
        .wrap {
            width: 95%;
            margin: 20px auto;
            background: #111;
            border: 2px solid #b00000;
            padding: 15px;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: #222;
            color: #ff3333;
            border-bottom: 2px solid #b00000;
            padding: 10px;
        }
        td {
            background: #1a1a1a;
            border-bottom: 1px solid #333;
            padding: 8px;
        }
        a {
            color: #0ff;
            text-decoration: none;
        }
        a:hover {
            color: #ff0;
        }
        .btn-add {
            display: inline-block;
            padding: 10px 15px;
            background: #b00000;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 15px;
        }
        .btn-add:hover {
            background: #ff0000;
        }
    </style>
</head>
<body>

<div class="wrap">
    <h2 style="text-align:center;color:#ff3333;">Replacement Parts List</h2>

    <a href="add_replacement_part.php" class="btn-add">+ Add Replacement Part</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Part Number</th>
            <th>Part Name</th>
            <th>Category</th>
            <th>Subcategory</th>
            <th>Type</th>
            <th>Component</th>
            <th>Cost</th>
            <th>Selling</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $q->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['part_number']) ?></td>
            <td><?= htmlspecialchars($row['part_name']) ?></td>
            <td><?= htmlspecialchars($row['category_name']) ?></td>
            <td><?= htmlspecialchars($row['subcat_name']) ?></td>
            <td><?= htmlspecialchars($row['type_name']) ?></td>
            <td><?= htmlspecialchars($row['comp_name']) ?></td>
            <td>R <?= number_format($row['cost_price'], 2) ?></td>
            <td>R <?= number_format($row['selling_price'], 2) ?></td>
            <td><?= $row['stock_qty'] ?></td>

            <td>
                <a href="replacement_part_view.php?id=<?= $row['id'] ?>">View</a> |
                <a href="replacement_part_edit.php?id=<?= $row['id'] ?>">Edit</a> |
                <a href="replacement_part_delete.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('Delete this part?');"
                   style="color:#f33;">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>
</div>

</body>
</html>
