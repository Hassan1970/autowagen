<?php
require_once __DIR__ . '/config/config.php';

$message = '';

if(isset($_POST['generate'])){

$start_row = strtoupper($_POST['start_row'] ?? 'A');
$end_row   = strtoupper($_POST['end_row'] ?? 'D');
$columns   = (int)($_POST['columns'] ?? 4);

for($row = ord($start_row); $row <= ord($end_row); $row++){

$letter = chr($row);

for($i = 1; $i <= $columns; $i++){

$location = $letter.$i;

$check = $conn->prepare("SELECT id FROM yard_locations WHERE code=?");
$check->bind_param("s",$location);
$check->execute();
$check->store_result();

if($check->num_rows == 0){

$description = "Yard Location ".$location;

$insert = $conn->prepare("INSERT INTO yard_locations (code,description) VALUES (?,?)");
$insert->bind_param("ss",$location,$description);
$insert->execute();

}

}

}

$message = "Locations Generated Successfully";

}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<div style="padding:20px;">

<h2>Generate Yard Locations</h2>

<?php if($message){ ?>
<div style="color:lime;font-size:18px;margin-bottom:20px;">
<?php echo $message; ?>
</div>
<?php } ?>

<form method="post">

<p>Row Start</p>
<input type="text" name="start_row" value="A" maxlength="1">

<p>Row End</p>
<input type="text" name="end_row" value="D" maxlength="1">

<p>Columns</p>
<input type="number" name="columns" value="4">

<br><br>

<button type="submit" name="generate" style="
padding:10px 20px;
background:#cc0000;
border:none;
color:white;
cursor:pointer;
">
Generate Locations
</button>

</form>

</div>