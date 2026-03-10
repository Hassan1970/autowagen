<?php
require_once __DIR__ . '/config/config.php';

/*
EXPECTED POST:
- customer_id
- document (file)
OPTIONAL:
- document_type
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request');
}

if (
    empty($_POST['customer_id']) ||
    !isset($_FILES['document']) ||
    $_FILES['document']['error'] !== UPLOAD_ERR_OK
) {
    http_response_code(400);
    exit('Missing data');
}

$customer_id = (int)$_POST['customer_id'];
$document_type = !empty($_POST['document_type'])
    ? trim($_POST['document_type'])
    : 'general';

/* UPLOAD DIRECTORY */
$uploadDir = __DIR__ . '/uploads/customer_documents/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

/* FILE INFO */
$tmpName = $_FILES['document']['tmp_name'];
$origName = basename($_FILES['document']['name']);
$ext = pathinfo($origName, PATHINFO_EXTENSION);

/* SAFE FILE NAME */
$newName = 'cust_' . $customer_id . '_' . time() . '.' . $ext;
$destPath = $uploadDir . $newName;

/* MOVE FILE */
if (!move_uploaded_file($tmpName, $destPath)) {
    http_response_code(500);
    exit('Upload failed');
}

/* STORE RELATIVE PATH */
$filePathForDb = 'uploads/customer_documents/' . $newName;

/* INSERT DATABASE ROW */
$stmt = $conn->prepare("
    INSERT INTO customer_documents
        (customer_id, document_type, file_path, uploaded_at)
    VALUES (?, ?, ?, NOW())
");

$stmt->bind_param(
    'iss',
    $customer_id,
    $document_type,
    $filePathForDb
);

if (!$stmt->execute()) {
    http_response_code(500);
    exit('DB insert failed');
}

echo 'OK';
exit;
