<?php

function scanDirRecursive($dir){
    $files = scandir($dir);

    foreach($files as $file){

        if($file == "." || $file == "..") continue;

        $fullPath = $dir . "/" . $file;

        echo $fullPath . "<br>";

        if(is_dir($fullPath)){
            scanDirRecursive($fullPath);
        }
    }
}

$root = __DIR__;
scanDirRecursive($root);

?>