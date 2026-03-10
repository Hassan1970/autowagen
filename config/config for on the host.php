```php
<?php
/**
 * Autowagen Master - Database Configuration
 * FINAL & SINGLE SOURCE OF TRUTH
 */

/* =====================================================
   ERROR REPORTING
   (Disable display_errors later in production)
===================================================== */

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =====================================================
   DATABASE SETTINGS (AUTO LOCAL / LIVE DETECTION)
===================================================== */

$db_host = "localhost";

if ($_SERVER['SERVER_NAME'] == 'localhost') {

    // LOCAL XAMPP DATABASE
    $db_user = "root";
    $db_pass = "";
    $db_name = "autowagen";

} else {

    // LIVE HOSTING DATABASE
    $db_user = "ahnwebde_autowagen_admin";
    $db_pass = "l_I8cso8}B0Y]X68";
    $db_name = "ahnwebde_autowagen_epc_clean";

}

/* =====================================================
   MYSQL CONNECTION
===================================================== */

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(
        "Database connection failed<br>" .
        "HOST: {$db_host}<br>" .
        "USER: {$db_user}<br>" .
        "DB: {$db_name}<br>" .
        "ERROR: " . $conn->connect_error
    );
}

$conn->set_charset("utf8mb4");

/* =====================================================
   GLOBAL HELPER FUNCTIONS
===================================================== */

if (!function_exists('h')) {
    function h($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

/* =====================================================
   SESSION
===================================================== */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =====================================================
   TIMEZONE
===================================================== */

date_default_timezone_set("Africa/Johannesburg");
```
