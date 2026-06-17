<?php
include '../config/DBconfig.php';
// Session start is automatically called in DBconfig.php
if(!isset($_SESSION['user_id'])){
    header("Location: ../Auth/login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    validateCSRF($_POST['csrf_token'] ?? '');
    
    $title = trim($_POST['title']);
    $difficulty_input = trim($_POST['difficulty']);
    $user_id = $_SESSION['user_id'];

    if (empty($title)) {
        handleError("Title cannot be empty.");
    }

    // Map difficulty and set rewards
    $difficulty = 'medium';
    $gold = 10;
    $xp = 6;
    
    $diff_lower = strtolower($difficulty_input);
    if($diff_lower === 'easy') {
        $difficulty = 'easy';
        $gold = 5;
        $xp = 3;
    } elseif ($diff_lower === 'medium') {
        $difficulty = 'medium';
        $gold = 10;
        $xp = 6;
    } elseif ($diff_lower === 'hard') {
        $difficulty = 'hard';
        $gold = 15;
        $xp = 9;
    } else {
        handleError("Invalid difficulty value.");
    }

    $stmt = $conn->prepare("INSERT INTO habits (user_id, title, difficulty, xp_reward, gold_reward) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $user_id, $title, $difficulty, $xp, $gold);

    if($stmt->execute()){
        $page = $_POST['page'] ?? '';
        $stmt->close();
        $conn->close();
        if($page == 'Dashboard') {
            header("Location: ../Dashboard.php");
        } else {
            header("Location: ../Habit.php");
        }
        exit();
    } else {
        $stmt->close();
        $conn->close();
        handleError("Failed to add habit.");
    }
}
?>