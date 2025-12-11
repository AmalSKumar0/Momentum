<?php
include '../config/DBconfig.php';
session_start();
$user_id = $_SESSION['user_id'];
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    $habit_id = $_GET['habit_id'];
    $stmt = $conn->prepare("DELETE FROM activity WHERE HabitID = ? AND UserID = ?");
    $stmt->bind_param("ii", $habit_id, $user_id);
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $habit_id, $user_id);

    if($stmt->execute()){
        header("Location: ../Habit.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
header("Location: ../Habit.php");
exit();