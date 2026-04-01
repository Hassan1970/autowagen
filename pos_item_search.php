<?php
require_once __DIR__ . "/config/config.php";

/* ================= AJAX MODE TEST ================= */
if (isset($_GET['q'])) {

    header('Content-Type: application/json');

    $q = trim($_GET['q']);
    if ($q === '') {
        echo json_encode([]);
        exit;
    }

    $search = "%$q%";
    $results = [];

    function pushRow(&$results, $row, $priority)
    {
        $row['priority'] = $priority;

        $vehicleParts = array_filter([
            $row['make'] ?? '',
            $row['model'] ?? '',
            $row['year'] ?? ''
        ]);

        $row['vehicle_name'] = implode(' ', $vehicleParts);

        if (!isset($row['vehicle_stock_code'])) {
            $row['vehicle_stock_code'] = $row['code'] ?? '';
        }

        $results[] = $row;
    }

    /* STRIPPED */
    $stmt = $conn->prepare("
    SELECT
    vsp.id,
    vsp.part_name AS name,
    vsp.stock_code AS vehicle_stock_code,
    vsp.stock_code AS code,
    IFNULL(vsp.qty,1) AS qty,
    0 AS price,
    v.make,
    v.model,
    v.year,
    'STRIP' AS source
    FROM vehicle_stripped_parts vsp
    LEFT JOIN vehicles v ON v.id = vsp.vehicle_id
    WHERE vsp.part_name LIKE ?
    ");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) pushRow($results,$r,1);
    $stmt->close();

    /* OEM */
    $stmt = $conn->prepare("
    SELECT
    id,
    part_name AS name,
    CONCAT('OEM-',id) AS code,
    '' AS vehicle_stock_code,
    stock_qty AS qty,
    selling_price AS price,
    '' AS make,
    '' AS model,
    '' AS year,
    'OEM' AS source
    FROM oem_parts
    WHERE stock_qty > 0 AND part_name LIKE ?
    ");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) pushRow($results,$r,2);
    $stmt->close();

    /* TP */
    $stmt = $conn->prepare("
    SELECT
    id,
    description AS name,
    CONCAT('TP-',id) AS code,
    '' AS vehicle_stock_code,
    1 AS qty,
    selling_price AS price,
    '' AS make,
    '' AS model,
    '' AS year,
    'TP' AS source
    FROM third_party_parts
    WHERE stock_status='IN_STOCK' AND description LIKE ?
    ");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) pushRow($results,$r,3);
    $stmt->close();

    /* NEW */
    $stmt = $conn->prepare("
    SELECT
    id,
    part_name AS name,
    CONCAT('NEW-',id) AS code,
    '' AS vehicle_stock_code,
    stock_qty AS qty,
    selling_price AS price,
    '' AS make,
    '' AS model,
    '' AS year,
    'NEW' AS source
    FROM replacement_parts
    WHERE stock_qty > 0 AND part_name LIKE ?
    ");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) pushRow($results,$r,4);
    $stmt->close();

    usort($results,function($a,$b){
        if($a['priority']!=$b['priority']) return $a['priority'] <=> $b['priority'];
        return $b['qty'] <=> $a['qty'];
    });

    echo json_encode(array_slice($results,0,50));
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Search Item</title>

<style>
body{background:#000;color:#fff;font-family:Arial;padding:15px;}

input{
width:100%;
padding:12px;
background:#111;
border:1px solid #333;
color:#fff;
margin-bottom:10px;
}

#results{
max-height:500px;
overflow-y:auto;
border:1px solid #333;
}

.search-item{
padding:10px;
border-bottom:1px solid #222;
cursor:pointer;
}

.search-item:hover{
background:#1a1a1a;
}

/* COLORS */
.SRC_STRIP{color:#ff9933;}
.SRC_TP{color:#33cc33;}
.SRC_OEM{color:#4da6ff;}
.SRC_NEW{color:#cc66ff;}
</style>
</head>

<body>

<h3>Search Item</h3>
<input type="text" id="search" placeholder="Type item name...">
<div id="results"></div>

<script>
let search = document.getElementById("search");
let results = document.getElementById("results");

search.addEventListener("keyup", function(){

let q = this.value;

if(q.length < 2){
results.innerHTML = "";
return;
}

fetch("pos_item_search.php?q="+encodeURIComponent(q))
.then(r=>r.json())
.then(rows=>{

results.innerHTML="";

rows.forEach(it=>{
results.innerHTML += `
<div class="search-item SRC_${it.source}" onclick='selectItem(${JSON.stringify(it)})'>

<div style="display:flex;justify-content:space-between;align-items:center">

    <div>
        <b>[${it.source}] ${it.name}</b><br>
        <small style="color:#888">
            ${it.vehicle_name || ''} 
            ${it.vehicle_stock_code ? '| Code: '+it.vehicle_stock_code : ''}
        </small>
    </div>

    <div style="text-align:right">

        <div style="
            background:#111;
            border-radius:50%;
            width:26px;
            height:26px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            margin-bottom:5px;
            font-size:12px;
        ">
            ${it.qty}
        </div>

        <div style="color:#ff9933">
            R ${parseFloat(it.price || 0).toFixed(2)}
        </div>

    </div>

</div>

</div>
`;
});

});
});

function selectItem(it){
window.opener.postMessage({type:"ITEM_SELECTED",item:it},"*");
window.close();
}
</script>

</body>
</html>