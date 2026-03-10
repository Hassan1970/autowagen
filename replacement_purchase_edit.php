<?php
require_once "config/config.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('h')) {
    function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid invoice ID.");
}

// Load suppliers
$sres = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
$suppliers = [];
if ($sres) {
    while ($row = $sres->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Load invoice
$sql = "SELECT * FROM replacement_supplier_invoices WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$invRes = $stmt->get_result();
$invoice = $invRes->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("Invoice not found.");
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $supplier_id    = (int)($_POST['supplier_id'] ?? 0);
    $invoice_number = trim($_POST['invoice_number'] ?? '');
    $invoice_date   = trim($_POST['invoice_date'] ?? '');
    $notes          = trim($_POST['notes'] ?? '');

    if ($supplier_id <= 0 || $invoice_number === '' || $invoice_date === '') {
        $msg = "Supplier, invoice number and date are required.";
    } else {
        $sql = "
            UPDATE replacement_supplier_invoices
            SET supplier_id = ?, invoice_number = ?, invoice_date = ?, notes = ?
            WHERE id = ?
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $supplier_id, $invoice_number, $invoice_date, $notes, $id);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: replacement_purchase_view.php?id=".$id."&msg=Updated");
            exit;
        } else {
            $msg = "Error updating invoice: " . $stmt->error;
            $stmt->close();
        }
    }
}

// Reload latest if needed (for safety, use $invoice + POST overlay)
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Replacement Supplier Invoice</title>
<style>
body { background:#000; color:#fff; font-family:Arial,sans-serif; margin:0; }
.wrap {
    width:70%;
    margin:25px auto;
    background:#111;
    border:2px solid #b00000;
    border-radius:8px;
    padding:20px 25px 25px;
}
h1 { margin:0 0 15px 0; color:#ff3333; }
label { display:block; margin-top:10px; font-size:13px; }
input, select, textarea {
    width:100%;
    padding:7px;
    margin-top:4px;
    background:#222;
    color:#fff;
    border:1px solid #444;
    border-radius:4px;
}
button {
    background:#b00000;
    color:#fff;
    border:none;
    border-radius:4px;
    padding:9px 16px;
    margin-top:15px;
    cursor:pointer;
}
.msg {
    margin-bottom:10px;
    padding:6px 8px;
    background:#330000;
    border:1px solid #900;
    border-radius:4px;
    font-size:12px;
}
.btn-back {
    display:inline-block;
    margin-top:10px;
    padding:6px 12px;
    background:#444;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
    font-size:12px;
}
</style>
</head>
<body>

<div class="wrap">
    <h1>Edit Replacement Supplier Invoice</h1>

    <?php if ($msg): ?>
        <div class="msg"><?php echo h($msg); ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Supplier</label>
        <select name="supplier_id" required>
            <option value="">-- Select Supplier --</option>
            <?php
            $currentSup = (int)($invoice['supplier_id']);
            foreach ($suppliers as $s):
            ?>
                <option value="<?php echo (int)$s['id']; ?>"
                    <?php echo ($currentSup === (int)$s['id']) ? 'selected' : ''; ?>>
                    <?php echo h($s['supplier_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Invoice Number</label>
        <input type="text" name="invoice_number" required
               value="<?php echo h($invoice['invoice_number']); ?>">

        <label>Invoice Date</label>
        <input type="date" name="invoice_date" required
               value="<?php echo h($invoice['invoice_date']); ?>">

        <label>Notes</label>
        <textarea name="notes" rows="4"><?php echo h($invoice['notes']); ?></textarea>

        <button type="submit">Save Changes</button>
        <a href="replacement_purchase_view.php?id=<?php echo (int)$id; ?>" class="btn-back">Cancel</a>
    </form>
</div>

</body>
</html>
