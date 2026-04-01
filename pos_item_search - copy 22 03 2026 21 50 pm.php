<?php
require_once __DIR__ . "/config/config.php";

/* ================= AJAX SEARCH ================= */
if(isset($_GET['q'])){

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

    /* STRIP */
    $stmt = $conn->prepare("
    SELECT vsp.id, vsp.part_name AS name, vsp.stock_code AS vehicle_stock_code,
    vsp.stock_code AS code, IFNULL(vsp.qty,1) AS qty, 0 AS price,
    v.make, v.model, v.year, 'STRIP' AS source
    FROM vehicle_stripped_parts vsp
    LEFT JOIN vehicles v ON v.id = vsp.vehicle_id
    WHERE vsp.part_name LIKE ? OR vsp.stock_code LIKE ?
    LIMIT 25
    ");
    $stmt->bind_param("ss",$search,$search);
    $stmt->execute();
    $res=$stmt->get_result();
    while($r=$res->fetch_assoc()){ pushRow($results,$r,1); }
    $stmt->close();

    /* TP */
    $stmt = $conn->prepare("
    SELECT id, description AS name, CONCAT('TP-',id) AS code,
    '' AS vehicle_stock_code, 1 AS qty, selling_price AS price,
    '' AS make, '' AS model, '' AS year, 'TP' AS source
    FROM third_party_parts
    WHERE stock_status='IN_STOCK'
    AND (description LIKE ? OR CONCAT('TP-',id) LIKE ?)
    LIMIT 25
    ");
    $stmt->bind_param("ss",$search,$search);
    $stmt->execute();
    $res=$stmt->get_result();
    while($r=$res->fetch_assoc()){ pushRow($results,$r,2); }
    $stmt->close();

    /* OEM */
    $stmt = $conn->prepare("
    SELECT id, part_name AS name, CONCAT('OEM-',id) AS code,
    '' AS vehicle_stock_code, stock_qty AS qty, selling_price AS price,
    '' AS make, '' AS model, '' AS year, 'OEM' AS source
    FROM oem_parts
    WHERE stock_qty>0 AND (part_name LIKE ? OR CONCAT('OEM-',id) LIKE ?)
    LIMIT 25
    ");
    $stmt->bind_param("ss",$search,$search);
    $stmt->execute();
    $res=$stmt->get_result();
    while($r=$res->fetch_assoc()){ pushRow($results,$r,3); }
    $stmt->close();

    /* NEW */
    $stmt = $conn->prepare("
    SELECT id, part_name AS name, CONCAT('NEW-',id) AS code,
    '' AS vehicle_stock_code, stock_qty AS qty, selling_price AS price,
    '' AS make, '' AS model, '' AS year, 'NEW' AS source
    FROM replacement_parts
    WHERE stock_qty>0 AND (part_name LIKE ? OR CONCAT('NEW-',id) LIKE ?)
    LIMIT 25
    ");
    $stmt->bind_param("ss",$search,$search);
    $stmt->execute();
    $res=$stmt->get_result();
    while($r=$res->fetch_assoc()){ pushRow($results,$r,4); }
    $stmt->close();

    /* SORT */
    usort($results,function($a,$b){
        if($a['priority']!=$b['priority']) return $a['priority'] <=> $b['priority'];
        return $b['qty'] <=> $a['qty'];
    });

    echo json_encode(array_slice($results,0,40));
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Search Item</title>

<style>
body{
    background:#000;
    color:#fff;
    font-family:Arial;
    padding:15px;
}

/* SEARCH BOX */
input{
    width:100%;
    padding:12px;
    background:#111;
    border:1px solid #333;
    color:#fff;
    margin-bottom:10px;
    font-size:16px;
}

/* RESULTS */
#results{
    max-height:500px;
    overflow-y:auto;
    border:1px solid #333;
}

/* ROW */
.search-item{
    padding:6px;
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
            <div class="search-item" onclick='selectItem(${JSON.stringify(it)})'>

                <div style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    padding:8px;
                    border-radius:6px;
                ">

                    <!-- LEFT -->
                    <div>
                        <div style="font-weight:bold;">
                            <span class="SRC_${it.source}">[${it.source}]</span>
                            ${it.name}
                        </div>

                        <div style="font-size:12px;color:#888;margin-top:3px;">
                            ${it.vehicle_name || ''} 
                            ${it.vehicle_name ? ' | ' : ''}
                            Vehicle Code: ${it.vehicle_stock_code || it.code || '-'}
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div style="text-align:right;min-width:90px;">
                        <div style="
                            background:#111;
                            border-radius:12px;
                            padding:2px 10px;
                            font-size:12px;
                            display:inline-block;
                            margin-bottom:4px;
                        ">
                            ${it.qty || 0}
                        </div>

                        <div style="
                            font-weight:bold;
                            color:#ff9933;
                        ">
                            R ${(it.price || 0).toFixed(2)}
                        </div>
                    </div>

                </div>

            </div>
            `;
        });

    });
});

/* SEND BACK TO POS */
function selectItem(it){
    window.opener.postMessage({
        type:"ITEM_SELECTED",
        item:it
    },"*");
    window.close();
}

</script>

</body>
</html>