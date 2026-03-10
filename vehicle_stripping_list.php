<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
if ($vehicle_id <= 0) {
    die("<h3 style='color:red;'>✖ No vehicle selected.</h3>");
}

// GET VEHICLE DETAILS
$stmt = $conn->prepare("SELECT stock_code, year, make, model FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$veh = $stmt->get_result()->fetch_assoc();

// GET ALL STRIPPED PARTS FOR THIS VEHICLE
$sql = "
    SELECT sp.id,
           sp.qty,
           c.name AS category_name,
           sc.name AS subcategory_name,
           t.name AS type_name,
           comp.name AS component_name
    FROM vehicle_stripped_parts sp
    JOIN categories c ON sp.category_id = c.id
    JOIN subcategories sc ON sp.subcategory_id = sc.id
    JOIN types t ON sp.type_id = t.id
    JOIN components comp ON sp.component_id = comp.id
    WHERE sp.vehicle_id = ?
    ORDER BY c.name, sc.name, t.name, comp.name
";

$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $vehicle_id);
$stmt2->execute();
$result = $stmt2->get_result();
?>

<style>
.wrapper {
    width: 90%;
    margin: auto;
    padding: 20px;
}

h2 {
    color: red;
    margin-bottom: 20px;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
}

table th, table td {
    padding: 10px;
    border: 1px solid #444;
}

table th {
    background: red;
    color: white;
}

table tr:nth-child(even) {
    background: #222;
}

table tr:nth-child(odd) {
    background: #111;
}

.delete-btn {
    background: #d40000;
    padding: 6px 12px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
}

.delete-btn:hover {
    background: #ff1a1a;
}

.back-btn {
    background: #444;
    padding: 10px 15px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    margin-right: 10px;
}

.back-btn:hover {
    background: #666;
}

.add-btn {
    background: red;
    padding: 10px 15px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}

.add-btn:hover {
    background: #ff1a1a;
}
</style>

<div class="wrapper">
    <h2>Stripped Parts — <?= htmlspecialchars($veh['stock_code']) ?>  
    (<?= htmlspecialchars($veh['year'].' '.$veh['make'].' '.$veh['model']) ?>)</h2>

    <a class="back-btn" href="vehicles.php">← Back to Vehicles</a>
    <a class="add-btn" href="vehicle_stripping_entry.php?vehicle_id=<?= $vehicle_id ?>">+ Add More Parts</a>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Type</th>
                <th>Component</th>
                <th>Qty</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows == 0): ?>
            <tr>
                <td colspan="6" style="text-align:center; color:red;">No stripped parts yet.</td>
            </tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= htmlspecialchars($row['subcategory_name']) ?></td>
                    <td><?= htmlspecialchars($row['type_name']) ?></td>
                    <td><?= htmlspecialchars($row['component_name']) ?></td>
                    <td><?= htmlspecialchars($row['qty']) ?></td>
                    <td>
                        <a class="delete-btn"
                           href="vehicle_stripping_delete.php?id=<?= $row['id'] ?>&vehicle_id=<?= $vehicle_id ?>"
                           onclick="return confirm('Delete this stripped part?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
