<?php

// Database Configuration for BuildORA Blog Application
// Loads credentials from config/database.local.php (if present),
// environment variables (Railway, Docker, InfinityFree, etc.),
// or standard local development defaults.

// Prevent unhandled mysqli exceptions from causing 500 errors in PHP 8.1+
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

// 1. Check if mysqli extension is available
if (!extension_loaded('mysqli') && !class_exists('mysqli')) {
    die("Database connection error: Class \"mysqli\" not found. Please ensure the PHP MySQLi extension is installed and enabled.");
}

$localConfig = __DIR__ . '/database.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

// Helper function to resolve environment variables safely
function getEnvVar($key, $default = '') {
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    return $default;
}

// 2. Check for database connection URL (e.g. MYSQL_URL / DATABASE_URL on Railway)
$dbUrl = getEnvVar('MYSQL_URL', getEnvVar('DATABASE_URL'));
$urlHost = '';
$urlUser = '';
$urlPass = '';
$urlName = '';
$urlPort = 3306;

if (!empty($dbUrl)) {
    $parsedUrl = parse_url($dbUrl);
    if ($parsedUrl) {
        $urlHost = $parsedUrl['host'] ?? '';
        $urlUser = $parsedUrl['user'] ?? '';
        $urlPass = $parsedUrl['pass'] ?? '';
        $urlName = ltrim($parsedUrl['path'] ?? '', '/');
        $urlPort = isset($parsedUrl['port']) ? (int)$parsedUrl['port'] : 3306;
    }
}

// 3. Resolve Database Credentials (Constants -> Railway/Cloud Envs -> DB_ Envs -> Local Defaults)
$host = defined('DB_HOST') ? DB_HOST : (
    !empty($urlHost) ? $urlHost :
    getEnvVar('MYSQLHOST', getEnvVar('MYSQL_HOST', getEnvVar('DB_HOST', 'localhost')))
);

$username = defined('DB_USER') ? DB_USER : (
    !empty($urlUser) ? $urlUser :
    getEnvVar('MYSQLUSER', getEnvVar('MYSQL_USER', getEnvVar('DB_USER', 'root')))
);

$password = defined('DB_PASS') ? DB_PASS : (
    $urlPass !== '' ? $urlPass :
    getEnvVar('MYSQLPASSWORD', getEnvVar('MYSQL_PASSWORD', getEnvVar('MYSQL_ROOT_PASSWORD', getEnvVar('DB_PASS', ''))))
);

$database = defined('DB_NAME') ? DB_NAME : (
    !empty($urlName) ? $urlName :
    getEnvVar('MYSQLDATABASE', getEnvVar('MYSQL_DATABASE', getEnvVar('DB_NAME', 'project_showcase')))
);

$port = defined('DB_PORT') ? (int)DB_PORT : (
    $urlPort !== 3306 ? $urlPort :
    (int)getEnvVar('MYSQLPORT', getEnvVar('MYSQL_PORT', getEnvVar('DB_PORT', 3306)))
);

// Attempt primary database connection
try {
    $conn = @new mysqli($host, $username, $password, $database, $port);
} catch (Throwable $e) {
    $conn = false;
}

// Automatic fallback for local XAMPP environment if remote host is blocked/unreachable
if (!$conn || $conn->connect_error) {
    try {
        $localConn = @new mysqli('localhost', 'root', '', 'project_showcase', 3306);
        if ($localConn && !$localConn->connect_error) {
            $conn = $localConn;
        } else {
            $errMsg = ($conn && $conn->connect_error) ? $conn->connect_error : "Unable to reach database host ($host)";
            die("Database connection failed: " . htmlspecialchars($errMsg) . "<br>Please check your database configuration and environment variables.");
        }
    } catch (Throwable $e) {
        die("Database connection error: " . htmlspecialchars($e->getMessage()) . "<br>Please check your database configuration and environment variables.");
    }
}

$conn->set_charset("utf8mb4");

?>