<?php

$baseDir = __DIR__;
$outputFile = __DIR__ . "/all_php_files12032026.txt";

$foldersToScan = [
    $baseDir,
    $baseDir . "/ajax",
    $baseDir . "/includes",
];

$extensions = ['php']; // Add: 'js','css' if needed

$fileList = [];

/* RECURSIVELY SCAN SELECTED FOLDERS */
foreach ($foldersToScan as $folder) {

    if (!is_dir($folder)) continue;

    $dir = new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS);
    $iter = new RecursiveIteratorIterator($dir);

    foreach ($iter as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $extensions)) {
            $fileList[] = $file->getPathname();
        }
    }
}

/* WRITE OUTPUT FILE */
$fp = fopen($outputFile, "w");

foreach ($fileList as $filePath) {
    fwrite($fp, "\n====================================\n");
    fwrite($fp, "FILE: " . str_replace($baseDir . "\\", "", $filePath) . "\n");
    fwrite($fp, "====================================\n\n");

    $content = file_get_contents($filePath);
    fwrite($fp, $content . "\n\n");
}

fclose($fp);

echo "<h2>Export Completed!</h2>";
echo "<p>Saved to <strong>ALL_PHP_FILES.txt</strong></p>";
echo "<p>Full path: $outputFile</p>";
?>
