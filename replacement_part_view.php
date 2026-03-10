<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("Invalid ID.");

$sql = "
SELECT p.*, 
       c.name AS category_name,
       sb.name AS subcategory_name,
       t.name AS type_name,
       cp.name AS component_name
FROM replacement_parts p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN subcategories sb ON sb.id = p.subcategory_id
LEFT JOIN types t ON t.id = p.type_id
LEFT JOIN components cp ON cp.id = p.component_id
WHERE p.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$part = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$part) die("Part not found.");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Replacement Part Details</title>
<style>
body { background:#000; color:#fff; font-family:Arial; }
.wrap { width:80%; margin:20px auto; background:#111; border:2px solid #b00000; padding:25px; border-radius:8px; }
.row { margin-bottom:10px; }
label { font-weight:bold; display:block; margin-bottom:5px; }
.value { background:#222; padding:7px; border:1px solid #333; border-radius:4px; }
.btn {
    padding:8px 14px;
    background:#b00000;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
    margin-right:10px;
}
</style>
</head>
<body>

<div class="wrap">

<h2 style="text-align:center;color:#ff3333;">Replacement Part Details</h2>

<div class="row">
    <label>Part Number</label>
    <div class="value"><?php echo h($part['part_number']); ?></div>
</div>

<div class="row">
    <label>Part Name</label>
    <div class="value"><?php echo h($part['part_name']); ?></div>
</div>

<div class="row">
    <label>Category</label>
    <div class="value"><?php echo h($part['category_name']); ?></div>
</div>

<div class="row">
    <label>Subcategory</label>
    <div class="value"><?php echo h($part['subcategory_name']); ?></div>
</div>

<div class="row">
    <label>Type</label>
    <div class="value"><?php echo h($part['type_name']); ?></div>
</div>

<div class="row">
    <label>Component</label>
    <div class="value"><?php echo h($part['component_name']); ?></div>
</div>

<div class="row">
    <label>Cost Price</label>
    <div class="value">R <?php echo number_format($part['cost_price'],2); ?></div>
</div>

<div class="row">
    <label>Selling Price</label>
    <div class="value">R <?php echo number_format($part['selling_price'],2); ?></div>
</div>

<div class="row">
    <label>Stock Qty</label>
    <div class="value"><?php echo h($part['stock_qty']); ?></div>
</div>

<div class="row">
    <label>Notes</label>
    <div class="value"><?php echo h($part['notes']); ?></div>
</div>

<br>

<a href="replacement_parts_list.php" class="btn">Back to List</a>
<a href="replacement_part_edit.php?id=<?php echo $id; ?>" class="btn" style="background:#1177dd;">Edit Part</a>

</div>

</body>
</html>

