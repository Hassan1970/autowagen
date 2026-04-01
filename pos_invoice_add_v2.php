<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

$invoice_number = 'INV-' . date('Ymd-His');
$invoice_date   = date('Y-m-d');
?>

<!DOCTYPE html>
<html>
<head>
<title>POS Invoice – Add</title>

<style>
body{background:#000;color:#fff;font-family:Arial;}

.section{
border:1px solid #222;
padding:15px;
margin-bottom:20px;
background:#050505;
}

input,select,textarea{
width:100%;
padding:8px;
margin-top:4px;
background:#0a0a0a;
border:1px solid #222;
color:#fff;
}

.grid2{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

.search-results{
position:absolute;
background:#000;
border:1px solid #333;
width:100%;
max-height:200px;
overflow:auto;
z-index:999;
}

.search-item{
padding:8px;
cursor:pointer;
border-bottom:1px solid #222;
}
.search-item:hover{background:#1a1a1a;}

.SRC_STRIP{color:#ff9933;cursor:pointer;}
.SRC_TP{color:#33cc33;cursor:pointer;}
.SRC_OEM{color:#4da6ff;cursor:pointer;}
.SRC_NEW{color:#cc66ff;cursor:pointer;}

button{
padding:10px 14px;
background:#c00000;
border:none;
color:#fff;
cursor:pointer;
}
button:hover{background:#ff0000;}

table{
width:100%;
border-collapse:collapse;
margin-top:10px;
}

th,td{
border:1px solid #222;
padding:10px;
}

th{background:#111;}

#grand_total{
color:red;
font-size:22px;
font-weight:bold;
}
</style>
</head>

<body>

<h2>POS Invoice – Add</h2>

<form method="POST" action="pos_invoice_add_save.php">

<input type="hidden" name="invoice_number" value="<?= $invoice_number ?>">
<input type="hidden" name="invoice_date" value="<?= $invoice_date ?>">
<input type="hidden" name="items_json" id="items_json">
<input type="hidden" name="grand_total" id="grand_total_input">

<!-- ================= CUSTOMER ================= -->
<div class="section">
<h3>Customer</h3>

<div style="position:relative">
<input type="text" id="customer_search" placeholder="Search customer...">
<div id="customer_results" class="search-results" style="display:none"></div>
</div>

<input type="hidden" name="customer_id" id="customer_id">

<div class="grid2">
<input type="text" id="customer_name" placeholder="Full Name">
<input type="text" id="customer_phone" placeholder="Phone">
<input type="text" id="customer_id_number" placeholder="ID Number">
</div>

<textarea id="customer_address" placeholder="Address"></textarea>

<br>
<button type="button" onclick="openCustomerPage()">➕ Add Customer</button>

</div>

<!-- ================= INVOICE ================= -->
<div class="section">
<h3>Invoice</h3>

<div class="grid2">
<div>
<b>Invoice #:</b><br><?= $invoice_number ?><br><br>
<b>Date:</b><br><?= $invoice_date ?>
</div>

<div>
<label>Payment Method</label>
<select name="payment_method">
<option>Cash</option>
<option>Card</option>
<option>EFT</option>
<option>Mixed</option>
</select>

<label>Amount Paid</label>
<input type="number" name="amount_paid" value="0">
</div>
</div>
</div>

<!-- ================= ITEMS ================= -->
<div class="section">

<div>
<span class="SRC_STRIP" onclick="openStripped()">● Stripped</span>
<span class="SRC_TP" onclick="openThirdParty()">● Third Party</span>
<span class="SRC_OEM" onclick="openOEM()">● OEM</span>
<span class="SRC_NEW" onclick="openReplacement()">● Replacement</span> <!-- ✅ ADDED -->
</div>

<div style="display:flex;gap:10px;margin-top:10px">

<input type="text" id="item_search" placeholder="Search item..." onclick="openPopup()" readonly>

<input type="number" id="item_qty" value="1" style="width:80px">
<input type="number" id="item_price" placeholder="Price" style="width:120px">

<button type="button" onclick="addItem()">Add</button>

</div>

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
<tbody id="items_body"></tbody>
</table>

<div style="text-align:right;margin-top:10px;">
TOTAL: R <span id="grand_total">0.00</span>
</div>

</div>

<button type="submit">SAVE INVOICE</button>

</form>

<script>

let CART=[];
let SELECTED_ITEM=null;

/* ================= CUSTOMER SEARCH ================= */
customer_search.addEventListener("keyup",function(){
let q=this.value;

if(q.length<2){
customer_results.style.display="none";
return;
}

fetch("pos_customer_search.php?q="+q)
.then(r=>r.json())
.then(rows=>{
customer_results.innerHTML="";
rows.forEach(c=>{
customer_results.innerHTML+=`
<div class="search-item" onclick='selectCustomer(${JSON.stringify(c)})'>
${c.full_name}
</div>`;
});
customer_results.style.display="block";
});
});

function selectCustomer(c){
customer_id.value=c.id;
customer_name.value=c.full_name;
customer_phone.value=c.phone||"";
customer_id_number.value=c.id_number||"";
customer_address.value=c.address||"";
customer_search.value=c.full_name;
customer_results.style.display="none";
}

/* ================= OPEN CUSTOMER PAGE ================= */
function openCustomerPage(){
window.open("customer_add.php","AddCustomer","width=900,height=600");
}

/* ================= POPUPS ================= */
function openPopup(){
window.open("pos_item_search.php","ItemSearch","width=1000,height=600");
}

function openStripped(){
window.open("stripped_list.php","Stripped","width=1100,height=700");
}

function openThirdParty(){
window.open("popup_third_party_parts.php","ThirdParty","width=1100,height=700");
}

function openOEM(){
window.open("popup_oem_parts.php","OEM","width=1100,height=700");
}

/* ✅ ADDED FUNCTION */
function openReplacement(){
window.open("popup_replacement_parts.php","Replacement","width=1100,height=700");
}

/* ================= RECEIVE DATA ================= */
window.addEventListener("message", function(e){

let data = e.data;

/* ITEM */
if(data.type==="ITEM_SELECTED"){

let it = data.item;

SELECTED_ITEM = {
id: it.id,
name: it.name,
price: it.price || 0,
source: it.source,
vehicle_stock_code: it.vehicle_stock_code
};

item_search.value = SELECTED_ITEM.name;
item_price.value = SELECTED_ITEM.price;
}

/* CUSTOMER */
if(data.type==="CUSTOMER_SELECTED"){

let c = data.customer;

customer_id.value = c.id;
customer_name.value = c.full_name;
customer_phone.value = c.phone || "";
customer_id_number.value = c.id_number || "";
customer_address.value = c.address || "";

customer_search.value = c.full_name;
}

});

/* ================= ADD ITEM ================= */
function addItem(){

if(!SELECTED_ITEM){alert("Select item first");return;}

let qty=parseFloat(item_qty.value);
let price=parseFloat(item_price.value);
let total=qty*price;

let uid=Date.now();

CART.push({
id: SELECTED_ITEM.id,
uid: uid,
name: SELECTED_ITEM.name,
qty: qty,
price: price,
source: SELECTED_ITEM.source,
vehicle_stock_code: SELECTED_ITEM.vehicle_stock_code
});

items_body.innerHTML+=`
<tr data-uid="${uid}">
<td>${SELECTED_ITEM.name}</td>
<td>${SELECTED_ITEM.vehicle_stock_code||"-"}</td>
<td>${SELECTED_ITEM.source}</td>
<td>${qty}</td>
<td>${price.toFixed(2)}</td>
<td>${total.toFixed(2)}</td>
<td><button type="button" onclick="removeRow(this)">X</button></td>
</tr>`;

item_search.value="";
item_qty.value=1;
item_price.value="";
SELECTED_ITEM=null;

updateTotal();
updateJSON();
}

/* ================= REMOVE ================= */
function removeRow(btn){
let row=btn.closest("tr");
let uid=row.getAttribute("data-uid");

CART=CART.filter(i=>i.uid!=uid);
row.remove();

updateTotal();
updateJSON();
}

/* ================= TOTAL ================= */
function updateTotal(){
let total=0;

document.querySelectorAll("#items_body tr").forEach(r=>{
total+=parseFloat(r.children[5].innerText);
});

grand_total.innerText=total.toFixed(2);
grand_total_input.value=total.toFixed(2);
}

/* ================= JSON ================= */
function updateJSON(){
items_json.value=JSON.stringify(CART);
}

</script>

</body>
</html>