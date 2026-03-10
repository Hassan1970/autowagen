<?php
require_once __DIR__ . "/../config/config.php";

/*
 STEP 5 — PHASE 1
 Scan for orphaned stripped-part images
 NOTHING is deleted in this script
*/

$baseDir = realpath(__DIR__ . "/../uploads/stripped_parts");

if (!$baseDir || !is_dir($baseDir)) {
    die("Uploads directory not found.");
}

/* ================= GET DB FILES ================= */
$dbFiles = [];

$result = $conn->query("
    SELECT part_id, file_name
    FROM stripped_part_images
");

while ($row = $result->fetch_assoc()) {
    $path = $row['part_id'] . "/" . $row['file_name'];
    $dbFiles[$path] = true;
}

/* ================= SCAN DISK FILES ================= */
$diskFiles = [];

$dirIterator = new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($dirIterator);

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, "", $file->getPathname());
        $relativePath = str_replace("\\", "/", $relativePath); // windows safety
        $diskFiles[$relativePath] = true;
    }
}

/* ================= COMPARE ================= */
$orphaned = array_diff_key($diskFiles, $dbFiles);
$used     = array_intersect_key($diskFiles, $dbFiles);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Scan Orphaned Stripped Images</title>
<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial;
    padding:20px;
}
h1 { color:#ff3333; }
h2 { color:#ff6666; margin-top:30px; }
table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
th, td {
    border:1px solid #b00000;
    padding:8px;
    font-size:14px;
}
th {
    color:#ff3333;
}
.ok { color:#00cc66; }
.warn { color:#ffcc00; }
.note {
    background:#111;
    border:1px solid #b00000;
    padding:10px;
    margin-top:20px;
}
</style>
</head>

<body>

<h1>STEP 5 — Orphaned Image Scan (Safe)</h1>

<div class="note">
✔ This page only <strong>scans</strong> files.<br>
❌ No images are deleted.<br>
⚠️ Review results carefully before deletion.
</div>

<h2 class="ok">Used Images (Linked in Database)</h2>
<table>
<tr><th>File</th></tr>
<?php if ($used): ?>
    <?php foreach ($used as $file => $_): ?>
        <tr><td><?= htmlspecialchars($file) ?></td></tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td><em>None found</em></td></tr>
<?php endif; ?>
</table>

<h2 class="warn">Orphaned Images (NOT in Database)</h2>
<table>
<tr><th>File</th></tr>
<?php if ($orphaned): ?>
    <?php foreach ($orphaned as $file => $_): ?>
        <tr><td><?= htmlspecialchars($file) ?></td></tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td><em>No orphaned images found 🎉</em></td></tr>
<?php endif; ?>
</table>

</body>
</html>
