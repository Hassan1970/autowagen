<?php
require_once __DIR__ . '/config/config.php';
$page_title = "View 3rd Party Supplier";
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
?>

<style>
.view-box {
    background:#111;
    border:1px solid #b00000;
    border-radius:8px;
    padding:25px;
    max-width:850px;
    margin:30px auto;
}

.view-row {
    display:flex;
    padding:10px 0;
    border-bottom:1px solid #222;
}

.view-label {
    width:180px;
    font-weight:bold;
    color:#ccc;
}

.view-value {
    flex:1;
    color:#fff;
}
</style>

<div class="page-header">
    <div>
        <h1>3rd Party Supplier</h1>
        <p>View supplier details.</p>
    </div>
    <div>
        <a href="third_party_suppliers_list.php" class="btn secondary">Back</a>
    </div>
</div>

<div class="view-box">

    <div class="view-row">
        <div class="view-label">Supplier Name</div>
        <div class="view-value"><?=h($sup['supplier_name'])?></div>
    </div>

    <div class="view-row">
        <div class="view-label">Contact Person</div>
        <div class="view-value"><?=h($sup['contact_person'])?></div>
    </div>

    <div class="view-row">
        <div class="view-label">Phone</div>
        <div class="view-value"><?=h($sup['phone'])?></div>
    </div>

    <div class="view-row">
        <div class="view-label">Email</div>
        <div class="view-value"><?=h($sup['email'])?></div>
    </div>

    <div class="view-row">
        <div class="view-label">Address</div>
        <div class="view-value"><?=nl2br(h($sup['address']))?></div>
    </div>

    <div style="margin-top:20px;text-align:right;">
        <a href="third_party_supplier_edit.php?id=<?=$id?>" class="btn">Edit Supplier</a>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
