<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$inv_id = isset($_GET['inv_id']) ? (int)$_GET['inv_id'] : 0;
$file   = isset($_GET['file'])   ? $_GET['file']        : '';

if ($inv_id <= 0 || $file === '') {
    die("Invalid parameters.");
}

// Normalize filename
$filename = basename($file);
$path = __DIR__ . "/uploads/inventory/" . $inv_id . "/" . $filename;

if (is_file($path)) {
    unlink($path);
}

header("Location: inventory_photos.php?id=" . $inv_id);
exit;
