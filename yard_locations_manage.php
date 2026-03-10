<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

/* ================================
ADD LOCATION
================================ */

if(isset($_POST['add_location'])){

$code = trim($_POST['code']);
$description = trim($_POST['description']);

$stmt = $conn->prepare("
INSERT INTO yard_locations (code,description)
VALUES (?,?)
");

$stmt->bind_param("ss",$code,$description);
$stmt->execute();

header("Location: yard_locations_manage.php");
exit;
}

/* ================================
DELETE LOCATION
================================ */

if(isset($_GET['delete'])){

$id = (int)$_GET['delete'];

$stmt = $conn->prepare("DELETE FROM yard_locations WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: yard_locations_manage.php");
exit;
}

/* ================================
FETCH LOCATIONS
================================ */

$sql="SELECT * FROM yard_locations ORDER BY code ASC";
$result=$conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Yard Locations</title>

<style>

.wrap{
width:95%;
margin:25px auto;
}

h2{
color:#ff3333;
margin-bottom:20px;
}

table{
width:100%;
border-collapse:collapse;
}

th,td{
border:1px solid #333;
padding:10px;
}

th{
background:#111;
color:#ff3333;
}

tr:nth-child(even){
background:#0b0b0b;
}

input{
padding:6px;
background:#111;
border:1px solid #333;
color:#fff;
}

button{
padding:8px 12px;
background:#b00000;
border:none;
color:#fff;
cursor:pointer;
}

.delete{
background:#600;
}

.formbox{
margin-bottom:20px;
border:1px solid #333;
padding:15px;
background:#0b0b0b;
}

</style>

</head>

<body>

<div class="wrap">

<h2>Manage Yard Locations</h2>

<div class="formbox">

<form method="POST">

<label>Location Code</label>
<br>
<input type="text" name="code" required placeholder="Example: A1">

<br><br>

<label>Description</label>
<br>
<input type="text" name="description" placeholder="Example: Engine Rack">

<br><br>

<button type="submit" name="add_location">Add Location</button>

</form>

</div>

<table>

<tr>
<th>ID</th>
<th>Code</th>
<th>Description</th>
<th>Action</th>
</tr>

<?php if($result->num_rows>0): ?>

<?php while($row=$result->fetch_assoc()): ?>

<tr>

<td><?= $row['id'] ?></td>

<td><?= htmlspecialchars($row['code']) ?></td>

<td><?= htmlspecialchars($row['description']) ?></td>

<td>

<a href="?delete=<?= $row['id'] ?>" 
onclick="return confirm('Delete this location?')">

<button class="delete">Delete</button>

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="4">No locations created</td>
</tr>

<?php endif; ?>

</table>

</div>

</body>
</html>