<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

$invoice_id = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

if($invoice_id<=0){
die("Invalid invoice");
}

/* ===============================
GET INVOICE
=============================== */

$stmt=$conn->prepare("
SELECT *
FROM pos_invoices
WHERE id=?
");

$stmt->bind_param("i",$invoice_id);
$stmt->execute();

$invoice=$stmt->get_result()->fetch_assoc();

if(!$invoice){
die("Invoice not found");
}

/* ===============================
GET ITEMS
=============================== */

$stmtItems=$conn->prepare("
SELECT *
FROM pos_invoice_items
WHERE invoice_id=?
ORDER BY id ASC
");

$stmtItems->bind_param("i",$invoice_id);
$stmtItems->execute();

$items=$stmtItems->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>POS Invoice – Edit</title>

<style>

body{
background:#000;
color:#fff;
font-family:Arial;
}

.section{
border:1px solid #333;
padding:15px;
margin-bottom:12px;
background:#0b0b0b;
}

table{
width:100%;
border-collapse:collapse;
}

th,td{
border:1px solid #333;
padding:8px;
text-align:left;
}

input{
width:60px;
background:#111;
border:1px solid #333;
color:#fff;
padding:4px;
}

button{
padding:8px 12px;
background:#b00000;
border:none;
color:#fff;
cursor:pointer;
}

.print-btn{
background:#0066cc;
}

.remove-btn{
background:#444;
}

#grand_total{
font-size:22px;
color:#ff0000;
font-weight:bold;
}

</style>
</head>

<body>

<h2>POS Invoice – Edit</h2>

<div class="section">

<b>Invoice #:</b> <?= $invoice['id'] ?><br>
<b>Date:</b> <?= $invoice['created_at'] ?><br>
<b>Customer:</b> <?= htmlspecialchars($invoice['customer_name']) ?><br>

</div>


<div class="section">

<table>

<thead>
<tr>
<th>Item</th>
<th>Vehicle Code</th>
<th>Type</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
<th></th>
</tr>
</thead>

<tbody id="items_body">

<?php while($row=$items->fetch_assoc()): ?>

<tr data-id="<?= $row['id'] ?>">

<td><?= htmlspecialchars($row['part_name']) ?></td>

<td><?= htmlspecialchars($row['vehicle_stock_code'] ?: '-') ?></td>

<td><?= htmlspecialchars($row['part_type']) ?></td>

<td>
<input type="number"
value="<?= $row['quantity'] ?>"
class="qty">
</td>

<td class="price">
<?= number_format($row['price'],2) ?>
</td>

<td class="subtotal">
<?= number_format($row['subtotal'],2) ?>
</td>

<td>
<button class="remove-btn" onclick="removeRow(this)">X</button>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>


<div style="margin-top:10px;text-align:right">

TOTAL: R <span id="grand_total">
<?= number_format($invoice['total_amount'],2) ?>
</span>

</div>

<br>

<button onclick="saveChanges()">SAVE CHANGES</button>

<a href="print_invoice.php?invoice_id=<?= $invoice_id ?>">
<button class="print-btn">PRINT AGAIN</button>
</a>

</div>


<script>

/* ===============================
UPDATE TOTAL WHEN QTY CHANGES
=============================== */

document.querySelectorAll(".qty").forEach(input=>{

input.addEventListener("input",function(){

let row=this.closest("tr")

let price=parseFloat(row.querySelector(".price").innerText)

let qty=parseFloat(this.value)

let subtotal=price*qty

row.querySelector(".subtotal").innerText=subtotal.toFixed(2)

updateTotal()

})

})


function removeRow(btn){

btn.closest("tr").remove()

updateTotal()

}


function updateTotal(){

let total=0

document.querySelectorAll("#items_body tr").forEach(row=>{

let sub=parseFloat(row.querySelector(".subtotal").innerText)

total+=sub

})

document.getElementById("grand_total").innerText=total.toFixed(2)

}


/* ===============================
SAVE CHANGES
=============================== */

function saveChanges(){

let rows=[]

document.querySelectorAll("#items_body tr").forEach(r=>{

rows.push({

id:r.dataset.id,

qty:r.querySelector(".qty").value

})

})

fetch("pos_invoice_edit_save.php",{

method:"POST",

headers:{
"Content-Type":"application/json"
},

body:JSON.stringify({

invoice_id:<?= $invoice_id ?>,

items:rows

})

})

.then(r=>r.text())

.then(msg=>{

alert(msg)

location.reload()

})

}

</script>

</body>
</html>