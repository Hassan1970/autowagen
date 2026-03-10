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
.invoice-header{border:1px solid #333;padding:10px;background:#0b0b0b;margin-bottom:10px}
label{font-weight:bold}
table{width:100%;border-collapse:collapse}
th,td{padding:8px;border-bottom:1px solid #333}
th{background:#111}

/* SOURCE COLOURS */
.SRC_STRIP{color:#ff9933}
.SRC_TP{color:#33cc33}
.SRC_OEM{color:#4da6ff}
.SRC_NEW{color:#cc66ff}

/* SEARCH */
.search-box{position:relative;flex:1}
.search-results{
    position:absolute;
    background:#000;
    border:1px solid #333;
    width:100%;
    max-height:260px;
    overflow-y:auto;
    z-index:9999;
}
.search-item{padding:6px 8px;cursor:pointer}
.search-item:hover{background:#222}

.add-items{border:1px solid #333;padding:15px;background:#0b0b0b}
input,button{padding:8px}
</style>
</head>

<body>

<h2>POS Invoice – Add</h2>

<div class="invoice-header">
    <div><label>Invoice #:</label> <?= $invoice_number ?></div>
    <div><label>Date:</label> <?= $invoice_date ?></div>
    <div><label>Customer:</label> Not selected</div>
    <div><label>Documents:</label> ✖✖</div>
</div>

<!-- LEGEND -->
<div style="margin-bottom:10px;font-size:13px">
    <span class="SRC_STRIP">🟠 Stripped (Recorded)</span> |
    <span class="SRC_TP">🟢 Third-Party</span> |
    <span class="SRC_OEM">🔵 OEM</span> |
    <span class="SRC_NEW">🟣 Replacement</span>
</div>

<!-- ADD ITEMS -->
<div class="add-items">
<h3>Add Items</h3>

<div style="display:flex;gap:10px;margin-bottom:10px">
    <div class="search-box">
        <input type="text" id="item_search" placeholder="Search item" autocomplete="off">
        <div id="search_results" class="search-results" style="display:none"></div>
    </div>
    <input type="number" id="item_qty" value="1" min="1" style="width:70px">
    <input type="number" id="item_price" step="0.01" placeholder="0.00" style="width:100px">
    <button onclick="addItem()">Add</button>
</div>

<table>
<thead>
<tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr>
</thead>
<tbody id="items_body"></tbody>
</table>

<h3 style="text-align:right">Total: <span id="grand_total">0.00</span></h3>
</div>

<script>
const itemSearch = document.getElementById('item_search');
const searchBox  = document.getElementById('search_results');
const qtyInput   = document.getElementById('item_qty');
const priceInput = document.getElementById('item_price');
const itemsBody  = document.getElementById('items_body');
const totalEl    = document.getElementById('grand_total');
let total = 0;

/* ===============================
   SEARCH (HARD FIX)
================================ */
itemSearch.onkeyup = function(){
    const q = this.value.trim();
    if(q.length < 2){
        searchBox.style.display='none';
        searchBox.innerHTML='';
        return;
    }

    fetch('/pos_item_search.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(rows => {
            searchBox.innerHTML='';
            if(!rows.length){
                searchBox.style.display='none';
                return;
            }
            rows.forEach(it => {
                const div = document.createElement('div');
                div.className = 'search-item SRC_' + it.source;
                div.textContent = '['+it.source+'] ' + it.name + ' (' + it.code + ')';
                div.onclick = function(){
                    itemSearch.value = it.name;
                    priceInput.value = parseFloat(it.price || 0).toFixed(2);
                    searchBox.style.display='none';
                };
                searchBox.appendChild(div);
            });
            searchBox.style.display='block';
        })
        .catch(err => console.error(err));
};

function addItem(){
    const name = itemSearch.value.trim();
    const qty  = parseFloat(qtyInput.value);
    const price = parseFloat(priceInput.value) || 0;
    if(!name || qty <= 0) return;

    const line = qty * price;
    total += line;

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>${name}</td>
        <td>${qty}</td>
        <td>${price.toFixed(2)}</td>
        <td>${line.toFixed(2)}</td>
        <td><button onclick="removeItem(this,${line})">X</button></td>
    `;
    itemsBody.appendChild(tr);
    totalEl.textContent = total.toFixed(2);

    itemSearch.value='';
    priceInput.value='';
}

function removeItem(btn,val){
    total -= val;
    btn.closest('tr').remove();
    totalEl.textContent = total.toFixed(2);
}
</script>

</body>
</html>
