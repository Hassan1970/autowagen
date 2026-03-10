<?php
/**
 * Global helper functions
 * Loaded once via config.php
 */

if (!function_exists('h')) {
    function h($v) {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

if (!function_exists('post')) {
    function post($key, $default = null) {
        return $_POST[$key] ?? $default;
    }
}

if (!function_exists('get')) {
    function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }
}
