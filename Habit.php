<?php
include 'config/DBconfig.php';
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: Auth/login.php");
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM habits WHERE user_id = $user_id";
$stmt = $conn->query($sql);
$items = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Momentum Course Dashboard</title>
    <!-- Google Font: Poppins & Nunito (Rounder for games) -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Markdown Parser -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="styles/styles.css">
</head>

<body>
    <?php $page = 2;
    include './components/sidebar.php'; ?>
    <main class="main-content">
        <?php include './components/header.php'; ?>

        <div class="dashboard-container">
            <div class="section-header">
                <h2><i class="fas fa-fire"></i> Your Habits</h2>
                <button class="ai-brief-btn" onclick="toggleModal()">
                    <i class="fas fa-sparkles"></i> Add Habits
                </button>
            </div>

            <div class="course-list">
                <?php foreach ($items as $item): ?>
                     <div class="quest-card  <?php if (htmlspecialchars($item['difficulty']) == 'easy') {echo 'common';} ?> <?php if (htmlspecialchars($item['difficulty']) == 'medium') {echo 'rare';}  ?>  <?php if (htmlspecialchars($item['difficulty']) == 'hard') {echo 'epic';} ?>">
                        <div class="quest-header">
                            <div class="icon-box">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div class="quest-info">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="quest-meta">
                                    <span><i class="fas fa-shield-alt"></i> <?php echo htmlspecialchars($item['difficulty']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="quest-rewards">
                            <div class="loot-tag"><i class="fas fa-star"></i> <?php echo htmlspecialchars($item['xp_reward']); ?>XP</div>
                            <div class="loot-tag">
                                🪙
                                <?php echo htmlspecialchars($item['gold_reward']); ?> Coins
                            </div>
                        </div>

                        <div class="action-row">
                            <button class="btn-complete " onclick="window.location.href='habits/edit_habit.php?habit_id=<?php echo $item['id']; ?>'"><i class="fas fa-edit"></i></button>
                            <button class="btn-ai-help " onclick="window.location.href='habits/delete_habit.php?habit_id=<?php echo $item['id']; ?>'">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($items) === 0): ?>
                    <h2>The quest board is currently empty. Check back later!</h2>
                <?php endif; ?>
                <!-- Quest 2 (Rare) -->
            </div>
        </div>
    </main>


    <div class="modal-overlay" id="questModal">
        <!-- Note: Class changed to modal-card -->
        <div class="modal-card">
            <button class="close-btn" onclick="toggleModal()"><i class="fas fa-times"></i></button>

            <div class="form-header" style="display: flex; gap: 15px; margin-bottom: 30px; align-items: center;">
                <div class="icon-wrapper" style="width: 50px; height: 50px; background: #e0dcfc; color: #6c5ce7; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas fa-scroll"></i>
                </div>
                <div>
                    <h2 style="margin:0; color:#2d3436;">New Quest</h2>
                    <div style="font-size:0.9rem; color:#636e72;">Define your challenge</div>
                </div>
            </div>

            <form action="habits/add_habit.php" method="POST">
                <div class="form-group">
                    <label for="title"><i class="fas fa-pen"></i> Quest Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Read 10 Pages..." required>
                </div>

                <div class="form-group">
                    <label for="difficulty"><i class="fas fa-signal"></i> Difficulty Level</label>
                    <div class="select-wrapper">
                        <select id="difficulty" name="difficulty" required>
                            <option value="" disabled selected>Select Difficulty...</option>
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>
                </div>

                <input type="submit" value="Create Quest">
            </form>
        </div>
    </div>
    <script src="assets/scripts/script.js"></script>
</body>

</html>