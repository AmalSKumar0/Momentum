<?php
include 'config/DBconfig.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch User Stats for Header
$stmt = $conn->prepare("SELECT gold, xp, name FROM users WHERE uid = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($gold, $xp, $name);
$stmt->fetch();
$stmt->close();

// Fetch Standard Shop Items
$sql = "SELECT * FROM shop";
$stmt = $conn->query($sql);
$shop_items = $stmt->fetch_all(MYSQLI_ASSOC);

// Fetch Custom Shop Items
$sql_custom = "SELECT * FROM customshop WHERE user_id = $user_id";
$stmt_custom = $conn->query($sql_custom);
$custom_items = $stmt_custom->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Shop - Momentum</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="styles/styles.css">
    
</head>

<body>
    
    <!-- Sidebar -->

    <?php $page = 3; include './components/sidebar.php'; ?>

    <main class="main-content">
        <!-- Header -->
        <?php include './components/header.php'; ?>

        <div class="dashboard-container">
            
            <!-- Section Header -->
            <div class="section-header" style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5rem; color: #2d3436;"><i class="fas fa-store" style="color:#6c5ce7;"></i> Rewards Shop</h2>
                
                <div style="display: flex; gap: 10px;">
                    <button onclick="toggleModal()" class="ai-brief-btn" style="text-decoration:none; background:#6c5ce7; color:white; padding:10px 20px; border-radius:15px; font-weight:bold; box-shadow:0 4px 10px rgba(108, 92, 231, 0.3); display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-plus"></i> Add Custom
                    </button>
                </div>
            </div>

            <!-- Shop Grid -->
            <div class="course-list"> <!-- Using existing grid class from dashboard -->
                
                <!-- 1. Standard Shop Items -->
                <?php foreach ($shop_items as $item): ?>
                    <?php 
                        // Determine visual style based on difficulty/rarity
                        $rarityClass = 'common'; // Default (Cyan)
                        if (strtolower($item['difficulty']) == 'medium') $rarityClass = 'rare'; // Yellow
                        if (strtolower($item['difficulty']) == 'hard') $rarityClass = 'epic'; // Purple
                    ?>
                    
                    <div class="quest-card <?php echo $rarityClass; ?>">
                        <div class="quest-header">
                            <div class="icon-box">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="quest-info">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="quest-meta">
                                    <span><i class="fas fa-tag"></i> Shop Item</span>
                                    <span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($item['difficulty']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="quest-rewards">
                            <!-- Rewards/Cost Display -->
                            <div class="loot-tag xp-tag">
                                <i class="fas fa-star"></i> +<?php echo htmlspecialchars($item['xp_reward']); ?> XP
                            </div>
                            <div class="loot-tag cost-tag">
                                <i class="fas fa-coins"></i> -<?php echo htmlspecialchars($item['gold_cost']); ?> Gold
                            </div>
                        </div>

                        <div class="action-row">
                            <a href="Rewards/buy_rewards.php?type=shop&id=<?php echo $item['id']; ?>" class="btn-buy">
                                Buy Item
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- 2. Custom User Items -->
                <?php foreach ($custom_items as $item): ?>
                    <?php 
                        $rarityClass = 'common';
                        if (strtolower($item['difficulty']) == 'medium') $rarityClass = 'rare';
                        if (strtolower($item['difficulty']) == 'hard') $rarityClass = 'epic';
                    ?>

                    <div class="quest-card <?php echo $rarityClass; ?>">
                        <div class="quest-header">
                            <div class="icon-box">
                                <i class="fas fa-user-astronaut"></i>
                            </div>
                            <div class="quest-info">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="quest-meta">
                                    <span><i class="fas fa-user-tag"></i> Custom Reward</span>
                                </div>
                            </div>
                        </div>

                        <div class="quest-rewards">
                            <div class="loot-tag xp-tag">
                                <i class="fas fa-star"></i> +<?php echo htmlspecialchars($item['xp_reward']); ?> XP
                            </div>
                            <div class="loot-tag cost-tag">
                                <i class="fas fa-coins"></i> -<?php echo htmlspecialchars($item['gold_cost']); ?> Gold
                            </div>
                        </div>

                        <div class="action-row">
                            <a href="Rewards/buy_rewards.php?type=custom&cost=<?php echo $item['gold_cost']; ?>&id=<?php echo $item['id']; ?>" class="btn-buy">
                                Redeem
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Empty State -->
                <?php if (count($shop_items) === 0 && count($custom_items) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-ghost" style="font-size: 3rem; margin-bottom: 15px; color: #b2bec3;"></i>
                        <h3>The shop is currently empty.</h3>
                        <p>Check back later or add your own custom rewards!</p>
                    </div>
                <?php endif; ?>

            </div> <!-- End Grid -->
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
                    <h2 style="margin:0; color:#2d3436;">New Reward</h2>
                    <div style="font-size:0.9rem; color:#636e72;">Define your Reward</div>
                </div>
            </div>

            <form action="Rewards/add_reward.php" method="POST">
                <div class="form-group">
                    <label for="title"><i class="fas fa-pen"></i> Reward Title</label>
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

    <!-- Notification Element -->
    <div id="notification" class="notification">
        <i class="fas fa-info-circle"></i> 
        <span id="notif-message">Notification</span>
    </div>

    <!-- Notification Logic -->
    <script>
        function showNotification(msg) {
            const notif = document.getElementById('notification');
            const msgSpan = document.getElementById('notif-message');
            
            msgSpan.textContent = msg;
            notif.classList.add('show');

            // Hide after 2 seconds
            setTimeout(() => {
                notif.classList.remove('show');
            }, 2000);
        }

        // Check for URL parameter 'msg' (e.g. shop.php?msg=Purchase%20Successful)
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const msg = urlParams.get('msg');
            
            if (msg) {
                showNotification(msg);
                
                // Optional: Clean URL
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({path: newUrl}, '', newUrl);
            }
        });
    </script>

</body>
</html>