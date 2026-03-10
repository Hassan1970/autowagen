<?php
/**
 * Recursive directory listing
 * Place this file in: autowagen_master_clean/debug_dirs.php
 * Then open: http://localhost/autowagen_master_clean/debug_dirs.php
 */

$root = __DIR__;

function listDir($dir, $level = 0)
{
    $indent = str_repeat("&nbsp;&nbsp;&nbsp;", $level);

    $files = scandir($dir);
    if ($files === false) {
        return;
    }

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

        if (is_dir($fullPath)) {
            echo "{$indent}📁 <strong>{$file}</strong><br>";
            listDir($fullPath, $level + 1);
        } else {
            echo "{$indent}📄 {$file}<br>";
        }
    }
}

echo "<h2>Directory Tree for:</h2>";
echo "<strong>{$root}</strong><br><br>";

listDir($root);
