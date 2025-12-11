<?php
include '../config/DBconfig.php';
session_start();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $title = $_POST['title'];
    $difficulty = $_POST['difficulty'];
    $user_id = $_SESSION['user_id'];
    $gold = 0;
    $xp = 0;
    if($difficulty == 'Easy') {
        $gold = 5;
        $xp = 3;
    } elseif ($difficulty == 'Medium') {
        $gold = 10;
        $xp = 6;
    } elseif ($difficulty == 'Hard') {
        $gold = 15;
        $xp = 9;
    }

    $stmt = $conn->prepare("INSERT INTO habits (user_id, title, difficulty,xp_reward,gold_reward) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $user_id, $title, $difficulty, $xp, $gold);

    if($stmt->execute()){
        if($_POST['page'] == 'Dashboard')
        header("Location: ../Dashboard.php");
        else
        header("Location: ../Habit.php");
        exit();
    } else {
        $err = "Error: " . $stmt->error;
        
    }

    $stmt->close();
    $conn->close();
}