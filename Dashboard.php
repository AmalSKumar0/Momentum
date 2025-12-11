<?php
include 'config/DBconfig.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Auth/login.php");
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT h.*, a.isComplete as status
        FROM habits h
        LEFT JOIN activity a 
            ON h.id = a.HabitID 
            AND a.UserID = h.user_id 
            AND DATE(a.completedDay) = CURDATE()
        WHERE h.user_id = ?
        GROUP BY h.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
foreach ($items as &$item) {
    if ($item['status'] === null) {
        $item['status'] = null; 
    } else {
        $item['status'] = true;
    }
}
unset($item);
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
    <?php $page = 1;
    include './components/sidebar.php'; ?>
    <main class="main-content">
        <?php include './components/header.php'; ?>
        <div class="dashboard-container">
            <div class="month-nav">
                <div class="nav-arrow"><i class="fas fa-chevron-left"></i></div>
                <div class="month-list" id="monthList">
                    <!-- JS Populated -->
                </div>
                <div class="nav-arrow"><i class="fas fa-chevron-right"></i></div>
            </div>

            <div class="date-strip" id="dateStrip">
                <!-- JS Populated -->
            </div>

            <!-- Quest Board -->
            <div class="section-header">
                <h2><i class="fas fa-fire"></i> Daily Quests</h2>
                <button class="ai-brief-btn" onclick="toggleModal()">
                    <i class="fas fa-sparkles"></i> Add Habits
                </button>
            </div>

            <div class="course-list">
                <!-- Quest 1 (Epic) -->
                <?php foreach ($items as $item): ?>
                    <div class="quest-card <?php if ($item['status'] != NULL) echo 'quest-done-card'; ?> <?php if (htmlspecialchars($item['difficulty']) == 'easy') {
                                                                                                            echo 'common';
                                                                                                        } ?> <?php if (htmlspecialchars($item['difficulty']) == 'medium') {
                                                        echo 'rare';
                                                    }  ?>  <?php if (htmlspecialchars($item['difficulty']) == 'hard') {
                                                                                                                                        echo 'epic';
                                                                                                                                    } ?>">
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
                            <div class="loot-tag"><i class="fas fa-star"></i> -<?php echo $item['xp_reward']; ?>XP</div>
                            <div class="loot-tag">
                                🪙
                                <?php
                                if ($item['difficulty'] == 'easy') {
                                    echo '5';
                                } elseif ($item['difficulty'] == 'medium') {
                                    echo '10';
                                } elseif ($item['difficulty'] == 'hard') {
                                    echo '15';
                                }
                                ?>
                                Coins
                            </div>
                        </div>

                        <div class="action-row">
                            <button class="btn-complete <?php if ($item['status'] != NULL) echo 'quest-done-btn'; ?> " onclick=window.location.href="actions.php?did=<?php echo $item['id']; ?>">Complete</button>
                            <button class="btn-ai-help <?php if ($item['status'] != NULL) echo 'quest-done-btn'; ?> " onclick=window.location.href="actions.php?didnt=<?php echo $item['id']; ?>">
                                <i class="fas fa-cross"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($items) === 0): ?>
                    <h2></i>The quest board is currently empty. Check back later!</h2>
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

                <input type="hidden" name="page" value="Dashboard">

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