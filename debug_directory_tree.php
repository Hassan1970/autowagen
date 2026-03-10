<?php
/**
 * Autowagen Master - Directory Tree Dumper
 * READ ONLY – does NOT modify anything
 */

$root = realpath(__DIR__); // autowagen_master_clean
$outputFile = $root . DIRECTORY_SEPARATOR . 'autowagen_master_clean_directory_map.txt';

$lines = [];

function scanDirRecursive($dir, $level = 0)
{
    global $lines;

    $indent = str_repeat('│   ', $level);
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($fullPath)) {
            $lines[] = $indent . '📁 ' . $item . '/';
            scanDirRecursive($fullPath, $level + 1);
        } else {
            $lines[] = $indent . '📄 ' . $item;
        }
    }
}

// Run scan
$lines[] = "ROOT: " . $root;
$lines[] = str_repeat('=', 60);

scanDirRecursive($root);

// Save to TXT
file_put_contents($outputFile, implode(PHP_EOL, $lines));

// Output to browser
header('Content-Type: text/plain');
echo implode(PHP_EOL, $lines);

echo PHP_EOL . PHP_EOL;
echo "✔ Directory tree saved to:" . PHP_EOL;
echo $outputFile;
