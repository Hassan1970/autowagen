<?php
require_once __DIR__ . '/config.php';

$res = $conn->query("SELECT DATABASE() AS db");
$row = $res->fetch_assoc();

echo "Connected DB: " . $row['db'];
