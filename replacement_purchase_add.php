<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $part_number   = $_POST['part_number'] ?? '';
    $part_name     = $_POST['part_name'] ?? '';
    $category_id   = $_POST['category_id'] ?? null;
    $subcategory_id= $_POST['subcategory_id'] ?? null;
    $type_id       = $_POST['type_id'] ?? null;
    $component_id  = $_POST['component_id'] ?? null;
    $cost_price    = $_POST['cost_price'] ?? 0;
    $sell_price    = $_POST['sell_price'] ?? 0;
    $stock_qty     = $_POST['stock_qty'] ?? 0;
    $notes         = $_POST['notes'] ?? '';

    // IMAGE UPLOAD
    $photoPath = null;

    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = __DIR__ . "/uploads/replacement_parts/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = time() . "_" . basename($_FILES['photo']['name']);
        $target = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $photoPath = "uploads/replacement_parts/" . $filename;
        }
    }

    // Insert part
    $stmt = $conn->prepare("
        INSERT INTO replacement_parts 
        (part_number, part_name, category_id, subcategory_id, type_id, component_id,
         cost_price, sell_price, stock_qty, notes, photo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssiiiiddiss",
        $part_number,
        $part_name,
        $category_id,
        $subcategory_id,
        $type_id,
        $component_id,
        $cost_price,
        $sell_price,
        $stock_qty,
        $notes,
        $photoPath
    );

    if ($stmt->execute()) {
        echo "<div style='background:#0a0;border:2px solid #0f0;padding:10px;color:#fff;margin:20px auto;width:50%;text-align:center;'>
                Replacement Part Added Successfully!
              </div>";
    } else {
        echo "<div style='background:#700;border:2px solid #f00;padding:10px;color:#fff;margin:20px auto;width:50%;text-align:center;'>
                ERROR: " . $stmt->error . "
              </div>";
    }

    $stmt->close();
}
?>

<style>
body {
    background:#111;
    color:#fff;
    font-family:Arial, sans-serif;
}
.wrap {
    width:70%;
    margin:30px auto;
    background:#181818;
    border:2px solid #b00000;
    padding:25px;
    border-radius:10px;
}
h2 {
    text-align:center;
    margin-bottom:20px;
    color:#ff3b3b;
}
label {
    display:block;
    margin-top:10px;
}
input, select, textarea {
    width:100%;
    padding:8px;
    margin-top:5px;
    background:#000;
    border:1px solid #555;
    color:#fff;
    border-radius:4px;
}
button {
    width:100%;
    padding:12px;
    background:#d90000;
    border:none;
    margin-top:20px;
    font-size:16px;
    cursor:pointer;
    border-radius:6px;
    color:white;
}
button:hover {
    background:#ff0000;
}
</style>

<div class="wrap">
<h2>Add Replacement Part</h2>

<form method="post" enctype="multipart/form-data">

    <label>Part Number</label>
    <input type="text" name="part_number" required>

    <label>Part Name</label>
    <input type="text" name="part_name" required>

    <label>Cost Price</label>
    <input type="number" step="0.01" name="cost_price" required>

    <label>Selling Price</label>
    <input type="number" step="0.01" name="sell_price" required>

    <label>Opening Stock (Quantity Received)</label>
    <input type="number" name="stock_qty" required>

    <label>Notes</label>
    <textarea name="notes" rows="3"></textarea>

    <label>Upload Photo</label>
    <input type="file" name="photo" accept="image/*">

    <hr style="border-color:#333;margin:20px 0;">

    <!-- EPC CASCADING FIELDS -->
    <?php include __DIR__ . '/includes/epc_cascade_fields.php'; ?>

    <button type="submit">Save Replacement Part</button>
</form>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
