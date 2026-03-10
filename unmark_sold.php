<?php
require_once __DIR__ . "/config/config.php";

$inv_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($inv_id <= 0) { die("Invalid ID."); }

// RESET SOLD STATUS
$up = $conn->prepare("
    UPDATE stripped_inventory
    SET sold_status = 'AVAILABLE',
        sold_date = NULL,
        sold_notes = NULL
    WHERE id = ?
");
$up->bind_param("i", $inv_id);
$up->execute();
$up->close();

// REMOVE HISTORY
$del = $conn->prepare("DELETE FROM sold_inventory WHERE inventory_id = ?");
$del->bind_param("i", $inv_id);
$del->execute();
$del->close();

header("Location: stripped_inventory_list.php?undo=1");
exit;
?>
