<?php
require_once __DIR__ . "/config/config.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Third Party Part</title>

<style>
body{
background:#000;
color:#fff;
font-family:Arial;
}

.container{
width:95%;
margin:20px auto;
}

h2{
color:#33cc33;
}

table{
width:100%;
border-collapse:collapse;
margin-top:15px;
}

th,td{
border:1px solid #333;
padding:10px;
}

th{
background:#111;
color:#33cc33;
}

tr:hover{
background:#1a1a1a;
cursor:pointer;
}

.price{
color:#00ff88;
font-weight:bold;
}
</style>

<script>
function selectPart(id, name, price)
{
window.opener.postMessage({
type:"THIRD_PARTY_SELECTED",
part_id:id,
name:name,
price:price
},"*");

window.close();
}
</script>

</head>
<body>

<div class="container">

<h2>Select Third Party Part</h2>

<table>

<tr>
<th>Description</th>
<th>Price</th>
</tr>

<?php

$sql = "
SELECT id, description, selling_price
FROM third_party_parts
ORDER BY description ASC
";

$result = $conn->query($sql);

while($row = $result->fetch_assoc())
{
?>

<tr onclick="selectPart(
<?= $row['id'] ?>,
'<?= addslashes($row['description']) ?>',
<?= $row['selling_price'] ?>
)">
<td><?= htmlspecialchars($row['description']) ?></td>
<td class="price">R <?= number_format($row['selling_price'],2) ?></td>
</tr>

<?php
}
?>

</table>

</div>

</body>
</html>