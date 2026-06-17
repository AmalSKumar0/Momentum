<?php
include 'config/DBconfig.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: Auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}
$today_str = date('Y-m-d');

// Select habits for the selected date
$sql = "SELECT h.*, MAX(a.isComplete) as status
        FROM habits h
        LEFT JOIN activity a 
            ON h.id = a.HabitID 
            AND a.UserID = h.user_id 
            AND a.completedDay = ?
        WHERE h.user_id = ?
        GROUP BY h.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $selected_date, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($items as &$item) {
    if ($item['status'] === null) {
        $item['status'] = null; 
    } else {
        $item['status'] = intval($item['status']);
    }
}
unset($item);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Momentum</title>
    <!-- Google Font: Poppins & Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Canvas Confetti for completion explosions -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
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
                <h2><i class="fas fa-fire" style="color: #ff7675;"></i> Daily Quests</h2>
                <button class="ai-brief-btn" onclick="toggleModal()">
                    <i class="fas fa-plus"></i> Add Quest
                </button>
            </div>

            <div class="course-list">
                <?php foreach ($items as $item): ?>
                    <div class="quest-card <?php if ($item['status'] !== NULL) echo 'quest-done-card'; ?> <?php if (htmlspecialchars($item['difficulty']) == 'easy') {
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
                            <div class="loot-tag"><i class="fas fa-star" style="color: var(--primary-light);"></i> +<?php echo $item['xp_reward']; ?> XP</div>
                            <div class="loot-tag"><i class="fas fa-coins" style="color: var(--accent-gold);"></i> +<?php echo $item['gold_reward']; ?> Gold</div>
                        </div>

                        <div class="action-row">
                            <?php if ($selected_date === $today_str): ?>
                                <?php if ($item['status'] === NULL): ?>
                                    <form action="actions.php" method="POST" style="display:inline-block; margin-right:5px; flex: 2;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="did" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn-complete" style="width:100%;"><i class="fas fa-check"></i> Complete</button>
                                    </form>
                                    <form action="actions.php" method="POST" style="display:inline-block; flex: 1;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="didnt" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn-ai-help" style="width:100%;" title="Fail Quest (Take Damage)">
                                            <i class="fas fa-heart-broken"></i>
                                        </button>
                                    </form>
                                <?php elseif ($item['status'] === 1): ?>
                                    <div class="quest-status-badge success" style="width:100%; text-align:center; padding: 12px; border-radius: 12px; background: rgba(46, 204, 113, 0.15); color: #2ecc71; font-weight: 800; border: 1px solid rgba(46, 204, 113, 0.25); font-size: 0.85rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-check-circle"></i> COMPLETED TODAY
                                    </div>
                                <?php else: ?>
                                    <div class="quest-status-badge danger" style="width:100%; text-align:center; padding: 12px; border-radius: 12px; background: rgba(255, 118, 117, 0.15); color: #ff7675; font-weight: 800; border: 1px solid rgba(255, 118, 117, 0.25); font-size: 0.85rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-times-circle"></i> FAILED TODAY
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($selected_date < $today_str): ?>
                                    <?php if ($item['status'] === 1): ?>
                                        <div class="quest-status-badge success" style="width:100%; text-align:center; padding: 12px; border-radius: 12px; background: rgba(46, 204, 113, 0.1); color: #2ecc71; font-weight: 700; border: 1px solid rgba(46, 204, 113, 0.15); font-size: 0.85rem; letter-spacing: 0.5px;">
                                            <i class="fas fa-check-circle"></i> COMPLETED
                                        </div>
                                    <?php elseif ($item['status'] === 0): ?>
                                        <div class="quest-status-badge danger" style="width:100%; text-align:center; padding: 12px; border-radius: 12px; background: rgba(255, 118, 117, 0.15); color: #ff7675; font-weight: 700; border: 1px solid rgba(255, 118, 117, 0.25); font-size: 0.85rem; letter-spacing: 0.5px;">
                                            <i class="fas fa-times-circle"></i> FAILED
                                        </div>
                                    <?php else: ?>
                                        <div class="quest-status-badge muted" style="width:100%; text-align:center; padding: 12px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); color: var(--text-muted); font-weight: 700; border: 1px solid var(--border-color); font-size: 0.85rem; letter-spacing: 0.5px;">
                                            <i class="fas fa-minus-circle"></i> MISSED
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="quest-status-badge upcoming" style="width:100%; text-align:center; padding: 12px; border-radius: 12px; background: rgba(255, 255, 255, 0.03); color: var(--text-muted); font-weight: 700; border: 1px dashed var(--border-color); font-size: 0.85rem; letter-spacing: 0.5px;">
                                        <i class="fas fa-lock"></i> UPCOMING
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($items) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-scroll-old" style="font-size: 2.5rem; margin-bottom: 15px; display: block; color: var(--text-muted);"></i>
                        <h3>The quest board is currently empty.</h3>
                        <p>Click "Add Quest" to create a new challenge for today!</p>
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
                    <div style="font-size:0.9rem; color:var(--text-muted);">Define your daily challenge</div>
                </div>
            </div>

            <form action="habits/add_habit.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="title"><i class="fas fa-pen"></i> Quest Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Read 10 Pages..." required autocomplete="off">
                </div>

                <input type="hidden" name="page" value="Dashboard">

                <div class="form-group">
                    <label for="difficulty"><i class="fas fa-signal"></i> Difficulty Level</label>
                    <div class="select-wrapper">
                        <select id="difficulty" name="difficulty" required>
                            <option value="" disabled selected>Select Difficulty...</option>
                            <option value="Easy">Easy (Restores/Grants small stats)</option>
                            <option value="Medium">Medium (Balanced rewards)</option>
                            <option value="Hard">Hard (High rewards / risk)</option>
                        </select>
                    </div>
                </div>

                <input type="submit" value="Create Quest">
            </form>
        </div>
    </div>
    <script>
        const selectedDateStr = '<?php echo $selected_date; ?>';
    </script>
    <script src="assets/scripts/script.js"></script>

    <!-- Confetti explosion trigger -->
    <?php if (isset($_GET['confetti']) && $_GET['confetti'] === 'true'): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Main explosion
            confetti({
                particleCount: 140,
                spread: 75,
                origin: { y: 0.6 }
            });
            // Left burst
            setTimeout(() => {
                confetti({
                    particleCount: 60,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0, y: 0.7 }
                });
            }, 150);
            // Right burst
            setTimeout(() => {
                confetti({
                    particleCount: 60,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1, y: 0.7 }
                });
            }, 150);
        });
    </script>
    <?php endif; ?>

    <!-- Toast message notification -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="notification show" id="toast">
            <i class="fas fa-info-circle" style="color: var(--accent-gold);"></i>
            <span><?php echo htmlspecialchars($_GET['msg']); ?></span>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('toast');
                if (toast) {
                    toast.classList.remove('show');
                }
            }, 4000);
        </script>
    <?php endif; ?>

</body>

</html>