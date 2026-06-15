<?php

function handleError($error) {
    error_log($error);
    die("Something went wrong! Please try again later.");
}

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: 'habit_tracker';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn || $conn->connect_error) {
    handleError("Connection failed: " . ($conn ? $conn->connect_error : mysqli_connect_error()));
}
?>