<?php
// Print all PHP files in current directory
$files = glob('*.php');

foreach ($files as $file) {
    if ($file === __FILE__) continue; // Skip this script itself
    
    echo str_repeat("=", 60) . "\n";
    echo "FILE: " . $file . "\n";
    echo str_repeat("=", 60) . "\n\n";
    
    $content = file_get_contents($file);
    echo $content . "\n\n";
}

// For recursive scanning (including subdirectories)
function printAllPhpFiles($dir = '.') {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            echo str_repeat("=", 80) . "\n";
            echo "FILE: " . $file->getPathname() . "\n";
            echo str_repeat("=", 80) . "\n\n";
            
            echo file_get_contents($file->getPathname()) . "\n\n";
        }
    }
}

// Uncomment to use recursive version
// printAllPhpFiles();
?>