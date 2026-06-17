<?php
include 'config/DBconfig.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id'])){
    header("Location: Auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM habits WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quests - Momentum</title>
    <!-- Google Font: Poppins & Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>

<body>
    <?php $page = 2;
    include './components/sidebar.php'; ?>
    <main class="main-content">
        <?php include './components/header.php'; ?>

        <div class="dashboard-container">
            <div class="section-header">
                <h2><i class="fas fa-fire" style="color: var(--accent-red);"></i> Manage Habits</h2>
                <button class="ai-brief-btn" onclick="toggleModal()">
                    <i class="fas fa-plus"></i> Add Quest
                </button>
            </div>

            <div class="course-list">
                <?php foreach ($items as $item): ?>
                     <div class="quest-card <?php if (htmlspecialchars($item['difficulty']) == 'easy') {
                                                 echo 'common';
                                             } elseif (htmlspecialchars($item['difficulty']) == 'medium') {
                                                 echo 'rare';
                                             } elseif (htmlspecialchars($item['difficulty']) == 'hard') {
                                                 echo 'epic';
                                             } ?>">
                        <div class="quest-header">
                            <div class="icon-box">
                                <i class="fas fa-scroll"></i>
                            </div>
                            <div class="quest-info">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="quest-meta">
                                    <span><i class="fas fa-shield-alt"></i> <?php echo htmlspecialchars(ucfirst($item['difficulty'])); ?></span>
                                    <?php if (intval($item['streak']) > 0): ?>
                                        <span style="color: var(--accent-gold); margin-left: 8px;"><i class="fas fa-fire"></i> <?php echo intval($item['streak']); ?> Streak</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="quest-rewards">
                            <div class="loot-tag"><i class="fas fa-star" style="color: var(--primary-light);"></i> +<?php echo htmlspecialchars($item['xp_reward']); ?> XP</div>
                            <div class="loot-tag"><i class="fas fa-coins" style="color: var(--accent-gold);"></i> +<?php echo htmlspecialchars($item['gold_reward']); ?> Gold</div>
                        </div>

                        <div class="action-row">
                            <button class="btn-complete" onclick="window.location.href='habits/edit_habit.php?habit_id=<?php echo $item['id']; ?>'" style="display: flex; align-items: center; justify-content: center; gap: 8px; flex: 2;">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="habits/delete_habit.php" method="POST" style="display:inline-block; flex: 1;" onsubmit="return confirm('Are you sure you want to delete this habit?');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="habit_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-ai-help" style="width: 100%;" title="Delete Quest">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($items) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-scroll-old" style="font-size: 2.5rem; margin-bottom: 15px; display: block; color: var(--text-muted);"></i>
                        <h3>You have no habits.</h3>
                        <p>Click "Add Quest" to create one!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>


    <div class="modal-overlay" id="questModal">
        <div class="modal-card">
            <button class="close-btn" onclick="toggleModal()"><i class="fas fa-times"></i></button>

            <div class="form-header" style="display: flex; gap: 15px; margin-bottom: 30px; align-items: center;">
                <div class="icon-wrapper" style="width: 50px; height: 50px; background: rgba(140, 122, 230, 0.15); color: var(--primary-light); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border: 1px solid rgba(140, 122, 230, 0.25);">
                    <i class="fas fa-scroll"></i>
                </div>
                <div>
                    <h2 style="margin:0; color:var(--text-dark);">New Quest</h2>
                    <div style="font-size:0.9rem; color:var(--text-muted);">Define your challenge</div>
                </div>
            </div>

            <form action="habits/add_habit.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="title"><i class="fas fa-pen"></i> Quest Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Read 10 Pages..." required autocomplete="off">
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