<?php

function handleError($error) {
    error_log($error);
    die("Something went wrong! Please try again later.");
}

$conn = mysqli_connect("localhost", "root", "", "habit_tracker");

if ($conn->connect_error) {
    handleError("Connection failed: " . $conn->connect_error);
}
?>