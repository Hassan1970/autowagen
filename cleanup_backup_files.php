<?php

/*
-------------------------------------------------------
AUTO BACKUP / CLEANUP SCRIPT
-------------------------------------------------------

This script will:

1. Look for files matching specific patterns
   Example:
   back*.php
   db_*.php
   my_b*.php
   done_on*.php
   test*.php
   etc.

2. Create a folder like:

   1_2026-03-08

   If that folder already exists, it will create:

   2_2026-03-08
   3_2026-03-08
   etc.

3. Move all matching files into that folder.

-------------------------------------------------------
*/


/* ----------------------------------------
SET BASE DIRECTORY
---------------------------------------- */

$base_dir = __DIR__; 
// __DIR__ means: current folder where this PHP file is located


/* ----------------------------------------
GET TODAY'S DATE
---------------------------------------- */

$today = date('Y-m-d'); 
// Example: 2026-03-08


/* ----------------------------------------
CREATE NUMBERED FOLDER
---------------------------------------- */

$counter = 1;

while (true) {

    $folder_name = $counter . "_" . $today;

    $target_folder = $base_dir . "/" . $folder_name;

    // if folder does NOT exist → create it
    if (!is_dir($target_folder)) {

        mkdir($target_folder);

        break;
    }

    // otherwise increase number
    $counter++;
}


/* ----------------------------------------
FILE PATTERNS TO MOVE
---------------------------------------- */

$patterns = [

    "back*.php",
    "db_*.php",
    "my_b*.php",
    "done_on*.php",
    "test*.php",

];


/* ----------------------------------------
SPECIFIC FILES
---------------------------------------- */

$specific_files = [

    "part1.php",
    "pos_invoice_add_v204032026853",
    "vehicle_stripped_entry - 03 12 2025 1230.php",
    "stripped_list.php.zip",
    "vehicle_stripping_entry -30 12 25 wiorking version.php",
    "vehicle_stripping_save - this was working 100% at 29 12 2025 10 38",
    "vehicles_list.php.5A_WORKING"

];


/* ----------------------------------------
MOVE FILES MATCHING PATTERNS
---------------------------------------- */

foreach ($patterns as $pattern) {

    $files = glob($base_dir . "/" . $pattern);

    foreach ($files as $file) {

        $filename = basename($file);

        rename($file, $target_folder . "/" . $filename);

        echo "Moved: " . $filename . "<br>";

    }
}


/* ----------------------------------------
MOVE SPECIFIC FILES
---------------------------------------- */

foreach ($specific_files as $file) {

    $full_path = $base_dir . "/" . $file;

    if (file_exists($full_path)) {

        rename($full_path, $target_folder . "/" . $file);

        echo "Moved: " . $file . "<br>";

    }
}


echo "<br><b>Backup folder created:</b> " . $folder_name;

?>