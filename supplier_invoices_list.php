<?php
require_once __DIR__ . '/config/config.php';
$page_title = "Supplier Invoices";

// ----------------------------------
// Handle "Mark as Paid" action (POST)
// ----------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid_id'])) {
    $id = (int)$_POST['mark_paid_id'];
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE supplier_invoices SET payment_status = 'Paid' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: supplier_invoices_list.php");
    exit;
}

include __DIR__ . '/includes/header.php';

// ----------------------------------
// Build filters from GET
// ----------------------------------
$filter_supplier   = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
$filter_status     = $_GET['status'] ?? ''; // '', 'Paid', 'Unpaid'
$filter_number     = trim($_GET['invoice_number'] ?? '');
$filter_date_from  = trim($_GET['date_from'] ?? '');
$filter_date_to    = trim($_GET['date_to'] ?? '');

$where  = [];
$params = [];
$types  = "";

// Supplier filter
if ($filter_supplier > 0) {
    $where[] = "si.supplier_id = ?";
    $types  .= "i";
    $params[] = $filter_supplier;
}

// Status filter
if ($filter_status === 'Paid' || $filter_status === 'Unpaid') {
    $where[] = "si.payment_status = ?";
    $types  .= "s";
    $params[] = $filter_status;
}

// Invoice number filter (LIKE)
if ($filter_number !== '') {
    $where[] = "si.invoice_number LIKE ?";
    $types  .= "s";
    $params[] = "%" . $filter_number . "%";
}

// Date range filters
if ($filter_date_from !== '') {
    $where[] = "si.invoice_date >= ?";
    $types  .= "s";
    $params[] = $filter_date_from;
}
if ($filter_date_to !== '') {
    $where[] = "si.invoice_date <= ?";
    $types  .= "s";
    $params[] = $filter_date_to;
}

$whereSql = "";
if ($where) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

// ----------------------------------
// Load suppliers for filter dropdown
// ----------------------------------
$suppliersRes = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");

// ----------------------------------
// Build main query (totals + attachments)
// ----------------------------------
$sql = "
    SELECT
        si.id,
        si.invoice_number,
        si.invoice_date,
        si.total_amount,      -- optional header total; can be null
        si.payment_status,
        si.created_at,
        s.supplier_name,
        COALESCE(SUM(sii.cost_price * sii.qty), 0) AS calc_total,
        COUNT(DISTINCT f.id) AS file_count
    FROM supplier_invoices si
    LEFT JOIN suppliers s ON s.id = si.supplier_id
    LEFT JOIN supplier_invoice_items sii ON sii.invoice_id = si.id
    LEFT JOIN supplier_invoice_files f ON f.invoice_id = si.id
    $whereSql
    GROUP BY si.id
    ORDER BY si.invoice_date DESC, si.id DESC
";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$listRes = $stmt->get_result();
?>

<div class="page-header">
    <div>
        <h1>Supplier Invoices</h1>
        <p>All supplier invoices with totals, status and attachments.</p>
    </div>
    <div>
        <a href="invoice_add.php" class="btn">+ Add Supplier Invoice &amp; Parts</a>
    </div>
</div>

<!-- Filters -->
<div style="background:#111;border:1px solid #b00000;border-radius:6px;padding:10px;margin-bottom:15px;">
    <form method="get" class="filter-form">
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
            <div style="flex:0 0 180px;">
                <label style="font-size:12px;">Supplier</label>
                <select name="supplier_id" style="width:100%;">
                    <option value="0">-- All Suppliers --</option>
                    <?php if ($suppliersRes && $suppliersRes->num_rows): ?>
                        <?php while ($s = $suppliersRes->fetch_assoc()): ?>
                            <option value="<?php echo (int)$s['id']; ?>"
                                <?php echo ($filter_supplier == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo h($s['supplier_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div style="flex:0 0 150px;">
                <label style="font-size:12px;">Invoice #</label>
                <input type="text" name="invoice_number" value="<?php echo h($filter_number); ?>">
            </div>

            <div style="flex:0 0 140px;">
                <label style="font-size:12px;">Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="Paid"   <?php echo ($filter_status === 'Paid')   ? 'selected' : ''; ?>>Paid</option>
                    <option value="Unpaid" <?php echo ($filter_status === 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                </select>
            </div>

            <div style="flex:0 0 140px;">
                <label style="font-size:12px;">From date</label>
                <input type="date" name="date_from" value="<?php echo h($filter_date_from); ?>">
            </div>

            <div style="flex:0 0 140px;">
                <label style="font-size:12px;">To date</label>
                <input type="date" name="date_to" value="<?php echo h($filter_date_to); ?>">
            </div>

            <div style="flex:0 0 120px;">
                <button type="submit" class="btn" style="width:100%;">Search</button>
            </div>

            <div style="flex:0 0 80px;">
                <a href="supplier_invoices_list.php" class="btn secondary" style="width:100%;text-align:center;">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Invoices table -->
<div style="background:#111;border:1px solid #b00000;border-radius:6px;padding:10px;">
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice #</th>
                    <th>Supplier</th>
                    <th>Invoice Date</th>
                    <th>Status</th>
                    <th>Attachments</th>
                    <th style="text-align:right;">Total (calculated)</th>
                    <th style="width:210px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($listRes && $listRes->num_rows): ?>
                <?php $rowNum = 1; $grandTotal = 0; ?>
                <?php while ($row = $listRes->fetch_assoc()):
                    $total = (float)$row['calc_total'];
                    $grandTotal += $total;
                ?>
                    <tr>
                        <td><?php echo $rowNum++; ?></td>
                        <td><?php echo h($row['invoice_number']); ?></td>
                        <td><?php echo h($row['supplier_name']); ?></td>
                        <td><?php echo h($row['invoice_date']); ?></td>
                        <td>
                            <?php if ($row['payment_status'] === 'Paid'): ?>
                                <span style="background:#064;color:#fff;padding:2px 6px;border-radius:3px;font-size:11px;">Paid</span>
                            <?php else: ?>
                                <span style="background:#800;color:#fff;padding:2px 6px;border-radius:3px;font-size:11px;">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['file_count'] > 0): ?>
                                📎 <?php echo (int)$row['file_count']; ?>
                            <?php else: ?>
                                –
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <?php echo number_format($total, 2); ?>
                        </td>
                        <td>
                            <a href="invoice_add.php?id=<?php echo (int)$row['id']; ?>" class="btn secondary" style="font-size:12px;">Edit</a>
                            <a href="invoice_view.php?id=<?php echo (int)$row['id']; ?>" class="btn secondary" style="font-size:12px;">View</a>

                            <?php if ($row['payment_status'] !== 'Paid'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="mark_paid_id" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" class="btn" style="font-size:12px;background:#0a0;">Mark Paid</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="6" style="text-align:right;font-weight:bold;">Grand Total:</td>
                    <td style="text-align:right;font-weight:bold;"><?php echo number_format($grandTotal, 2); ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="8">No invoices found. Click “Add Supplier Invoice &amp; Parts” to create one.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt->close();
include __DIR__ . '/includes/footer.php';
?>
