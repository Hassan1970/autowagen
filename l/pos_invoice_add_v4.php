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
body{background:#000;color:#fff;font-family:Arial}
.section{border:1px solid #333;padding:15px;margin-bottom:12px;background:#0b0b0b;}
input,select,textarea{width:100%;padding:8px;margin-top:4px;background:#111;border:1px solid #333;color:#fff;}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}

.SRC_STRIP{color:#ff9933}
.SRC_TP{color:#33cc33}
.SRC_OEM{color:#4da6ff}
.SRC_NEW{color:#cc66ff}

.stock-in,.stock-low,.stock-last,.stock-out{
padding:3px 8px;border-radius:20px;font-size:12px;font-weight:bold;display:inline-block;
}
.stock-in{background:#003d1f;color:#00ff88;}
.stock-low{background:#3d3300;color:#ffcc00;}
.stock-last{background:#3d0000;color:#ff4444;}
.stock-out{background:#222;color:#777;}

.search-results{
position:absolute;background:#000;border:1px solid #333;
width:100%;max-height:260px;overflow-y:auto;z-index:9999;
}
.search-item{padding:8px;cursor:pointer;border-bottom:1px solid #111;}
.search-item:hover{background:#1a1a1a;}

button{padding:10px;background:#b00000;border:none;color:white;cursor:pointer;}
.clear-btn{background:#444;margin-left:10px;}
#grand_total{font-size:26px;font-weight:bold;color:#ff0000;}
.legend{display:flex;justify-content:space-between;margin-bottom:8px;}
</style>
</head>
<body>

<h2>POS Invoice – Add</h2>

<form method="POST" action="pos_invoice_add_save.php">

<!-- CUSTOMER -->
<div class="section">
<h3>Customer</h3>

<div style="position:relative">
<input type="text" id="customer_search" placeholder="Search customer..." autocomplete="off">
<div id="customer_results" class="search-results" style="display:none"></div>
</div>

<input type="hidden" name="customer_id" id="customer_id">
<input type="hidden" name="customer_id_number" id="customer_id_number">

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
<input type="text" id="customer_id_display" readonly style="background:#222;color:#888">
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

<input type="hidden" name="invoice_number" value="<?= $invoice_number ?>">
<input type="hidden" name="invoice_date" value="<?= $invoice_date ?>">

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
<input type="number" step="0.01" name="amount_paid" value="0">
</div>
</div>
</div>

<!-- ITEMS -->
<div class="section">

<div class="legend">
<div>
<span class="SRC_STRIP" style="cursor:pointer" onclick="openStripped()">● Stripped</span>
<span class="SRC_TP">● Third-Party</span>
<span class="SRC_OEM">● OEM</span>
<span class="SRC_NEW">● Replacement</span>
</div>
<div>
Stock:
<span class="stock-in">● In</span>
<span class="stock-low">● Low</span>
<span class="stock-last">● Last</span>
<span class="stock-out">● Out</span>
</div>
</div>

<div style="display:flex;gap:10px;margin-bottom:10px">
<div style="position:relative;flex:1">
<input type="text" id="item_search" placeholder="Search item" autocomplete="off">
<div id="search_results" class="search-results" style="display:none"></div>
</div>
<input type="number" id="item_qty" value="1" min="1" style="width:70px">
<input type="number" id="item_price" step="0.01" placeholder="0.00" style="width:100px">
<button type="button" onclick="addItem()">Add</button>
</div>

<table width="100%">
<thead>
<tr>
<th>Item</th>
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
<button type="button" class="clear-btn" onclick="clearInvoice()">Clear Invoice</button>
</div>

</div>

<input type="hidden" name="items_json" id="items_json">
<input type="hidden" name="grand_total" id="grand_total_input">

<button type="submit">SAVE INVOICE</button>
</form>

<script>

let CART={}
let SELECTED_ITEM=null

function openStripped(){
window.open("stripped_list.php","StrippedWindow","width=1100,height=700");
}

/* CUSTOMER SEARCH */
document.getElementById("customer_search").addEventListener("keyup",function(){
let q=this.value.trim()
if(q.length<2){customer_results.style.display="none";return}

fetch("pos_customer_search.php?q="+encodeURIComponent(q))
.then(r=>r.json())
.then(rows=>{
customer_results.innerHTML=""

if(rows.length===0){
customer_results.innerHTML=`
<div class="search-item" style="background:#1e7e34;color:white;font-weight:bold"
onclick="window.location='customer_add.php?return=pos_invoice_add_v2.php&name=${encodeURIComponent(q)}'">
+ Add "${q}" as New Customer
</div>`
customer_results.style.display="block"
return
}

rows.forEach(c=>{
customer_results.innerHTML+=`
<div class="search-item"
onclick='selectCustomer(${JSON.stringify(c)})'>
${c.full_name}
</div>`
})

customer_results.style.display="block"
})
})

function selectCustomer(c){
customer_id.value=c.id
customer_name.value=c.full_name
customer_phone.value=c.phone ?? ""
customer_address.value=c.address ?? ""
customer_id_number.value=c.id_number ?? ""
customer_id_display.value=c.id_number ?? ""
customer_results.style.display="none"
}

/* ITEM SEARCH */
document.getElementById("item_search").addEventListener("keyup",function(){
let q=this.value.trim()
if(q.length<2){search_results.style.display="none";return}

fetch("pos_item_search.php?q="+encodeURIComponent(q))
.then(r=>r.json())
.then(rows=>{
search_results.innerHTML=""

rows.forEach(it=>{
let stockClass="stock-in"
if(it.qty==0) stockClass="stock-out"
else if(it.qty==1) stockClass="stock-last"
else if(it.qty<=5) stockClass="stock-low"

search_results.innerHTML+=`
<div class="search-item SRC_${it.source}"
onclick='selectItem(${JSON.stringify(it)})'>
[${it.source}] ${it.name}<br>
<span class="${stockClass}">Stock: ${it.qty}</span> |
R ${parseFloat(it.price||0).toFixed(2)}
</div>`
})

search_results.style.display="block"
})
})

function selectItem(it){
SELECTED_ITEM=it
item_search.value=it.name
item_price.value=parseFloat(it.price||0).toFixed(2)
search_results.style.display="none"
}

function addItem(){
if(!SELECTED_ITEM)return
let qty=parseFloat(item_qty.value)
let price=parseFloat(item_price.value)||0
let id=Date.now()

CART[id]={id:SELECTED_ITEM.id,description:SELECTED_ITEM.name,qty:qty,price:price}

items_body.innerHTML+=`
<tr data-id="${id}">
<td>${SELECTED_ITEM.name}</td>
<td>${qty}</td>
<td>${price.toFixed(2)}</td>
<td>${(qty*price).toFixed(2)}</td>
<td><button type="button" onclick="removeItem(${id},this)">X</button></td>
</tr>`

updateTotal()

SELECTED_ITEM=null
item_search.value=""
item_price.value=""
}

function removeItem(id,btn){
delete CART[id]
btn.closest("tr").remove()
updateTotal()
}

function updateTotal(){
let total=0
for(let key in CART){total+=CART[key].qty*CART[key].price}
grand_total.textContent=total.toFixed(2)
items_json.value=JSON.stringify(Object.values(CART))
grand_total_input.value=total.toFixed(2)
}

function clearInvoice(){
CART={}
items_body.innerHTML=""
updateTotal()
}

/* STRIPPED RECEIVE (NO AUTO ADD) */
window.addEventListener("message",function(event){
if(event.data.type==="STRIPPED_PART_SELECTED"){
fetch("pos_get_stripped_part.php?id="+event.data.part_id)
.then(r=>r.json())
.then(part=>{
SELECTED_ITEM={
id:part.id,
name:part.part_name,
price:0,
qty:1,
source:"STRIP"
}
item_search.value=part.part_name
item_price.value=0
item_qty.value=1
})
}
})

</script>

</body>
</html>