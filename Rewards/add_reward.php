<?php
include '../config/DBconfig.php';
session_start();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $title = $_POST['title'];
    $difficulty = $_POST['difficulty'];
    $user_id = $_SESSION['user_id'];
    $gold = 50;
    $xp = 5;

    $stmt = $conn->prepare("INSERT INTO customshop(user_id, title, difficulty,xp_reward,gold_cost) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $user_id, $title, $difficulty, $xp, $gold);

    if($stmt->execute()){
        header("Location: ../Shop.php");
        exit();
    } else {
        $err = "Error: " . $stmt->error;
        
    }

    $stmt->close();
    $conn->close();
}