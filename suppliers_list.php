<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";   // ⭐ LOADS YOUR TOP NAV HEADER ⭐

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* Safe helper */
if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/* ---------------------------
   DELETE SUPPLIER
-----------------------------*/
$deleteMsg = '';
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId > 0) {
        $stmt = $conn->prepare("DELETE FROM third_party_suppliers WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $delId);

        if ($stmt->execute()) {
            $deleteMsg = "Supplier ID $delId deleted successfully.";
        } else {
            $deleteMsg = "Error deleting supplier: " . $stmt->error;
        }
        $stmt->close();
    }
}

/* ---------------------------
   SEARCH
-----------------------------*/
$search = trim($_GET['search'] ?? '');
$whereSql = '';
$params = [];
$types = '';

if ($search !== '') {
    $whereSql = "WHERE 
        supplier_name LIKE CONCAT('%', ?, '%')
        OR contact_person LIKE CONCAT('%', ?, '%')
        OR phone LIKE CONCAT('%', ?, '%')
        OR email LIKE CONCAT('%', ?, '%')";
    $params = [$search, $search, $search, $search];
    $types = "ssss";
}

/* ---------------------------
   LOAD SUPPLIERS
-----------------------------*/
$sql = "SELECT 
            id, 
            supplier_name, 
            contact_person, 
            phone, 
            email, 
            company_reg_number, 
            created_at
        FROM third_party_suppliers
        $whereSql
        ORDER BY supplier_name ASC";

if ($whereSql !== '') {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}

$suppliers = [];
while ($row = $res->fetch_assoc()) {
    $suppliers[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Suppliers List</title>

<style>
body {
    background: #000;
    color: #fff;
    font-family: Arial, sans-serif;
    margin: 0;
}

/* Main wrapper box */
.wrap {
    width: 95%;
    margin: 25px auto;
    background: #111;
    padding: 25px;
    border: 1px solid #b00000;
    border-radius: 8px;
}

/* Heading */
h1 {
    margin: 0 0 10px 0;
    color: #ff3333;
}

/* Buttons */
.btn {
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 12px;
}
.btn-add { background: #008000; color: #fff; }
.btn-edit { background: #0044aa; color: #fff; }
.btn-del { background: #b00000; color: #fff; }
.btn-view { background: #444; color: #fff; }

/* Search box */
.search-box input {
    padding: 7px;
    width: 250px;
    background: #222;
    border: 1px solid #444;
    color: #fff;
}

/* Table */
table {
    width: 100%;
    margin-top: 15px;
    border-collapse: collapse;
}
th, td {
    padding: 10px 6px;
    border-bottom: 1px solid #333;
}
th {
    background: #181818;
    text-transform: uppercase;
    font-size: 12px;
}
tr:hover {
    background: #1c1c1c;
}
</style>

<script>
function confirmDel(id) {
    if (confirm("Are you sure you want to delete supplier ID " + id + "?")) {
        window.location = "suppliers_list.php?delete=" + id;
    }
}
</script>
</head>

<body>

<div class="wrap">

    <h1>Suppliers</h1>

    <!-- SEARCH BAR -->
    <form method="get" class="search-box">
        <input type="text" name="search" placeholder="Search supplier name, email, phone..." 
               value="<?php echo h($search); ?>">
        <button class="btn btn-view">Search</button>
    </form>

    <br>

    <!-- ADD BUTTON -->
    <a href="supplier_add.php" class="btn btn-add">+ Add Supplier</a>

    <!-- DELETE MESSAGE -->
    <?php if ($deleteMsg): ?>
        <div style="margin-top: 10px; background:#250808; padding:10px; border:1px solid #900;">
            <?php echo h($deleteMsg); ?>
        </div>
    <?php endif; ?>

    <!-- RESULTS TABLE -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Supplier Name</th>
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Company Reg</th>
                <th>Created</th>
                <th style="width:180px;">Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php if (empty($suppliers)): ?>
            <tr><td colspan="8">No suppliers found.</td></tr>

        <?php else: ?>
            <?php foreach ($suppliers as $s): ?>
            <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo h($s['supplier_name']); ?></td>
                <td><?php echo h($s['contact_person']); ?></td>
                <td><?php echo h($s['phone']); ?></td>
                <td><?php echo h($s['email']); ?></td>
                <td><?php echo h($s['company_reg_number']); ?></td>
                <td><?php echo h($s['created_at']); ?></td>

                <td>
                    <a class="btn btn-view" href="supplier_view.php?id=<?php echo $s['id']; ?>">View</a>
                    <a class="btn btn-edit" href="supplier_edit.php?id=<?php echo $s['id']; ?>">Edit</a>
                    <a class="btn btn-del" href="#" onclick="confirmDel(<?php echo $s['id']; ?>)">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
