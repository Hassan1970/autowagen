<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';
$return = $_GET['return'] ?? 'customer_list.php';
?>

<style>
.wrap{width:92%;margin:30px auto}
.box{border:1px solid #ff2e2e;padding:20px;border-radius:12px;background:#0b0b0b}
input,textarea{width:100%;padding:10px;margin-top:5px;margin-bottom:15px;background:#111;border:1px solid #333;color:white;border-radius:8px}
button{padding:12px 20px;background:#b00000;color:white;border:none;border-radius:8px;font-weight:bold}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
</style>

<div class="wrap">
<div class="box">

<h2>Add Customer</h2>

<form method="POST" action="customer_add_save.php" enctype="multipart/form-data">

<input type="hidden" name="return" value="<?= htmlspecialchars($return) ?>">

<div class="grid">
<div>
<label>Name *</label>
<input type="text" name="name" required>
</div>

<div>
<label>Phone</label>
<input type="text" name="phone">
</div>
</div>

<label>ID Number</label>
<input type="text" name="id_number">

<label>Address</label>
<textarea name="address"></textarea>

<label>ID Document</label>
<input type="file" name="id_document">

<label>Proof of Residence</label>
<input type="file" name="proof_residence">

<button type="submit">Save Customer</button>

</form>

</div>
</div>