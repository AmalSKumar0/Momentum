<?php
include '../config/DBconfig.php';
session_start();

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: ../Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$err = "";

// Handle Form Submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $title = $_POST['title'];
    $difficulty = $_POST['difficulty'];
    $habit_id = $_POST['habit_id'];
    
    // Calculate Rewards based on difficulty
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

    $stmt = $conn->prepare("UPDATE habits SET title = ?, difficulty = ?, xp_reward = ?, gold_reward = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssiiii", $title, $difficulty, $xp, $gold, $habit_id, $user_id);

    if($stmt->execute()){
        header("Location: ../Habit.php");
        exit();
    } else {
        $err = "Error updating quest: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch Existing Habit Data
if (isset($_GET['habit_id'])) {
    $id = $_GET['habit_id'];
    $stmt = $conn->prepare("SELECT * FROM habits WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
}

// If no habit found (or ID not provided), redirect back
if (!$item) {
    header("Location: ../Habit.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quest - Momentum</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/edit.css">
</head>
<body>

    <!-- Overlay Background -->
    <div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(45,52,54,0.6); backdrop-filter:blur(5px); z-index:-1;"></div>

    <div class="modal-card">
        <!-- Close button acts as Cancel/Back -->
        <a href="../Habit.php" class="close-btn"><i class="fas fa-times"></i></a>

        <div class="form-header" style="display: flex; gap: 15px; margin-bottom: 30px; align-items: center;">
            <div class="icon-wrapper" style="width: 50px; height: 50px; background: #e0dcfc; color: #6c5ce7; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <h2 style="margin:0; font-family:'Poppins', sans-serif; color:#2d3436;">Edit Quest</h2>
                <div style="font-size:0.9rem; color:#636e72;">Modify existing challenge</div>
            </div>
        </div>

        <?php if($err): ?>
            <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $err; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="habit_id" value="<?php echo $item['id']; ?>">
            
            <div class="form-group">
                <label for="title"><i class="fas fa-pen"></i> Quest Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" placeholder="e.g. Read 10 Pages..." required>
            </div>

            <div class="form-group">
                <label for="difficulty"><i class="fas fa-signal"></i> Difficulty Level</label>
                <div class="select-wrapper">
                    <select id="difficulty" name="difficulty" required>
                        <option value="Easy" <?php if ($item['difficulty'] == 'Easy') echo 'selected'; ?>>Easy (+3 XP / 5 Gold)</option>
                        <option value="Medium" <?php if ($item['difficulty'] == 'Medium') echo 'selected'; ?>>Medium (+6 XP / 10 Gold)</option>
                        <option value="Hard" <?php if ($item['difficulty'] == 'Hard') echo 'selected'; ?>>Hard (+9 XP / 15 Gold)</option>
                    </select>
                </div>
            </div>

            <input type="submit" value="Save Changes">
        </form>
    </div>

</body>
</html>