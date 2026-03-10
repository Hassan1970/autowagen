<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// --------------------------------------------------
// GET INVENTORY ID
// --------------------------------------------------
$inv_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($inv_id <= 0) {
    die("<h2 style='color:red; text-align:center; margin-top:40px;'>Invalid inventory ID.</h2>");
}

// --------------------------------------------------
// LOAD INVENTORY
// --------------------------------------------------
$stmt = $conn->prepare("
    SELECT si.*, v.stock_code, v.make, v.model, v.year
    FROM stripped_inventory si
    LEFT JOIN vehicles v ON si.vehicle_id = v.id
    WHERE si.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $inv_id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$inv) {
    die("<h2 style='color:red; text-align:center; margin-top:40px;'>Inventory item not found.</h2>");
}

$barcodeText = "INV-" . $inv_id;

// --------------------------------------------------
// BARCODE GENERATION (No external library)
// --------------------------------------------------
function code128Bar($text) {
    $codes = [
        ' '=>0,'!'=>1,'"'=>2,'#'=>3,'$'=>4,'%'=>5,'&'=>6,"'"=>7,'('=>8,')'=>9,'*'=>10,'+'=>11,','=>12,'-'=>13,'.'=>14,'/'=>15,
        '0'=>16,'1'=>17,'2'=>18,'3'=>19,'4'=>20,'5'=>21,'6'=>22,'7'=>23,'8'=>24,'9'=>25,
        ':'=>26,';'=>27,'<'=>28,'='=>29,'>'=>30,'?'=>31,'@'=>32,
        'A'=>33,'B'=>34,'C'=>35,'D'=>36,'E'=>37,'F'=>38,'G'=>39,'H'=>40,'I'=>41,'J'=>42,
        'K'=>43,'L'=>44,'M'=>45,'N'=>46,'O'=>47,'P'=>48,'Q'=>49,'R'=>50,'S'=>51,'T'=>52,
        'U'=>53,'V'=>54,'W'=>55,'X'=>56,'Y'=>57,'Z'=>58,
        '['=>59,'\\'=>60,']'=>61,'^'=>62,'_'=>63,'`'=>64,
        'a'=>65,'b'=>66,'c'=>67,'d'=>68,'e'=>69,'f'=>70,'g'=>71,'h'=>72,'i'=>73,'j'=>74,
        'k'=>75,'l'=>76,'m'=>77,'n'=>78,'o'=>79,'p'=>80,'q'=>81,'r'=>82,'s'=>83,'t'=>84,
        'u'=>85,'v'=>86,'w'=>87,'x'=>88,'y'=>89,'z'=>90,
        '{'=>91,'|'=>92,'}'=>93,'~'=>94,'DEL'=>95
    ];

    $patterns = [
        "11011001100","11001101100","11001100110","10010011000",
        "10010001100","10001001100","10011001000","10011000100",
        "10001100100","11001001000","11001000100","11000100100",
        "10110011100","10011011100","10011001110","10111001100",
        "10011101100","10011100110","11001110010","11001011100",
        "11001001110","11011100100","11001110100","11101101110",
        "11101001100","11100101100","11100100110","11101100100",
        "11100110100","11100110010","11011011000","11011000110",
        "11000110110","10100011000","10001011000","10001000110",
        "10110001000","10001101000","10001100010","11010001000",
        "11000101000","11000100010","10110111000","10110001110",
        "10001101110","10111011000","10111000110","10001110110",
        "11101110110","11010001110","11000101110","11011101000",
        "11011100010","11011101110","11101011000","11101000110",
        "11100010110","11101101000","11101100010","11100011010",
        "11101111010","11001000010","11110001010","10100110000",
        "10100001100","10010110000","10010000110","10000101100",
        "10000100110","10110010000","10110000100","10011010000",
        "10011000010","10000110100","10000110010","11000010010",
        "11001010000","11110111010","11000010100","10001111010",
        "10100111100","10010111100","10010011110","10111100100",
        "10011110100","10011110010","11110100100","11110010100",
        "11110010010","11011011110","11011110110","11110110110",
        "10101111000","10100011110","10001011110","10111101000",
        "10111100010","11110101000","11110100010","10111011110",
        "10111101110","11101011110","11110101110","11010000100",
        "11010010000","11010011100","11000111010"
    ];

    $start = 104; // Start Code B
    $stop  = "1100011101011";

    $encoded = $patterns[$start];

    $checksum = $start;
    $pos = 1;

    for ($i = 0; $i < strlen($text); $i++) {
        $v = $codes[$text[$i]];
        $encoded .= $patterns[$v];
        $checksum += $v * $pos;
        $pos++;
    }

    $checksum = $checksum % 103;
    $encoded .= $patterns[$checksum];
    $encoded .= $stop;

    return $encoded;
}

$barcodePattern = code128Bar($barcodeText);
?>
<!DOCTYPE html>
<html>
<head>
<title>Barcode Label</title>
<style>
    body { background:#fff; font-family:Arial; }
    .label {
        width: 320px;
        padding: 15px;
        border: 1px solid #000;
        margin: 20px auto;
        text-align:center;
    }
    .barcode {
        margin-top:10px;
        height:60px;
        display:flex;
    }
    .bar {
        height:100%;
        display:inline-block;
    }
</style>
</head>
<body>

<div class="label">

    <strong><?= h($inv['part_name']) ?></strong><br>
    <?= h($inv['stock_code']) ?><br>
    <?= h($inv['make'] . " " . $inv['model'] . " " . $inv['year']) ?><br><br>

    <div class="barcode">
        <?php foreach (str_split($barcodePattern) as $b): ?>
            <div class="bar" style="width:2px; background:<?= $b == '1' ? '#000' : '#fff' ?>"></div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top:8px; font-size:14px;">
        <?= h($barcodeText) ?>
    </div>

    <br>
    <button onclick="window.print()" style="padding:8px 16px; font-size:14px;">
        Print Label
    </button>

</div>

</body>
</html>
