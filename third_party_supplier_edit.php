<?php
require_once __DIR__ . '/config/config.php';
$page_title = "Edit 3rd Party Supplier";
include __DIR__ . '/includes/header.php';

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("<p style='color:red;text-align:center;'>Invalid Supplier ID</p>");
}

// Fetch supplier
$stmt = $conn->prepare("SELECT * FROM third_party_suppliers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$sup = $res->fetch_assoc();
$stmt->close();

if (!$sup) {
    die("<p style='color:red;text-align:center;'>Supplier not found.</p>");
}

$errors = [];
$success = "";

// ======================
// SAVE CHANGES
// ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name   = trim($_POST['supplier_name']);
    $person = trim($_POST['contact_person']);
    $phone  = trim($_POST['phone']);
    $email  = trim($_POST['email']);
    $addr   = trim($_POST['address']);

    if ($name === "") $errors[] = "Supplier Name is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE third_party_suppliers
            SET supplier_name=?, contact_person=?, phone=?, email=?, address=?
            WHERE id=?
        ");
        $stmt->bind_param("sssssi", $name, $person, $phone, $email, $addr, $id);
        $stmt->execute();
        $stmt->close();

        $success = "Supplier updated successfully!";
        
        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM third_party_suppliers WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $sup = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>

<style>
/* Local styling for this page only */
.input-box input, .input-box textarea {
    width:100%;
    padding:8px;
    border-radius:4px;
    border:1px solid #444;
    background:#000;
    color:#fff;
}
.input-box label {
    display:block;
    margin-bottom:4px;
    font-size:13px;
    color:#ccc;
}
</style>

<div class="page-header">
    <div>
        <h1>Edit 3rd Party Supplier</h1>
        <p>Update supplier information.</p>
    </div>
    <div>
        <a href="third_party_suppliers_list.php" class="btn secondary">Back</a>
    </div>
</div>

<div style="background:#111;border:1px solid #b00000;border-radius:8px;padding:25px;max-width:850px;margin:30px auto;">

    <?php if ($success): ?>
        <p style="color:#00cc66;font-weight:bold;"><?=h($success)?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div style="color:#ff5555;margin-bottom:10px;">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?=h($e)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" style="display:flex;flex-direction:column;gap:18px;">

        <!-- Supplier Name -->
        <div class="input-box">
            <label>Supplier Name *</label>
            <input type="text" name="supplier_name" required
                   value="<?=h($sup['supplier_name'])?>">
        </div>

        <!-- Row: Contact Person + Phone -->
        <div style="display:flex;gap:18px;">
            <div class="input-box" style="flex:1;">
                <label>Contact Person</label>
                <input type="text" name="contact_person"
                       value="<?=h($sup['contact_person'])?>">
            </div>

            <div class="input-box" style="flex:1;">
                <label>Phone</label>
                <input type="text" name="phone"
                       value="<?=h($sup['phone'])?>">
            </div>
        </div>

        <!-- Email -->
        <div class="input-box">
            <label>Email</label>
            <input type="text" name="email"
                   value="<?=h($sup['email'])?>">
        </div>

        <!-- Address -->
        <div class="input-box">
            <label>Address</label>
            <textarea name="address" rows="3"><?=h($sup['address'])?></textarea>
        </div>

        <div>
            <button class="btn">Save Changes</button>
        </div>

    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
