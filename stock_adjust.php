<?php
require_once __DIR__ . '/config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Handle POST (adjustment)
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $part_id      = isset($_POST['part_id']) ? (int)$_POST['part_id'] : 0;
    $direction    = $_POST['direction'] ?? 'IN'; // IN or OUT
    $qty          = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;
    $notes        = trim($_POST['notes'] ?? '');

    if ($part_id <= 0 || $qty <= 0) {
        $msg = "Invalid part or quantity.";
    } else {
        // Get current stock
        $stmt = $conn->prepare("SELECT stock_qty FROM replacement_parts WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $part_id);
        $stmt->execute();
        $stmt->bind_result($currentStock);
        if (!$stmt->fetch()) {
            $currentStock = 0;
        }
        $stmt->close();

        if ($direction === 'OUT' && $qty > $currentStock) {
            $msg = "Cannot remove more than current stock ({$currentStock}).";
        } else {
            // Calculate new stock
            $newStock   = $direction === 'IN'
                ? ($currentStock + $qty)
                : ($currentStock - $qty);

            // Update replacement_parts stock
            $stmt = $conn->prepare("
                UPDATE replacement_parts
                SET stock_qty = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $newStock, $part_id);
            if ($stmt->execute()) {
                $stmt->close();

                // Log movement
                $movement_type = ($direction === 'IN') ? 'ADJUST-IN' : 'ADJUST-OUT';
                $stmt = $conn->prepare("
                    INSERT INTO stock_movements (part_id, movement_type, qty, notes)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->bind_param("isis", $part_id, $movement_type, $qty, $notes);
                $stmt->execute();
                $stmt->close();

                $msg = "Stock adjusted successfully. New stock: {$newStock}";
            } else {
                $msg = "Error updating stock: " . h($stmt->error);
                $stmt->close();
            }
        }
    }
}

// Load parts for dropdown
$parts = $conn->query("
    SELECT id, part_number, part_name, stock_qty
    FROM replacement_parts
    ORDER BY part_name ASC
");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Stock Adjustment</title>
<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial, sans-serif;
}
.wrap {
    width:60%;
    margin:30px auto;
    background:#111;
    border:2px solid #b00000;
    border-radius:8px;
    padding:20px;
}
h2 {
    text-align:center;
    color:#ff3333;
}
label {
    display:block;
    margin-top:10px;
}
select, input[type=number], textarea {
    width:100%;
    padding:7px;
    margin-top:4px;
    background:#222;
    color:#fff;
    border:1px solid #444;
    border-radius:4px;
}
button {
    margin-top:15px;
    padding:10px;
    width:100%;
    background:#b00000;
    border:none;
    color:#fff;
    font-size:15px;
    border-radius:4px;
    cursor:pointer;
}
button:hover {
    background:#ff0000;
}
.msg {
    margin:10px 0;
    padding:8px;
    border-radius:4px;
    text-align:center;
}
.msg-ok {
    background:#063f06;
    border:1px solid #0f0;
}
.msg-err {
    background:#5a0000;
    border:1px solid #f00;
}
</style>
</head>
<body>
<div class="wrap">
    <h2>Stock Adjustment (Manual)</h2>

    <?php if ($msg): ?>
        <div class="msg <?= (strpos($msg, 'successfully') !== false) ? 'msg-ok' : 'msg-err'; ?>">
            <?= h($msg) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Part</label>
        <select name="part_id" required>
            <option value="">-- Select Part --</option>
            <?php while ($p = $parts->fetch_assoc()): ?>
                <option value="<?= (int)$p['id'] ?>">
                    <?= h($p['part_name']) ?> (<?= h($p['part_number']) ?>) — Stock: <?= (int)$p['stock_qty'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Direction</label>
        <select name="direction" required>
            <option value="IN">Add to Stock</option>
            <option value="OUT">Remove from Stock</option>
        </select>

        <label>Quantity</label>
        <input type="number" name="qty" min="1" required>

        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Reason for adjustment (stock take, damage, etc.)"></textarea>

        <button type="submit">Apply Adjustment</button>
    </form>
</div>
</body>
</html>
