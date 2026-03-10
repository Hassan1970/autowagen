<?php
/**
 * Deduct OEM stock and record sale movement
 *
 * @param mysqli $conn
 * @param int $part_id
 * @param int $qty
 * @param string $reference
 */
function oem_sell_part(mysqli $conn, int $part_id, int $qty, string $reference = "Sale") {

    // Fetch current stock
    $sql = "SELECT stock_qty FROM oem_parts WHERE id = ?";
    $stm = $conn->prepare($sql);
    $stm->bind_param("i", $part_id);
    $stm->execute();
    $res = $stm->get_result();
    $row = $res->fetch_assoc();
    $stm->close();

    if (!$row) {
        die("Error: OEM part not found.");
    }

    $current_qty = (int)$row['stock_qty'];

    // Prevent overselling
    if ($qty > $current_qty) {
        die("Error: Cannot sell {$qty} units. Only {$current_qty} in stock.");
    }

    // Deduct stock
    $upd = $conn->prepare("UPDATE oem_parts SET stock_qty = stock_qty - ? WHERE id = ?");
    $upd->bind_param("ii", $qty, $part_id);
    $upd->execute();
    $upd->close();

    // Add stock movement
    $mov = $conn->prepare("
        INSERT INTO oem_stock_movements
        (part_id, movement_type, qty, reference)
        VALUES (?, 'SALE', ?, ?)
    ");
    $mov->bind_param("iis", $part_id, $qty, $reference);
    $mov->execute();
    $mov->close();
}
