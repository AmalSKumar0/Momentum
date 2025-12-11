<?php
include 'config/DBconfig.php';
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: Auth/login.php");
}

$user_id = $_SESSION['user_id'];
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    $sql = "SELECT gold, xp FROM users WHERE uid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $gold = $user['gold'];
    $xp = $user['xp'];


    if (isset($_GET['did'])) {
        $habit_id = $_GET['did'];
        $stmt = $conn->prepare("SELECT xp_reward, gold_reward FROM habits WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $habit_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($habit = $result->fetch_assoc()) {
            $gold += $habit['gold_reward'];
            $stmt = $conn->prepare("INSERT into activity (UserID,HabitID,completedDay) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $user_id, $habit_id);
            $stmt->execute();
        }
    } elseif (isset($_GET['didnt'])) {
        $habit_id = $_GET['didnt'];
        $stmt = $conn->prepare("SELECT xp_reward, gold_reward FROM habits WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $habit_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($habit = $result->fetch_assoc()) {
            $xp -= $habit['xp_reward'];
            if ($xp < 0){ $xp = 0;}
            $stmt = $conn->prepare("INSERT into activity (UserID,HabitID,isComplete,completedDay) VALUES (?, ?, 0, NOW())");
            $stmt->bind_param("ii", $user_id, $habit_id);
            $stmt->execute();
        }
    }
    $stmt = $conn->prepare("UPDATE users SET xp = ?, gold = ? WHERE uid = ?");
    $stmt->bind_param("iii", $xp, $gold, $user_id);
    $stmt->execute();
    if ($xp == 0){
        header("Location: died.php");
        exit();
    }
    $stmt->close();
    $conn->close();
    header("Location: Dashboard.php");
    exit();
}