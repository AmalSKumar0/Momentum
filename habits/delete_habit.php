<?php
include '../config/DBconfig.php';
// Session start is automatically called in DBconfig.php
if(!isset($_SESSION['user_id'])){
    header("Location: ../Auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    validateCSRF($_POST['csrf_token'] ?? '');
    $habit_id = $_POST['habit_id'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM activity WHERE HabitID = ? AND UserID = ?");
        $stmt->bind_param("ii", $habit_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $habit_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $conn->close();
        header("Location: ../Habit.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        handleError("Deletion failed: " . $e->getMessage());
    }
}
header("Location: ../Habit.php");
exit();
?>