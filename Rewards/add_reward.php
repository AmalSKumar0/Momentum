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

    $difficulty = 'medium';
    $diff_lower = strtolower($difficulty_input);
    if (in_array($diff_lower, ['easy', 'medium', 'hard'])) {
        $difficulty = $diff_lower;
    } else {
        handleError("Invalid difficulty value.");
    }

    $gold = 50;
    $xp = 5;

    $stmt = $conn->prepare("INSERT INTO customshop(user_id, title, difficulty, xp_reward, gold_cost) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issii", $user_id, $title, $difficulty, $xp, $gold);

    if($stmt->execute()){
        $stmt->close();
        $conn->close();
        header("Location: ../Shop.php");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        handleError("Failed to add custom reward.");
    }
}
?>