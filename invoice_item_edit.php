<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid item ID.");
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $part_type   = $_POST['part_type'] ?? 'OEM';
    $part_name   = trim($_POST['part_name'] ?? '');
    $cost_price  = (float)($_POST['cost_price'] ?? 0);
    $qty         = (int)($_POST['qty'] ?? 1);
    $notes       = trim($_POST['notes'] ?? '');

    if ($id > 0 && $part_name !== '') {
        $stmt = $conn->prepare("
            UPDATE supplier_invoice_items
            SET part_type = ?, part_name = ?, cost_price = ?, qty = ?, notes = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssdiss", $part_type, $part_name, $cost_price, $qty, $notes, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: supplier_invoice_items_list.php");
        exit;
    }
}

// Load item
$stmt = $conn->prepare("
    SELECT sii.*, si.invoice_number, s.supplier_name
    FROM supplier_invoice_items sii
    LEFT JOIN supplier_invoices si ON si.id = sii.invoice_id
    LEFT JOIN suppliers s ON s.id = si.supplier_id
    WHERE sii.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$item = $res->fetch_assoc();
$stmt->close();

if (!$item) {
    die("Item not found.");
}
?>

<div style="width:80%;max-width:800px;margin:30px auto;background:#111;border:2px solid #b00000;border-radius:8px;padding:20px;">
    <h2 style="color:#ff3333;margin-top:0;">Edit Invoice Item #<?php echo (int)$item['id']; ?></h2>
    <p style="font-size:13px;color:#ccc;">
        Supplier: <?php echo h($item['supplier_name']); ?> —
        Invoice: <?php echo h($item['invoice_number']); ?>
    </p>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">

        <label>Part Type</label>
        <select name="part_type" style="width:100%;padding:6px;background:#000;color:#fff;border:1px solid #444;border-radius:4px;margin-bottom:10px;">
            <option value="OEM"        <?php echo ($item['part_type']=='OEM'?'selected':''); ?>>OEM</option>
            <option value="SecondHand" <?php echo ($item['part_type']=='SecondHand'?'selected':''); ?>>SecondHand</option>
            <option value="Stripped"   <?php echo ($item['part_type']=='Stripped'?'selected':''); ?>>Stripped</option>
        </select>

        <label>Part Name</label>
        <input type="text" name="part_name" value="<?php echo h($item['part_name']); ?>" style="width:100%;padding:6px;background:#000;color:#fff;border:1px solid #444;border-radius:4px;margin-bottom:10px;">

        <label>Cost Price</label>
        <input type="number" step="0.01" name="cost_price" value="<?php echo h($item['cost_price']); ?>" style="width:100%;padding:6px;background:#000;color:#fff;border:1px solid #444;border-radius:4px;margin-bottom:10px;">

        <label>Quantity</label>
        <input type="number" name="qty" value="<?php echo (int)$item['qty']; ?>" style="width:100%;padding:6px;background:#000;color:#fff;border:1px solid #444;border-radius:4px;margin-bottom:10px;">

        <label>Notes (internal)</label>
        <textarea name="notes" style="width:100%;padding:6px;background:#000;color:#fff;border:1px solid #444;border-radius:4px;margin-bottom:10px;"><?php echo h($item['notes']); ?></textarea>

        <button type="submit" class="btn">Save Changes</button>
        <a href="supplier_invoice_items_list.php" class="btn secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
