<?php

// Database Configuration for BuildORA Blog Application
// Loads credentials from config/database.local.php (if present),
// environment variables, or standard local development defaults.

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
$conn = @new mysqli($host, $username, $password, $database, $port);

// Automatic fallback for local XAMPP environment if remote host is blocked/unreachable
if ($conn->connect_error) {
    $localConn = @new mysqli('localhost', 'root', '', 'project_showcase', 3306);
    if (!$localConn->connect_error) {
        $conn = $localConn;
    } else {
        die("Database connection failed: " . $conn->connect_error . " (Local fallback: " . $localConn->connect_error . ")");
    }
}

$conn->set_charset("utf8mb4");

?>