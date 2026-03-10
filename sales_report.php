<?php
// Include your database connection here
require_once 'config/config.php';

// The SQL query to get the sales by category
$query = "
    SELECT c.name AS category_name, SUM(si.selling_price) AS total_sales
    FROM vehicle_stripped_parts vsp
    JOIN stripped_inventory si ON vsp.id = si.stripped_part_id
    JOIN categories c ON vsp.category_id = c.id
    GROUP BY vsp.category_id
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        h1 { color: red; }
        table { width: 60%; margin: 20px auto; border-collapse: collapse; background-color: #fff; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color: #333; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
    </style>
</head>
<body>
    <h1>Sales Report by Category</h1>
    <table>
        <tr>
            <th>Category</th>
            <th>Total Sales</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td><?= number_format($row['total_sales'], 2) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

Now the report page will have your black, gray, and red theme and will look more in line with the rest of your site. Let me know how that works out!
