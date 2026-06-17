<?php
include 'config/DBconfig.php';
// Session start is automatically called inside DBconfig.php
if(!isset($_SESSION['user_id'])){
    header("Location: Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    validateCSRF($_POST['csrf_token'] ?? '');

    $conn->begin_transaction();
    try {
        // 1. Fetch user stats
        $sql = "SELECT gold, hp, xp, level FROM users WHERE uid = ? FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $gold = intval($user['gold']);
        $hp = intval($user['hp']);
        $xp = intval($user['xp']);
        $level = intval($user['level']);
        $stmt->close();

        $did_level_up = false;

        if (isset($_POST['did'])) {
            $habit_id = intval($_POST['did']);
            // Fetch habit details with streak info
            $stmt = $conn->prepare("SELECT xp_reward, gold_reward, difficulty, streak, last_completed FROM habits WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $habit_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($habit = $result->fetch_assoc()) {
                $xp_reward = intval($habit['xp_reward']);
                $gold_reward = intval($habit['gold_reward']);
                $old_streak = intval($habit['streak']);
                $last_completed = $habit['last_completed'];

                $today = date('Y-m-d');
                $yesterday = date('Y-m-d', strtotime('-1 day'));

                // Calculate new streak
                if (empty($last_completed)) {
                    $new_streak = 1;
                } elseif ($last_completed === $today) {
                    $new_streak = $old_streak; // Already completed today, keep streak
                } elseif ($last_completed === $yesterday) {
                    $new_streak = $old_streak + 1;
                } else {
                    $new_streak = 1; // Broke streak
                }

                // Streak multipliers
                $multiplier = 1.0;
                if ($new_streak >= 30) {
                    $multiplier = 1.5;
                } elseif ($new_streak >= 10) {
                    $multiplier = 1.25;
                } elseif ($new_streak >= 5) {
                    $multiplier = 1.1;
                }

                $gold_gained = round($gold_reward * $multiplier);
                $gold += $gold_gained;
                $xp += $xp_reward;

                // Level up checking
                while ($xp >= $level * 100) {
                    $xp -= $level * 100;
                    $level++;
                    $hp = min(100, $hp + 25); // Restores 25 HP on level up
                    $did_level_up = true;
                }

                $stmt->close();

                // Update habit completion status
                $stmt = $conn->prepare("UPDATE habits SET streak = ?, last_completed = CURDATE(), clicked = CURDATE() WHERE id = ?");
                $stmt->bind_param("ii", $new_streak, $habit_id);
                $stmt->execute();
                $stmt->close();

                // Record activity
                $stmt = $conn->prepare("INSERT into activity (UserID, HabitID, completedDay, isComplete) VALUES (?, ?, CURDATE(), 1)");
                $stmt->bind_param("ii", $user_id, $habit_id);
                $stmt->execute();
                $stmt->close();
            }
        } elseif (isset($_POST['didnt'])) {
            $habit_id = intval($_POST['didnt']);
            $stmt = $conn->prepare("SELECT difficulty FROM habits WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $habit_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($habit = $result->fetch_assoc()) {
                $difficulty = strtolower($habit['difficulty']);
                
                // Damage scaling based on difficulty
                $damage = 10; // Medium default
                if ($difficulty === 'easy') {
                    $damage = 5;
                } elseif ($difficulty === 'hard') {
                    $damage = 20;
                }

                $hp -= $damage;
                if ($hp < 0) {
                    $hp = 0;
                }

                $stmt->close();

                // Reset habit streak to 0 on failure
                $stmt = $conn->prepare("UPDATE habits SET streak = 0 WHERE id = ?");
                $stmt->bind_param("i", $habit_id);
                $stmt->execute();
                $stmt->close();

                // Record activity as failed
                $stmt = $conn->prepare("INSERT into activity (UserID, HabitID, isComplete, completedDay) VALUES (?, ?, 0, CURDATE())");
                $stmt->bind_param("ii", $user_id, $habit_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // 2. Update user stats
        $stmt = $conn->prepare("UPDATE users SET hp = ?, xp = ?, level = ?, gold = ? WHERE uid = ?");
        $stmt->bind_param("iiiii", $hp, $xp, $level, $gold, $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $conn->close();

        if ($hp <= 0) {
            header("Location: died.php");
            exit();
        }

        // Redirect with message if level up occurred
        $redirect_url = "Dashboard.php";
        if (isset($_POST['did'])) {
            $msg = "Quest completed!";
            if ($did_level_up) {
                $msg .= " Level Up! You are now Level " . $level . "!";
            }
            $redirect_url .= "?msg=" . urlencode($msg) . "&confetti=true";
        } else {
            $redirect_url .= "?msg=" . urlencode("Quest failed! You took damage.");
        }
        
        header("Location: " . $redirect_url);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        handleError("Transaction failed: " . $e->getMessage());
    }
} else {
    header("Location: Dashboard.php");
    exit();
}
?>