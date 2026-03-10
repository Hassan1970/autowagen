<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Customer</title>

<style>
body{font-family:Arial;background:#111;color:#fff;padding:20px}
input,textarea{
width:100%;
padding:10px;
margin-top:5px;
margin-bottom:12px;
background:#000;
border:1px solid #444;
color:#fff;
}
button{
padding:12px;
background:#1e7e34;
border:none;
color:white;
width:100%;
cursor:pointer;
font-size:16px;
}
</style>
</head>
<body>

<h2>Add New Customer</h2>

<form id="form">

<label>Full Name *</label>
<input type="text" id="name" required>

<label>Phone</label>
<input type="text" id="phone">

<label>Address</label>
<textarea id="address"></textarea>

<button type="submit">Save Customer</button>

</form>

<script>

document.getElementById("form").onsubmit = async function(e){
e.preventDefault();

const data = new URLSearchParams({
name: name.value,
phone: phone.value,
address: address.value
});

const res = await fetch("pos_customer_quick_add.php",{
method:"POST",
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:data
});

const json = await res.json();

if(json.status==="ok"){

window.opener.postMessage({
id:json.id,
name:json.name,
phone:json.phone,
address:json.address
},"*");

window.close();
}
else{
alert(json.msg || "Error saving");
}
};
</script>

</body>
</html>