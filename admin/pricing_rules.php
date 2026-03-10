<?php
/*********************************************************
 * ADMIN – PRICING RULES MANAGEMENT (HARDENED)
 * Phase 3.4.2 – FINAL
 *********************************************************/

require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = '';

/* ---------------- ADD NEW RULE ---------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_rule'])) {

    $category_id = (int)($_POST['category_id'] ?? 0);
    $condition   = trim($_POST['part_condition'] ?? '');
    $price       = (float)($_POST['suggested_price'] ?? 0);

    if ($category_id > 0 && $condition !== '' && $price > 0) {

        // Force single active rule per category + condition
        $deactivate = $conn->prepare("
            UPDATE pricing_rules
            SET is_active = 0
            WHERE category_id = ?
              AND part_condition = ?
        ");
        $deactivate->bind_param("is", $category_id, $condition);
        $deactivate->execute();
        $deactivate->close();

        // Insert new active rule
        $insert = $conn->prepare("
            INSERT INTO pricing_rules
            (category_id, part_condition, suggested_price, is_active, created_at)
            VALUES (?, ?, ?, 1, NOW())
        ");
        $insert->bind_param("isd", $category_id, $condition, $price);

        if ($insert->execute()) {
            $message = "New pricing rule added and activated.";
        } else {
            $message = "Error adding pricing rule.";
        }

        $insert->close();

    } else {
        $message = "All fields are required and price must be greater than zero.";
    }
}

/* ---------------- TOGGLE ACTIVE (SAFE) ---------------- */

if (isset($_GET['toggle_id'])) {

    $rule_id = (int)$_GET['toggle_id'];

    // Get rule details
    $stmt = $conn->prepare("
        SELECT category_id, part_condition, is_active
        FROM pricing_rules
        WHERE id = ?
    ");
    $stmt->bind_param("i", $rule_id);
    $stmt->execute();
    $rule = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($rule) {

        if ($rule['is_active'] == 0) {
            // Activating → deactivate others first
            $deactivate = $conn->prepare("
                UPDATE pricing_rules
                SET is_active = 0
                WHERE category_id = ?
                  AND part_condition = ?
            ");
            $deactivate->bind_param("is", $rule['category_id'], $rule['part_condition']);
            $deactivate->execute();
            $deactivate->close();

            $conn->query("
                UPDATE pricing_rules
                SET is_active = 1
                WHERE id = $rule_id
            ");

        } else {
            // Deactivating is allowed
            $conn->query("
                UPDATE pricing_rules
                SET is_active = 0
                WHERE id = $rule_id
            ");
        }
    }

    header("Location: pricing_rules.php");
    exit;
}

/* ---------------- LOAD DATA ---------------- */

// Categories
$categories = [];
$res = $conn->query("SELECT id, name FROM categories ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}

// Pricing rules
$rules = [];
$res = $conn->query("
    SELECT pr.*, c.name AS category_name
    FROM pricing_rules pr
    JOIN categories c ON c.id = pr.category_id
    ORDER BY pr.created_at DESC
");
while ($row = $res->fetch_assoc()) {
    $rules[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin – Pricing Rules</title>

<style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; }
    th { background: #f4f4f4; }
    .active { color: green; font-weight: bold; }
    .inactive { color: red; }
    .msg { margin: 10px 0; font-weight: bold; }
</style>
</head>

<body>

<h2>Pricing Rules Management</h2>

<?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<h3>Add New Pricing Rule</h3>

<form method="post">
    <select name="category_id" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="part_condition" required>
        <option value="">Select Condition</option>
        <option value="New">New</option>
        <option value="Good">Good</option>
        <option value="Fair">Fair</option>
    </select>

    <input type="number" step="0.01" name="suggested_price" placeholder="Price" required>

    <button type="submit" name="add_rule">Add Rule</button>
</form>

<h3>Existing Pricing Rules</h3>

<table>
<tr>
    <th>ID</th>
    <th>Category</th>
    <th>Condition</th>
    <th>Price</th>
    <th>Status</th>
    <th>Created</th>
    <th>Action</th>
</tr>

<?php foreach ($rules as $r): ?>
<tr>
    <td><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['category_name']) ?></td>
    <td><?= htmlspecialchars($r['part_condition']) ?></td>
    <td><?= number_format($r['suggested_price'], 2) ?></td>
    <td class="<?= $r['is_active'] ? 'active' : 'inactive' ?>">
        <?= $r['is_active'] ? 'Active' : 'Inactive' ?>
    </td>
    <td><?= $r['created_at'] ?></td>
    <td>
        <a href="?toggle_id=<?= $r['id'] ?>">
            <?= $r['is_active'] ? 'Deactivate' : 'Activate' ?>
        </a>
    </td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
