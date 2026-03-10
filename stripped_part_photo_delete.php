<?php
require_once __DIR__ . "/config/config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$part_id    = isset($_GET['part_id']) ? (int)$_GET['part_id'] : 0;
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
$file       = isset($_GET['file']) ? $_GET['file'] : '';

if ($part_id <= 0 || !$file) {
    die("Invalid request.");
}

// Prevent directory traversal
$baseName = basename($file);

$baseDir  = __DIR__ . "/uploads/stripped_parts/" . $part_id;
$mainDir  = $baseDir . "/main";
$thumbDir = $baseDir . "/thumbs";

$mainFile  = $mainDir  . "/" . $baseName;
$thumbFile = $thumbDir . "/" . $baseName;

if (is_file($mainFile))  @unlink($mainFile);
if (is_file($thumbFile)) @unlink($thumbFile);

header("Location: stripped_part_photos.php?part_id=" . $part_id . "&vehicle_id=" . $vehicle_id);
exit;
