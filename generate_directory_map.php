<?php
/**
 * Autowagen Master - Directory Map Generator
 * Scans all folders/files and saves to a TXT file
 */

$rootPath = __DIR__; // autowagen_master_clean
$outputFile = __DIR__ . '/autowagen_master_clean_directory_map.txt';

$lines = [];

function scanDirRecursive($path, $prefix = '')
{
    global $lines;

    $items = scandir($path);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $item;

        if (is_dir($fullPath)) {
            $lines[] = $prefix . '[DIR]  ' . $item;
            scanDirRecursive($fullPath, $prefix . '    ');
        } else {
            $lines[] = $prefix . '[FILE] ' . $item;
        }
    }
}

// Header
$lines[] = "AUTOWAGEN MASTER CLEAN - DIRECTORY MAP";
$lines[] = "Generated on: " . date('Y-m-d H:i:s');
$lines[] = str_repeat('=', 60);
$lines[] = "";

// Scan
scanDirRecursive($rootPath);

// Save to TXT
file_put_contents($outputFile, implode(PHP_EOL, $lines));

// Output confirmation
echo "<pre>";
echo "Directory map generated successfully!\n\n";
echo "Saved to:\n";
echo $outputFile;
echo "</pre>";
