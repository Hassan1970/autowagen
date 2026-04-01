<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

/* ===== ADDITION 1 : EPC SELL PREFILL ===== */
$prefill_part = $_GET['part'] ?? '';

$invoice_number = 'INV-' . date('Ymd-His');
$invoice_date   = date('Y-m-d');
?>

<!DOCTYPE html>
<html>
<head>
<title>POS Invoice – Add</title>

<style>

body{background:#000;color:#fff;font-family:Arial;}

.section{border:1px solid #333;padding:15px;margin-bottom:12px;background:#0b0b0b;}

input,select,textarea{
width:100%;
padding:8px;
margin-top:4px;
background:#111;
border:1px solid #333;
color:#fff;
}

.grid2{
display:grid;
grid-template-columns:1fr 1fr;
gap:10px;
}

.SRC_STRIP{color:#ff9933;cursor:pointer}
.SRC_TP{color:#33cc33;cursor:pointer}
.SRC_OEM{color:#4da6ff}
.SRC_NEW{color:#cc66ff}

.stock-in{background:#003d1f;color:#00ff88;padding:3px 10px;border-radius:20px;font-size:12px;}
.stock-low{background:#3d3300;color:#ffcc00;padding:3px 10px;border-radius:20px;font-size:12px;}
.stock-last{background:#3d0000;color:#ff4444;padding:3px 10px;border-radius:20px;font-size:12px;}
.stock-out{background:#222;color:#777;padding:3px 10px;border-radius:20px;font-size:12px;}

.search-results{
position:absolute;
background:#000;
border:1px solid #333;
width:100%;
max-height:260px;
overflow-y:auto;
z-index:9999;
}

.search-item{
padding:8px;
border-bottom:1px solid #111;
display:flex;
justify-content:space-between;
cursor:pointer;
}

.search-item:hover{
background:#1a1a1a;
}

.vehicle-info{
font-size:12px;
color:#888;
margin-top:3px;
}

button{
padding:10px;
background:#b00000;
border:none;
color:#fff;
cursor:pointer;
}

.clear-btn{background:#444;}

#grand_total{
font-size:26px;
font-weight:bold;
color:#ff0000;
}

.legend{margin-bottom:8px}

table{
width:100%;
border-collapse:collapse;
table-layout:fixed;
}

th,td{
border:1px solid #333;
padding:8px;
}

th{
background:#111;
}

.qty{text-align:center;}
.price{text-align:right;}
.total{text-align:right;}

</style>
</head>

<body>

<h2>POS Invoice – Add</h2>

<form method="POST" action="pos_invoice_add_save.php">

<input type="hidden" name="invoice_number" value="<?= $invoice_number ?>">
<input type="hidden" name="invoice_date" value="<?= $invoice_date ?>">
<input type="hidden" name="items_json" id="items_json">
<input type="hidden" name="grand_total" id="grand_total_input">

<!-- CUSTOMER -->
<div class="section">
<h3>Customer</h3>

<div style="position:relative">
<input type="text" id="customer_search" placeholder="Search customer..." autocomplete="off">
<div id="customer_results" class="search-results" style="display:none"></div>
</div>

<input type="hidden" name="customer_id" id="customer_id">

<div class="grid2">
<div>
<label>Full Name</label>
<input type="text" name="customer_name" id="customer_name">
</div>
<div>
<label>Phone</label>
<input type="text" name="customer_phone" id="customer_phone">
</div>
<div>
<label>ID Number</label>
<input type="text" name="customer_id_number" id="customer_id_number">
</div>
</div>

<label>Address</label>
<textarea name="customer_address" id="customer_address"></textarea>

</div>

<!-- INVOICE -->
<div class="section">
<h3>Invoice</h3>

<div><b>Invoice #:</b> <?= $invoice_number ?></div>
<div><b>Date:</b> <?= $invoice_date ?></div>

<div class="grid2">
<div>
<label>Payment Method</label>
<select name="payment_method">
<option>Cash</option>
<option>Card</option>
<option>EFT</option>
<option>Mixed</option>
</select>
</div>

<div>
<label>Amount Paid</label>
<input type="number" name="amount_paid" step="0.01" value="0">
</div>
</div>
</div>

<!-- ITEMS -->
<div class="section">

<div class="legend">
<span class="SRC_STRIP" onclick="openStripped()">● Stripped</span>
<span class="SRC_TP" onclick="openThirdParty()">● Third-Party</span>
<span class="SRC_OEM">● OEM</span>
<span class="SRC_NEW">● Replacement</span>
</div>

<div style="display:flex;gap:10px;margin-bottom:10px">

<div style="position:relative;flex:1">
<input type="text" id="item_search" placeholder="Search item">
<div id="search_results" class="search-results" style="display:none"></div>
</div>

<input type="number" id="item_qty" value="1" style="width:70px">
<input type="number" id="item_price" step="0.01" style="width:100px">

<button type="button" onclick="addItem()">Add</button>

</div>

<table>
<thead>
<tr>
<th style="width:35%">Item</th>
<th style="width:15%">Vehicle Code</th>
<th style="width:15%">Type</th>
<th style="width:8%">Qty</th>
<th style="width:12%">Price</th>
<th style="width:12%">Total</th>
<th style="width:3%"></th>
</tr>
</thead>
<tbody id="items_body"></tbody>
</table>

<div style="text-align:right;margin-top:10px;">
TOTAL: R <span id="grand_total">0.00</span>
<button type="button" class="clear-btn" onclick="clearInvoice()">Clear Invoice</button>
</div>

</div>

<button type="submit">SAVE INVOICE</button>

</form>

<script>

let CART=[];
let SELECTED_ITEM=null;

/* POPUPS */
function openStripped(){
window.open("stripped_list.php","Stripped","width=1100,height=700");
}

function openThirdParty(){
window.open("popup_third_party_parts.php","ThirdParty","width=1100,height=700");
}

/* RECEIVE */
window.addEventListener("message",function(e){

if(e.data.type==="STRIPPED_PART_SELECTED"){
fetch("pos_get_stripped_part.php?id="+e.data.part_id)
.then(r=>r.json())
.then(part=>{
SELECTED_ITEM={
id:part.id,
name:part.part_name,
price:0,
source:"STRIP",
vehicle_stock_code:part.vehicle_stock_code||"",
vehicle_name:part.vehicle_name||""
};
item_search.value=part.part_name;
item_price.value=0;
});
}

if(e.data.type==="THIRD_PARTY_SELECTED"){
fetch("pos_get_third_party.php?id="+e.data.part_id)
.then(r=>r.json())
.then(part=>{
SELECTED_ITEM={
id:part.id,
name:part.description,
price:part.selling_price,
source:"TP",
vehicle_stock_code:"",
vehicle_name:""
};
item_search.value=part.description;
item_price.value=part.selling_price;
});
}

});

/* ITEM SEARCH */
item_search.addEventListener("keyup",function(){

let q=this.value.trim();
if(q.length<2){search_results.style.display="none";return;}

fetch("pos_item_search.php?q="+encodeURIComponent(q))
.then(r=>r.json())
.then(rows=>{

search_results.innerHTML="";

rows.forEach(it=>{

let qty=parseInt(it.qty||0);

let stockClass="stock-in";
if(qty==0)stockClass="stock-out";
else if(qty==1)stockClass="stock-last";
else if(qty<=3)stockClass="stock-low";

search_results.innerHTML+=`
<div class="search-item SRC_${it.source}" onclick='selectItem(${JSON.stringify(it)})'>
<div>
<strong>[${it.source}] ${it.name}</strong>
<div class="vehicle-info">
${it.vehicle_name||""}
${it.vehicle_stock_code?" | Vehicle Code: "+it.vehicle_stock_code:""}
${it.code?" | Part Code: "+it.code:""}
</div>
</div>
<div>
<span class="${stockClass}">${qty}</span>
&nbsp; R ${parseFloat(it.price||0).toFixed(2)}
</div>
</div>`;
});

search_results.style.display="block";

});

});

function selectItem(it){
SELECTED_ITEM=it;
item_search.value=it.name;
item_price.value=parseFloat(it.price||0).toFixed(2);
search_results.style.display="none";
}

/* ADD ITEM */
function addItem(){

if(!SELECTED_ITEM){alert("Select item first");return;}

let qty=parseFloat(item_qty.value);
let price=parseFloat(item_price.value);
let total=qty*price;

let uid=Date.now();

CART.push({
id:SELECTED_ITEM.id,
uid:uid,
name:SELECTED_ITEM.name,
qty:qty,
price:price,
source:SELECTED_ITEM.source,
vehicle_stock_code:SELECTED_ITEM.vehicle_stock_code||"",
vehicle_name:SELECTED_ITEM.vehicle_name||""
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

updateTotal();
updateJSON();
}

/* FIX REMOVE */
function removeRow(btn){

let row=btn.closest("tr");
let uid=row.getAttribute("data-uid");

CART=CART.filter(i=>i.uid!=uid);

row.remove();

updateTotal();
updateJSON();
}

/* TOTAL */
function updateTotal(){
let total=0;
document.querySelectorAll("#items_body tr").forEach(r=>{
total+=parseFloat(r.children[5].innerText);
});
grand_total.innerText=total.toFixed(2);
grand_total_input.value=total.toFixed(2);
}

function updateJSON(){
items_json.value=JSON.stringify(CART);
}

function clearInvoice(){
items_body.innerHTML="";
CART=[];
updateTotal();
updateJSON();
}

</script>

</body>
</html>