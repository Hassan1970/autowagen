<?php
require_once "config/config.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("DELETE FROM oem_parts WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo "<script>alert('OEM Part deleted'); window.location='oem_parts_list.php';</script>";
exit;
?>
