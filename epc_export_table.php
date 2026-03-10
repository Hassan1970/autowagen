<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/*
 OPTIONAL FILTER:
 epc_export_table.php?subcategory_id=305
*/
$subcategoryFilter = isset($_GET['subcategory_id']) ? (int)$_GET['subcategory_id'] : null;

// --------------------------------------------
// BUILD QUERY
// --------------------------------------------
$sql = "
SELECT
    sc.id   AS subcategory_id,
    sc.name AS subcategory_name,
    t.id    AS type_id,
    t.name  AS type_name,
    c.id    AS component_id,
    c.name  AS component_name
FROM subcategories sc
JOIN types t
    ON t.subcategory_id = sc.id
LEFT JOIN components c
    ON c.type_id = t.id
";

if ($subcategoryFilter) {
    $sql .= " WHERE sc.id = {$subcategoryFilter}";
}

$sql .= "
ORDER BY
    sc.id,
    t.id,
    c.id
";

$result = $conn->query($sql);
?>

<style>
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #111;
    color: white;
}
th, td {
    border: 1px solid #444;
    padding: 8px;
    text-align: left;
}
th {
    background: #222;
    color: #ff4444;
}
tr:nth-child(even) {
    background: #1a1a1a;
}
</style>

<h2 style="color:red;">EPC Export — Subcategory → Type → Component</h2>

<table>
    <thead>
        <tr>
            <th>Subcategory ID</th>
            <th>Subcategory Name</th>
            <th>Type ID</th>
            <th>Type Name</th>
            <th>Component ID</th>
            <th>Component Name</th>
        </tr>
    </thead>
    <tbody>

<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['subcategory_id']; ?></td>
            <td><?= htmlspecialchars($row['subcategory_name']); ?></td>
            <td><?= $row['type_id']; ?></td>
            <td><?= htmlspecialchars($row['type_name']); ?></td>
            <td><?= $row['component_id']; ?></td>
            <td><?= htmlspecialchars($row['component_name']); ?></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
        <tr>
            <td colspan="6">No EPC data found.</td>
        </tr>
<?php endif; ?>

    </tbody>
</table>

<?php include __DIR__ . "/includes/footer.php"; ?>
