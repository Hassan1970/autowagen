<?php
require_once __DIR__ . '/config/config.php';

$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($id <= 0) {
    die('Invalid customer');
}

if ($action === 'deactivate') {
    $stmt = $conn->prepare("UPDATE customers SET is_active = 0 WHERE id = ?");
} elseif ($action === 'activate') {
    $stmt = $conn->prepare("UPDATE customers SET is_active = 1 WHERE id = ?");
} else {
    die('Invalid action');
}

$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: customer_list.php");
exit;
