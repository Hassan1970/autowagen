<?php
require_once __DIR__ . '/config/config.php';
include __DIR__ . '/includes/header.php';

$prefill_part = $_GET['part'] ?? '';

$invoice_number = 'INV-' . date('Ymd-His');
$invoice_date   = date('Y-m-d');
?>

<!DOCTYPE html>
<html>
<head>
<title>POS Invoice – Add</title>
</head>

<body>

<h2>POS Invoice – Add</h2>

<form method="POST" action="pos_invoice_add_save.php">

<input type="hidden" name="items_json" id="items_json">
<input type="hidden" name="grand_total" id="grand_total_input">

<table>
<tbody id="items_body"></tbody>
</table>

<button type="submit">SAVE INVOICE</button>

</form>

<script>

let CART=[];

function addItem(){

CART.push({
id:1,
name:"Test",
qty:1,
price:10
});

document.getElementById("items_body").innerHTML+=`
<tr>
<td>Test</td>
<td>1</td>
<td>10</td>
<td><button type="button" onclick="removeRow(this)">X</button></td>
</tr>`;

updateJSON();

}

function removeRow(btn){

    let row = btn.closest("tr");
    let index = Array.from(row.parentNode.children).indexOf(row);

    CART.splice(index, 1);

    row.remove();

    updateJSON();
}

function updateJSON(){
document.getElementById("items_json").value=JSON.stringify(CART);
}

function clearInvoice(){

document.getElementById("items_body").innerHTML="";

CART=[];

updateJSON();
}

</script>

</body>
</html>