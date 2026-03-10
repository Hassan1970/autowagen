<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;

if ($amount <= 0) {
    echo '';
    exit;
}

if (!function_exists('encode_cost_secure')) {
    echo '';
    exit;
}

echo encode_cost_secure($amount);
