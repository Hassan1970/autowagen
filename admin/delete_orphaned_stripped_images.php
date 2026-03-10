<?php
require_once __DIR__ . "/../config/config.php";

/*
 STEP 5 — PHASE 2
 Delete orphaned stripped-part images
 SAFE: DB is rechecked before deletion
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
$deleted = [];
$skipped = [];

$dirIterator = new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($dirIterator);

foreach ($iterator as $file) {

    if (!$file->isFile()) {
        continue;
    }

    $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, "", $file->getPathname());
    $relativePath = str_replace("\\", "/", $relativePath);

    /* ================= DOUBLE SAFETY CHECK ================= */
    if (isset($dbFiles[$relativePath])) {
        $skipped[] = $relativePath;
        continue;
    }

    /* ================= DELETE ================= */
    if (@unlink($file->getPathname())) {
        $deleted[] = $relativePath;
    } else {
        $skipped[] = $relativePath;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Delete Orphaned Stripped Images</title>
<style>
body {
    background:#000;
    color:#fff;
    font-family:Arial;
    padding:20px;
}
h1 { color:#ff3333; }
h2 { margin-top:30px; }
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
    border:1px solid #b00000;
    padding:8px;
    font-size:14px;
}
th { color:#ff3333; }
.ok { color:#00cc66; }
.warn { color:#ffcc00; }
.note {
    background:#111;
    border:1px solid #b00000;
    padding:12px;
    margin-bottom:20px;
}
</style>
</head>

<body>

<h1>STEP 5 — Orphaned Image Cleanup (Completed)</h1>

<div class="note">
✔ Only orphaned images were deleted.<br>
✔ Database-linked images were preserved.<br>
✔ This script can now be archived.
</div>

<h2 class="ok">Deleted Files</h2>
<table>
<tr><th>File</th></tr>
<?php if ($deleted): ?>
    <?php foreach ($deleted as $file): ?>
        <tr><td><?= htmlspecialchars($file) ?></td></tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td><em>No orphaned files found to delete.</em></td></tr>
<?php endif; ?>
</table>

<h2 class="warn">Skipped Files (Protected)</h2>
<table>
<tr><th>File</th></tr>
<?php if ($skipped): ?>
    <?php foreach ($skipped as $file): ?>
        <tr><td><?= htmlspecialchars($file) ?></td></tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td><em>None</em></td></tr>
<?php endif; ?>
</table>

</body>
</html>
