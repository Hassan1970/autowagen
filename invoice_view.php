<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("<h2>Invalid Invoice ID</h2>");
}

// ===============================
// LOAD INVOICE HEADER (CORRECT TABLE)
// ===============================
$sql = "
SELECT si.*, s.supplier_name, s.phone, s.email, s.address
FROM supplier_invoices si
LEFT JOIN suppliers s ON s.id = si.supplier_id
WHERE si.id = $id
";

$invoice = $conn->query($sql)->fetch_assoc();

if (!$invoice) {
    die("<h2>Invoice not found.</h2>");
}
?>

<h1>Supplier Invoice — <?php echo h($invoice['invoice_number']); ?></h1>

<div style="background:#111;border:1px solid #b00000;padding:20px;border-radius:8px;margin-bottom:25px;">
    <h3 style="color:#ff3333;margin-top:0;">Invoice Information</h3>
    <table>
        <tr><td style="width:180px;">Supplier</td><td><?php echo h($invoice['supplier_name']); ?></td></tr>
        <tr><td>Invoice Number</td><td><?php echo h($invoice['invoice_number']); ?></td></tr>
        <tr><td>Date</td><td><?php echo h($invoice['invoice_date']); ?></td></tr>
        <tr><td>Total Amount</td><td>R <?php echo number_format($invoice['total_amount'], 2); ?></td></tr>
        <tr><td>Notes</td><td><?php echo nl2br(h($invoice['notes'])); ?></td></tr>
    </table>
</div>

<?php
// ===============================
// LOAD INVOICE ITEMS (CORRECT TABLE)
// ===============================
$items = $conn->query("
    SELECT * FROM supplier_invoice_items
    WHERE invoice_id = $id
    ORDER BY id ASC
");
?>

<h2>Invoice Items</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Type</th>
        <th>Name</th>
        <th>Vehicle</th>
        <th>Cost</th>
        <th>Qty</th>
        <th>Photos</th>
    </tr>

<?php if ($items->num_rows == 0): ?>

    <tr>
        <td colspan="7">No items added yet.</td>
    </tr>

<?php else: ?>

    <?php while ($i = $items->fetch_assoc()): ?>

        <?php
        // Load photos for this item
        $photos = $conn->query("
            SELECT file_name 
            FROM supplier_invoice_item_photos
            WHERE item_id = {$i['id']}
        ");
        ?>

        <tr>
            <td><?php echo $i['id']; ?></td>
            <td><?php echo h($i['part_type']); ?></td>
            <td><?php echo h($i['part_name']); ?></td>

            <td>
                <?php
                if (!empty($i['related_vehicle_id'])) {
                    $v = $conn->query("SELECT stock_code FROM vehicles WHERE id = {$i['related_vehicle_id']}")->fetch_assoc();
                    echo h($v['stock_code']);
                } else {
                    echo "-";
                }
                ?>
            </td>

            <td>
                R <?php echo number_format($i['cost_price'], 2); ?><br>
                <small style="color:#bbb;">Code: <?php echo h($i['encoded_cost']); ?></small>
            </td>

            <td><?php echo h($i['qty']); ?></td>

            <td>
                <?php if ($photos->num_rows == 0): ?>
                    <span style="color:#444;">–</span>
                <?php else: ?>
                    <?php while ($p = $photos->fetch_assoc()): ?>
                        <a href="uploads/invoice_items/<?php echo h($p['file_name']); ?>" target="_blank">
                            <img src="uploads/invoice_items/<?php echo h($p['file_name']); ?>"
                                 style="width:50px;height:50px;object-fit:cover;border:1px solid #333;margin-right:4px;">
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
            </td>
        </tr>

    <?php endwhile; ?>

<?php endif; ?>

</table>

<div style="margin-top:25px;">
    <a href="invoice_add.php?id=<?php echo $id; ?>" class="btn">Add More Items</a>
    <a href="supplier_invoices_list.php" class="btn secondary">Back to Invoices</a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
