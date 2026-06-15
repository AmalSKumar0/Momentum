<?php

function handleError($error) {
    error_log($error);
    die("Something went wrong! Please try again later.");
}

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: 'habit_tracker';
$db_port = getenv('DB_PORT') ?: '3306';
$db_ssl_ca = getenv('DB_SSL_CA') ?: null;

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