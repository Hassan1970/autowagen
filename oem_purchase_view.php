<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($invoice_id <= 0) {
    die("Invalid invoice ID.");
}

// Load invoice + supplier
$sql = "
    SELECT i.*, s.supplier_name
    FROM supplier_oem_invoices i
    LEFT JOIN suppliers s ON i.supplier_id = s.id
    WHERE i.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$res = $stmt->get_result();
$invoice = $res->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("Invoice not found.");
}

// Load OEM parts linked to this invoice
$sqlParts = "
    SELECT 
        p.id,
        p.oem_number,
        p.part_name,
        p.stock_qty,
        p.cost_price,
        p.selling_price,
        c.name  AS category_name,
        sc.name AS subcategory_name,
        t.name  AS type_name,
        comp.name AS component_name
    FROM oem_parts p
    LEFT JOIN categories c     ON p.category_id = c.id
    LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
    LEFT JOIN types t          ON p.type_id = t.id
    LEFT JOIN components comp  ON p.component_id = comp.id
    WHERE p.supplier_oem_invoice_id = ?
    ORDER BY p.id DESC
";

$stmt = $conn->prepare($sqlParts);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$partsRes = $stmt->get_result();

$parts = [];
while ($row = $partsRes->fetch_assoc()) {
    $parts[] = $row;
}
$stmt->close();
?>
<style>
.page-wrap {
    width: 90%;
    margin: 25px auto;
    background: #111;
    border: 2px solid #b00000;
    color: #fff;
    padding: 20px 25px 30px;
    border-radius: 8px;
}
.page-title {
    text-align: center;
    color: #ff3333;
    font-size: 22px;
    margin-bottom: 20px;
}
.invoice-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(260px,1fr));
    gap: 10px 30px;
    margin-bottom: 20px;
}
.invoice-meta div {
    padding: 6px 0;
}
.invoice-meta strong {
    color: #ff3333;
}
.btn-bar {
    margin-bottom: 20px;
}
.btn {
    display: inline-block;
    background: #b00000;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 6px 14px;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
    margin-right: 8px;
}
.btn:hover {
    background: #ff3333;
}
.parts-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 13px;
}
.parts-table th,
.parts-table td {
    border: 1px solid #333;
    padding: 6px 8px;
}
.parts-table th {
    background: #b00000;
    color: #fff;
    text-align: left;
}
.parts-table tr:nth-child(even) {
    background: #181818;
}
.parts-table tr:nth-child(odd) {
    background: #101010;
}
.sub-label {
    font-size: 11px;
    color: #ccc;
}
</style>

<div class="page-wrap">
    <h2 class="page-title">OEM Supplier Invoice Details</h2>

    <div class="btn-bar">
        <a href="oem_purchase_list.php" class="btn">← Back to Invoice List</a>
        <a href="oem_purchase_edit.php?id=<?php echo (int)$invoice['id']; ?>" class="btn">Edit Invoice Header</a>
        <a href="oem_parts_add.php?invoice_id=<?php echo (int)$invoice['id']; ?>" class="btn">+ Add OEM Part for This Invoice</a>
    </div>

    <div class="invoice-meta">
        <div><strong>Supplier:</strong><br><?php echo h($invoice['supplier_name'] ?? ''); ?></div>
        <div><strong>Invoice Number:</strong><br><?php echo h($invoice['invoice_number']); ?></div>
        <div><strong>Invoice Date:</strong><br><?php echo h($invoice['invoice_date']); ?></div>
        <div><strong>Account Type:</strong><br><?php echo h($invoice['account_type']); ?></div>
        <div><strong>Total Price Paid:</strong><br><?php echo number_format((float)$invoice['total_amount'], 2); ?></div>
        <div><strong>Reference:</strong><br><?php echo h($invoice['reference']); ?></div>
    </div>

    <div style="margin-bottom:15px;">
        <strong style="color:#ff3333;">Notes:</strong><br>
        <div style="white-space:pre-wrap;"><?php echo nl2br(h($invoice['notes'])); ?></div>
    </div>

    <h3 style="color:#ff3333; margin-top:25px; margin-bottom:10px;">OEM Parts Linked to This Invoice</h3>

    <?php if (empty($parts)): ?>
        <p>No OEM parts are linked to this invoice yet.</p>
        <a href="oem_parts_add.php?invoice_id=<?php echo (int)$invoice['id']; ?>" class="btn">
            + Add First OEM Part
        </a>
    <?php else: ?>
        <table class="parts-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>OEM Number</th>
                    <th>Part Name</th>
                    <th>EPC Path</th>
                    <th>Qty</th>
                    <th>Cost</th>
                    <th>Selling</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($parts as $p): ?>
                <tr>
                    <td><?php echo (int)$p['id']; ?></td>
                    <td><?php echo h($p['oem_number']); ?></td>
                    <td><?php echo h($p['part_name']); ?></td>
                    <td>
                        <span class="sub-label">
                            <?php
                                $bits = [];
                                if (!empty($p['category_name']))    $bits[] = $p['category_name'];
                                if (!empty($p['subcategory_name'])) $bits[] = $p['subcategory_name'];
                                if (!empty($p['type_name']))        $bits[] = $p['type_name'];
                                if (!empty($p['component_name']))   $bits[] = $p['component_name'];
                                echo h(implode(" → ", $bits));
                            ?>
                        </span>
                    </td>
                    <td><?php echo (int)$p['stock_qty']; ?></td>
                    <td><?php echo number_format((float)$p['cost_price'], 2); ?></td>
                    <td><?php echo number_format((float)$p['selling_price'], 2); ?></td>
                    <td>
                        <a class="btn" href="oem_parts_edit.php?id=<?php echo (int)$p['id']; ?>">
                            Edit Part
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
