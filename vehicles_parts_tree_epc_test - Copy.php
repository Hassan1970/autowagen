<?php
require_once __DIR__ . "/config/config.php";
include __DIR__ . "/includes/header.php";

$sql="
SELECT stock_code
FROM vehicles
ORDER BY stock_code
";

$res=$conn->query($sql);
?>

<style>

.wrap{
width:95%;
margin:20px auto;
display:flex;
gap:20px;
}

.tree{
width:50%;
background:#0f0f0f;
border:1px solid #333;
padding:10px;
height:calc(100vh - 120px);
overflow:auto;
white-space:nowrap;
}

.tree h3{
color:#ff3333;
margin-bottom:10px;
}

.node{
padding:8px;
border-bottom:1px solid #222;
cursor:pointer;
}

.node:hover{
background:#1a1a1a;
}

.tree-header{
cursor:pointer;
padding:6px;
border-bottom:1px solid #222;
font-weight:bold;
color:#ff4444;
}

.tree-header:hover{
background:#1a1a1a;
}

.tree-children{
display:none;
margin-left:15px;
}

.parts-panel{
width:50%;
background:#0f0f0f;
border:1px solid #333;
padding:15px;
height:calc(100vh - 120px);
overflow:auto;
}

.parts-table{
width:100%;
border-collapse:collapse;
}

.parts-table th,
.parts-table td{
border-bottom:1px solid #222;
padding:8px;
text-align:left;
}

.parts-table th{
color:#ff3333;
}

.vehicle-select select{
width:100%;
padding:8px;
background:#111;
color:#fff;
border:1px solid #333;
}

#epcSearch{
width:100%;
padding:8px;
background:#111;
border:1px solid #333;
color:#fff;
margin-top:10px;
}

</style>

<div class="wrap">

<div class="tree">

<h3>EPC Parts Tree</h3>

<div class="vehicle-select">

<select id="vehicleSelect">

<option value="">Select Vehicle</option>

<?php
while($row=$res->fetch_assoc()){
echo "<option value='".$row['stock_code']."'>".$row['stock_code']."</option>";
}
?>

</select>

</div>

<input
type="text"
id="epcSearch"
placeholder="Search parts (hinge, pump, mirror...)">

<br><br>

<div id="epcTree">
Select vehicle to load parts
</div>

</div>

<div class="parts-panel">

<h3>Vehicle Parts</h3>

<div id="partsResults">
Select a part to view details
</div>

</div>

</div>

<script>

/* LOAD VEHICLE */

document.getElementById("vehicleSelect").addEventListener("change",function(){

let stock=this.value;

if(!stock)return;

fetch("vehicle_parts_epc_api_test.php?stock_code="+stock+"&level=category")

.then(res=>res.text())

.then(data=>{
document.getElementById("epcTree").innerHTML=data;
});

});


/* LOAD PART DETAILS */

function loadParts(part,stock){

fetch("vehicle_parts_epc_api_test.php?part_name="+encodeURIComponent(part)+"&stock_code="+stock)

.then(res=>res.text())

.then(data=>{
document.getElementById("partsResults").innerHTML=data;
});

}


/* SEARCH */

document.getElementById("epcSearch").addEventListener("keyup",function(){

let search=this.value.trim();
let stock=document.getElementById("vehicleSelect").value;

if(search.length<2)return;

fetch("vehicle_parts_epc_api_test.php?search="+encodeURIComponent(search)+"&stock_code="+stock)

.then(res=>res.text())

.then(data=>{
document.getElementById("epcTree").innerHTML=data;
});

});


/* LOAD TREE LEVELS */

function loadLevel(level,parent,id){

let stock=document.getElementById("vehicleSelect").value;

let target=document.getElementById(id);

if(target.dataset.loaded){
toggleTree(id);
return;
}

fetch("vehicle_parts_epc_api_test.php?stock_code="+stock+"&level="+level+"&parent="+encodeURIComponent(parent))

.then(res=>res.text())

.then(data=>{

target.innerHTML=data;

target.dataset.loaded=true;

target.style.display="block";

});

}


/* TOGGLE TREE */

function toggleTree(id){

let el=document.getElementById(id);

if(el.style.display==="none" || el.style.display===""){
el.style.display="block";
}else{
el.style.display="none";
}

}

</script>

<?php
include __DIR__ . "/includes/footer.php";
?>