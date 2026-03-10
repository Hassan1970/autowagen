<?php
require_once __DIR__ . '/config/config.php';
$page_title = "Add 3rd Party Supplier";
include __DIR__ . '/includes/header.php';

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['supplier_name'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $errors[] = "Supplier name is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO third_party_suppliers (supplier_name, contact_person, phone, email, address)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $name, $contact, $phone, $email, $address);

        if ($stmt->execute()) {
            $success = "3rd Party Supplier added successfully!";
            $_POST = [];
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<style>
.form-box {
    background:#111;
    border:1px solid #b00000;
    border-radius:8px;
    padding:20px;
    max-width:900px;
    margin:20px auto;
}

.form-row {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
    margin-bottom:12px;
}

.form-col {
    flex:1 1 200px;
}

label {
    display:block;
    font-size:13px;
    margin-bottom:3px;
}

input[type=text],
textarea {
    width:100%;
    padding:7px;
    border:1px solid #444;
    background:#000;
    color:#fff;
    border-radius:4px;
}

textarea {
    height:70px;
}

</style>

<div class="page-header">
    <div>
        <h1>Add 3rd Party Supplier</h1>
        <p>Create a new 3rd party supplier.</p>
    </div>
    <div>
        <a href="third_party_suppliers_list.php" class="btn secondary">Back</a>
    </div>
</div>

<div class="form-box">

    <?php if ($success): ?>
        <p style="color:#6f6;font-weight:bold;"><?=h($success)?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div style="color:#f66;">
            <?php foreach ($errors as $e): ?>
                <p>• <?=h($e)?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <div class="form-row">
            <div class="form-col">
                <label>Supplier Name *</label>
                <input type="text" name="supplier_name" required
                       value="<?=h($_POST['supplier_name'] ?? '')?>">
            </div>

            <div class="form-col">
                <label>Contact Person</label>
                <input type="text" name="contact_person"
                       value="<?=h($_POST['contact_person'] ?? '')?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-col">
                <label>Phone</label>
                <input type="text" name="phone"
                       value="<?=h($_POST['phone'] ?? '')?>">
            </div>

            <div class="form-col">
                <label>Email</label>
                <input type="text" name="email"
                       value="<?=h($_POST['email'] ?? '')?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-col" style="flex:1 1 100%;">
                <label>Address</label>
                <textarea name="address"><?=h($_POST['address'] ?? '')?></textarea>
            </div>
        </div>

        <button class="btn" style="margin-top:10px;">Save Supplier</button>

    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
