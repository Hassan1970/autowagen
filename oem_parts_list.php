<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

// ---------- LOAD FILTER DROPDOWNS ----------

// Categories
$catSql = "SELECT id, name FROM categories ORDER BY name ASC";
$catRes = $conn->query($catSql);

// Supplier OEM invoices (for filter)
$invSql = "
    SELECT i.id, i.invoice_number, i.invoice_date, s.supplier_name
    FROM supplier_oem_invoices i
    LEFT JOIN suppliers s ON s.id = i.supplier_id
    ORDER BY i.id DESC
";
$invRes = $conn->query($invSql);

// ---------- READ FILTERS ----------
$q              = trim($_GET['q'] ?? '');
$category_id    = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
$invoice_id     = isset($_GET['invoice_id'])  && $_GET['invoice_id']  !== '' ? (int)$_GET['invoice_id']  : null;
$limit          = 100; // show up to 100 rows on screen
$page           = max(1, (int)($_GET['page'] ?? 1));
$offset         = ($page - 1) * $limit;

// ---------- BUILD QUERY ----------
$where  = "1=1";
$params = [];
$types  = "";

// Quick search on OEM number or part name
if ($q !== '') {
    $where .= " AND (p.oem_number LIKE ? OR p.part_name LIKE ?)";
    $types .= "ss";
    $like = "%{$q}%";
    $params[] = $like;
    $params[] = $like;
}

// Filter by category
if ($category_id !== null) {
    $where .= " AND p.category_id = ?";
    $types .= "i";
    $params[] = $category_id;
}

// Filter by supplier OEM invoice link
if ($invoice_id !== null) {
    $where .= " AND p.supplier_oem_invoice_id = ?";
    $types .= "i";
    $params[] = $invoice_id;
}

// ---------- COUNT TOTAL ----------
$countSql = "
    SELECT COUNT(*) AS cnt
    FROM oem_parts p
    WHERE $where
";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countRes = $countStmt->get_result();
$totalRows = (int)($countRes->fetch_assoc()['cnt'] ?? 0);
$countStmt->close();

$totalPages = max(1, (int)ceil($totalRows / $limit));

// ---------- MAIN LIST QUERY ----------
$listSql = "
    SELECT 
        p.id,
        p.oem_number,
        p.part_name,
        p.stock_qty,
        p.cost_price,
        p.selling_price,
        c.name      AS category_name,
        sc.name     AS subcategory_name,
        t.name      AS type_name,
        comp.name   AS component_name,
        i.id        AS invoice_id,
        i.invoice_number,
        i.invoice_date,
        s.supplier_name
    FROM oem_parts p
    LEFT JOIN categories     c    ON p.category_id = c.id
    LEFT JOIN subcategories  sc   ON p.subcategory_id = sc.id
    LEFT JOIN types          t    ON p.type_id = t.id
    LEFT JOIN components     comp ON p.component_id = comp.id
    LEFT JOIN supplier_oem_invoices i ON p.supplier_oem_invoice_id = i.id
    LEFT JOIN suppliers      s    ON i.supplier_id = s.id
    WHERE $where
    ORDER BY p.id DESC
    LIMIT ? OFFSET ?
";

// add limit & offset types
$typesList  = $types . "ii";
$paramsList = $params;
$paramsList[] = $limit;
$paramsList[] = $offset;

$listStmt = $conn->prepare($listSql);
$listStmt->bind_param($typesList, ...$paramsList);
$listStmt->execute();
$listRes = $listStmt->get_result();

$parts = [];
while ($row = $listRes->fetch_assoc()) {
    $parts[] = $row;
}
$listStmt->close();
?>

<style>
.page-wrap {
    width: 95%;
    margin: 25px auto;
    background: #111;
    border: 2px solid #b00000;
    border-radius: 8px;
    padding: 20px 25px 30px;
    color: #fff;
}
.page-title {
    text-align: center;
    color: #ff3333;
    margin-bottom: 15px;
}
.filter-bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}
.filter-bar input,
.filter-bar select {
    background: #222;
    border: 1px solid #444;
    color: #fff;
    border-radius: 4px;
    padding: 6px 8px;
    font-size: 13px;
}
.btn {
    display: inline-block;
    background: #b00000;
    color: #fff;
    border-radius: 4px;
    border: none;
    padding: 6px 14px;
    font-size: 13px;
    text-decoration: none;
    cursor: pointer;
}
.btn:hover { background: #ff3333; }
.btn-sm {
    padding: 4px 10px;
    font-size: 12px;
}
.btn-grey {
    background: #444;
    color: #eee;
}
.btn-grey:hover {
    background: #666;
}
.table-wrap {
    margin-top: 10px;
    overflow-x: auto;
}
.parts-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.parts-table th,
.parts-table td {
    border: 1px solid #333;
    padding: 6px 8px;
    white-space: nowrap;
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
.sub-path {
    font-size: 11px;
    color: #ccc;
}
.pagination {
    margin-top: 12px;
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}
.pagination a,
.pagination span {
    padding: 4px 8px;
    font-size: 12px;
    border-radius: 4px;
    border: 1px solid #444;
    text-decoration: none;
    color: #eee;
}
.pagination .active {
    background: #b00000;
    border-color: #b00000;
}
.pagination a:hover {
    background: #333;
}
.count-info {
    margin-top: 8px;
    font-size: 12px;
    color: #ccc;
}
</style>

<div class="page-wrap">
    <h2 class="page-title">OEM Parts List</h2>

    <div style="margin-bottom:10px;">
        <a href="oem_parts_add.php" class="btn">+ Add New OEM Part</a>
        <a href="oem_purchase_list.php" class="btn-grey btn-sm">← OEM Invoices</a>
    </div>

    <!-- FILTER FORM -->
    <form method="get" class="filter-bar">
        <input type="text"
               name="q"
               placeholder="Search OEM # or Part Name"
               value="<?php echo h($q); ?>">

        <select name="category_id">
            <option value="">All Categories</option>
            <?php
            // we need fresh cursor for categories
            $catRes->data_seek(0);
            while ($c = $catRes->fetch_assoc()):
            ?>
                <option value="<?php echo (int)$c['id']; ?>"
                    <?php echo ($category_id === (int)$c['id']) ? 'selected' : ''; ?>>
                    <?php echo h($c['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="invoice_id">
            <option value="">All OEM Invoices</option>
            <?php
            $invRes->data_seek(0);
            while ($inv = $invRes->fetch_assoc()):
                $label = sprintf(
                    "#%d - %s (%s) - %s",
                    $inv['id'],
                    $inv['invoice_number'],
                    $inv['invoice_date'],
                    $inv['supplier_name']
                );
            ?>
                <option value="<?php echo (int)$inv['id']; ?>"
                    <?php echo ($invoice_id === (int)$inv['id']) ? 'selected' : ''; ?>>
                    <?php echo h($label); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn-sm btn">Filter</button>
        <a href="oem_parts_list.php" class="btn-sm btn-grey">Reset</a>
    </form>

    <div class="count-info">
        Showing <?php echo count($parts); ?> of <?php echo $totalRows; ?> part(s)
        <?php if ($totalPages > 1): ?>
            — page <?php echo $page; ?> of <?php echo $totalPages; ?>
        <?php endif; ?>
    </div>

    <div class="table-wrap">
        <table class="parts-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>OEM #</th>
                    <th>Part Name</th>
                    <th>EPC Path</th>
                    <th>Stock</th>
                    <th>Cost</th>
                    <th>Selling</th>
                    <th>Invoice</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($parts)): ?>
                <tr>
                    <td colspan="10">No OEM parts found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($parts as $p): ?>
                    <tr>
                        <td><?php echo (int)$p['id']; ?></td>
                        <td><?php echo h($p['oem_number']); ?></td>
                        <td><?php echo h($p['part_name']); ?></td>
                        <td>
                            <span class="sub-path">
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
                            <?php if ($p['invoice_id']): ?>
                                <a href="oem_purchase_view.php?id=<?php echo (int)$p['invoice_id']; ?>" class="btn-sm btn-grey">
                                    <?php echo h($p['invoice_number']); ?>
                                </a>
                            <?php else: ?>
                                <span class="sub-path">No invoice link</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo h($p['supplier_name']); ?></td>
                        <td>
                            <a href="oem_parts_edit.php?id=<?php echo (int)$p['id']; ?>" class="btn-sm btn">Edit</a>
                            <!-- optional: delete later if you have delete logic -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            // keep filters in pagination links
            $baseParams = $_GET;
            foreach (range(1, $totalPages) as $pageno):
                $baseParams['page'] = $pageno;
                $url = 'oem_parts_list.php?' . http_build_query($baseParams);
            ?>
                <?php if ($pageno == $page): ?>
                    <span class="active"><?php echo $pageno; ?></span>
                <?php else: ?>
                    <a href="<?php echo h($url); ?>"><?php echo $pageno; ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
