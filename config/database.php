<?php

// Database Configuration for BuildORA Blog Application
// Loads credentials from config/database.local.php (if present),
// environment variables, or standard local development defaults.

// Prevent unhandled mysqli exceptions from causing 500 errors in PHP 8.1+
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

$localConfig = __DIR__ . '/database.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

$host     = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost'));
$username = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root'));
$password = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : (isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : ''));
$database = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'project_showcase'));
$port     = defined('DB_PORT') ? (int)DB_PORT : (int)(getenv('DB_PORT') ?: (isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : 3306));

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
            die("Database connection failed: " . htmlspecialchars($errMsg) . "<br>Please check <code>config/database.local.php</code> on your hosting server.");
        }
    } catch (Throwable $e) {
        die("Database connection error: " . htmlspecialchars($e->getMessage()) . "<br>Please check <code>config/database.local.php</code> on your hosting server.");
    }
}

$conn->set_charset("utf8mb4");

?>