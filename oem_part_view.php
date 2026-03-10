<?php
require_once "config/config.php";
include "includes/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid OEM part.");
}

$sql = "
    SELECT 
        p.*,
        c.name  AS category_name,
        s.name  AS subcategory_name,
        t.name  AS type_name,
        cmp.name AS component_name
    FROM oem_parts p
    LEFT JOIN categories c    ON p.category_id = c.id
    LEFT JOIN subcategories s ON p.subcategory_id = s.id
    LEFT JOIN types t         ON p.type_id = t.id
    LEFT JOIN components cmp  ON p.component_id = cmp.id
    WHERE p.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$part = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$part) {
    die("OEM part not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>View OEM Part</title>
    <style>
        .oem-view-wrap {
            width: 70%;
            margin: 20px auto;
            background:#000;
            color:#fff;
            border:2px solid #b00000;
            border-radius:6px;
            padding:20px 25px 30px;
        }
        h1 {
            margin-top:0;
            color:#ff3333;
            font-size:22px;
            text-align:center;
            margin-bottom:20px;
        }
        .row {
            display:grid;
            grid-template-columns: 180px 1fr;
            row-gap:8px;
        }
        .label {
            font-weight:bold;
        }
        .btn-bar {
            margin-top:20px;
            text-align:right;
        }
        .btn {
            padding:7px 12px;
            border:none;
            border-radius:4px;
            cursor:pointer;
            font-size:13px;
            text-decoration:none;
            display:inline-block;
        }
        .btn-back {
            background:#555;
            color:#fff;
            margin-right:6px;
        }
        .btn-edit {
            background:#ff3333;
            color:#fff;
        }
    </style>
</head>
<body>
<div class="oem-view-wrap">
    <h1>OEM Part Details</h1>

    <div class="row">
        <div class="label">ID</div>
        <div><?php echo (int)$part['id']; ?></div>

        <div class="label">OEM Number</div>
        <div><?php echo htmlspecialchars($part['oem_number']); ?></div>

        <div class="label">Part Name</div>
        <div><?php echo htmlspecialchars($part['part_name']); ?></div>

        <div class="label">Category</div>
        <div><?php echo htmlspecialchars($part['category_name'] ?? ""); ?></div>

        <div class="label">Subcategory</div>
        <div><?php echo htmlspecialchars($part['subcategory_name'] ?? ""); ?></div>

        <div class="label">Type</div>
        <div><?php echo htmlspecialchars($part['type_name'] ?? ""); ?></div>

        <div class="label">Component</div>
        <div><?php echo htmlspecialchars($part['component_name'] ?? ""); ?></div>

        <div class="label">Stock Qty</div>
        <div><?php echo (int)$part['stock_qty']; ?></div>

        <div class="label">Cost Price</div>
        <div>R <?php echo number_format((float)$part['cost_price'], 2); ?></div>

        <div class="label">Selling Price</div>
        <div>R <?php echo number_format((float)$part['selling_price'], 2); ?></div>

        <div class="label">Created At</div>
        <div><?php echo htmlspecialchars($part['created_at']); ?></div>
    </div>

    <div class="btn-bar">
        <a href="oem_parts_list.php" class="btn btn-back">Back</a>
        <a href="oem_parts_edit.php?id=<?php echo (int)$part['id']; ?>" class="btn btn-edit">Edit</a>
    </div>
</div>
</body>
</html>
<?php
$conn->close();
?>
