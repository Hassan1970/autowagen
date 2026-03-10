<?php
/**
 * Dump directory tree of autowagen_master_clean
 * Outputs to browser AND saves to a TXT file
 */

$rootDir = __DIR__; // autowagen_master_clean
$outputFile = $rootDir . '/autowagen_master_clean_directory_map.txt';

$lines = [];

/**
 * Recursive directory scan
 */
function scanDirRecursive($dir, $prefix = '')
{
    global $lines;

    $items = scandir($dir);
    if ($items === false) return;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($fullPath)) {
            $lines[] = $prefix . "[DIR]  " . $item;
            scanDirRecursive($fullPath, $prefix . "    ");
        } else {
            $lines[] = $prefix . "[FILE] " . $item;
        }
    }
}

// Run scan
$lines[] = "Directory tree for: " . $rootDir;
$lines[] = str_repeat("=", 80);

scanDirRecursive($rootDir);

// Save to TXT
file_put_contents($outputFile, implode(PHP_EOL, $lines));

// Output to browser
header("Content-Type: text/plain");
echo implode(PHP_EOL, $lines);
echo PHP_EOL . PHP_EOL;
echo "Saved to: " . $outputFile;
