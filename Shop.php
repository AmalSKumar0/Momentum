<?php
include 'config/DBconfig.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch User Stats for Header / Check
$stmt = $conn->prepare("SELECT gold, hp, name FROM users WHERE uid = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($gold, $hp, $name);
$stmt->fetch();
$stmt->close();

// Fetch Standard Shop Items
$sql = "SELECT * FROM shop";
$stmt = $conn->query($sql);
$shop_items = $stmt->fetch_all(MYSQLI_ASSOC);

// Fetch Custom Shop Items
$sql_custom = "SELECT * FROM customshop WHERE user_id = ?";
$stmt_custom = $conn->prepare($sql_custom);
$stmt_custom->bind_param("i", $user_id);
$stmt_custom->execute();
$result_custom = $stmt_custom->get_result();
$custom_items = $result_custom->fetch_all(MYSQLI_ASSOC);
$stmt_custom->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apothecary & Shop - Momentum</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .item-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 4px 6px 12px 6px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    
    <?php $page = 3; include './components/sidebar.php'; ?>

    <main class="main-content">
        <!-- Header -->
        <?php include './components/header.php'; ?>

        <div class="dashboard-container">
            
            <!-- Section Header -->
            <div class="section-header" style="margin-bottom: 30px;">
                <h2><i class="fas fa-store" style="color: var(--primary-light);"></i> Apothecary & Shop</h2>
                
                <div style="display: flex; gap: 10px;">
                    <button onclick="toggleModal()" class="ai-brief-btn">
                        <i class="fas fa-plus"></i> Add Custom Reward
                    </button>
                </div>
            </div>

            <!-- Shop Grid -->
            <div class="course-list">
                
                <!-- 1. Standard Apothecary Shop Items -->
                <?php foreach ($shop_items as $item): ?>
                    <?php 
                        $rarityClass = 'common'; 
                        if (strtolower($item['difficulty']) == 'medium') $rarityClass = 'rare';
                        if (strtolower($item['difficulty']) == 'hard') $rarityClass = 'epic';

                        // Set item icons
                        $icon = 'fa-flask';
                        if ($item['type'] === 'scroll') {
                            $icon = 'fa-scroll';
                        } elseif ($item['type'] === 'gear') {
                            $icon = 'fa-shield-alt';
                        }
                    ?>
                    
                    <div class="quest-card <?php echo $rarityClass; ?>">
                        <div class="quest-header">
                            <div class="icon-box">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="quest-info">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="quest-meta">
                                    <span><i class="fas fa-tag"></i> Apothecary</span>
                                    <span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars(ucfirst($item['difficulty'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="item-desc">
                            <?php echo htmlspecialchars($item['description']); ?>
                        </div>

                        <div class="quest-rewards">
                            <div class="loot-tag xp-tag">
                                <?php if ($item['type'] === 'potion'): ?>
                                    <i class="fas fa-heart" style="color: var(--accent-red);"></i> +<?php echo htmlspecialchars($item['xp_reward']); ?> HP
                                <?php else: ?>
                                    <i class="fas fa-star" style="color: var(--primary-light);"></i> +<?php echo htmlspecialchars($item['xp_reward']); ?> XP
                                <?php endif; ?>
                            </div>
                            <div class="loot-tag cost-tag">
                                <i class="fas fa-coins" style="color: var(--accent-gold);"></i> <?php echo htmlspecialchars($item['gold_cost']); ?> Gold
                            </div>
                        </div>

                        <div class="action-row" style="width: 100%;">
                            <form action="Rewards/buy_rewards.php" method="POST" style="width: 100%;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="type" value="shop">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-buy">
                                    Buy Item
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- 2. Custom User Rewards -->
                <?php foreach ($custom_items as $item): ?>
                    <?php 
                        $rarityClass = 'common';
                        if (strtolower($item['difficulty']) == 'medium') $rarityClass = 'rare';
                        if (strtolower($item['difficulty']) == 'hard') $rarityClass = 'epic';
                    ?>

                    <div class="quest-card <?php echo $rarityClass; ?>">
                        <div class="quest-header">
                            <div class="icon-box">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="quest-info">
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="quest-meta">
                                    <span><i class="fas fa-user-tag"></i> Custom Reward</span>
                                </div>
                            </div>
                        </div>

                        <div class="item-desc">
                            Custom reward redeemed directly from your inventory.
                        </div>

                        <div class="quest-rewards">
                            <div class="loot-tag xp-tag">
                                <i class="fas fa-star" style="color: var(--primary-light);"></i> Custom Effect
                            </div>
                            <div class="loot-tag cost-tag">
                                <i class="fas fa-coins" style="color: var(--accent-gold);"></i> <?php echo htmlspecialchars($item['gold_cost']); ?> Gold
                            </div>
                        </div>

                        <div class="action-row" style="width: 100%;">
                            <form action="Rewards/buy_rewards.php" method="POST" style="width: 100%;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="type" value="custom">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-buy">
                                    Redeem
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Empty State -->
                <?php if (count($shop_items) === 0 && count($custom_items) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-ghost" style="font-size: 3rem; margin-bottom: 15px; color: var(--text-muted);"></i>
                        <h3>The shop is currently empty.</h3>
                        <p>Check back later or add your own custom rewards!</p>
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
                    <h2 style="margin:0; color:var(--text-dark);">New Custom Reward</h2>
                    <div style="font-size:0.9rem; color:var(--text-muted);">Define your custom purchase</div>
                </div>
            </div>

            <form action="Rewards/add_reward.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="title"><i class="fas fa-pen"></i> Reward Name</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Watch 1 Episode of Anime..." required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="gold_cost"><i class="fas fa-coins"></i> Gold Cost</label>
                    <input type="number" id="gold_cost" name="gold_cost" min="10" placeholder="e.g. 50" required>
                </div>

                <input type="submit" value="Create Custom Reward">
            </form>
        </div>
    </div>
    <script src="assets/scripts/script.js"></script>

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