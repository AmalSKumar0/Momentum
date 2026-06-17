<?php
ob_start();

// Configure secure session cookie settings before any session starts
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    $isSecure = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') 
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function handleError($error) {
    error_log($error);
    die("Something went wrong! Please try again later.");
}

function validateCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die("Invalid CSRF token.");
    }
}

// Load local .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $line = trim($line);
        if (strpos($line, '=') !== false) {
            list($env_key, $env_val) = explode('=', $line, 2);
            $env_key = trim($env_key);
            $env_val = trim($env_val);
            // Remove optional surrounding quotes
            $env_val = trim($env_val, "\"'");
            if (!array_key_exists($env_key, $_SERVER) && !array_key_exists($env_key, $_ENV)) {
                putenv("{$env_key}={$env_val}");
                $_ENV[$env_key] = $env_val;
                $_SERVER[$env_key] = $env_val;
            }
        }
    }
}

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '2208';
$db_name = getenv('DB_NAME') ?: 'habit_tracker';
$db_port = getenv('DB_PORT') ?: '3306';
$db_ssl_ca = getenv('DB_SSL_CA') ?: null;

// Auto-detect Aiven cloud and enable SSL CA if not explicitly provided
if (!$db_ssl_ca && strpos($db_host, 'aivencloud.com') !== false) {
    if (file_exists(__DIR__ . '/../ca.pem')) {
        $db_ssl_ca = 'ca.pem';
    }
}

$conn = mysqli_init();

if ($db_ssl_ca) {
    // If the path to CA is relative, resolve it from the project root
    $resolved_ca = realpath($db_ssl_ca);
    if ($resolved_ca === false) {
        $resolved_ca = realpath(__DIR__ . '/../' . $db_ssl_ca);
    }
    
    if ($resolved_ca !== false) {
        $conn->ssl_set(NULL, NULL, $resolved_ca, NULL, NULL);
    }
}

$success = @$conn->real_connect($db_host, $db_user, $db_pass, $db_name, $db_port, NULL, $db_ssl_ca ? MYSQLI_CLIENT_SSL : 0);

if (!$success || $conn->connect_error) {
    handleError("Connection failed: " . ($conn ? $conn->connect_error : mysqli_connect_error()));
}
?>