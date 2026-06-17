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

// Fetch User Stats (Clean and up-to-date)
$stmt = $conn->prepare("SELECT gold, hp, name FROM users WHERE uid = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($gold, $hp, $name);
$stmt->fetch();
$stmt->close();

// Fetch Inventory Items (including type)
$stmt = $conn->prepare("
    SELECT 
        i.id as inventory_id,
        i.Quantity,
        COALESCE(s.title, cs.title) AS title,
        COALESCE(s.difficulty, cs.difficulty) AS difficulty,
        COALESCE(s.xp_reward, cs.xp_reward) AS xp_reward,
        COALESCE(s.type, cs.type) AS type
    FROM inventory i
    LEFT JOIN shop s ON i.sid = s.id
    LEFT JOIN customshop cs ON i.cid = cs.id
    WHERE i.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$inventory_result = $stmt->get_result();
$inventory_items = $inventory_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Momentum</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="styles/styles.css">
    
    <style>
        .btn-use {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: #fff;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            font-weight: 800;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.2);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        .btn-use:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 15px rgba(46, 204, 113, 0.3); 
        }
        .btn-use:active { 
            transform: translateY(1px); 
            box-shadow: none; 
        }

        .qty-tag {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid var(--border-color);
            font-weight: 800;
        }
    </style>
</head>

<body>
    
    <?php $page = 4; include './components/sidebar.php'; ?>

    <main class="main-content">
        <!-- Header -->
        <?php include './components/header.php'; ?>

        <div class="dashboard-container">
            
            <!-- Section Header -->
            <div class="section-header" style="margin-bottom: 30px;">
                <h2><i class="fas fa-box-open" style="color: var(--primary-light);"></i> Player Inventory</h2>
                
                <a href="Shop.php" class="ai-brief-btn">
                    <i class="fas fa-store"></i> Visit Shop
                </a>
            </div>

            <!-- Inventory Grid -->
            <div class="course-list"> 
                
                <?php foreach ($inventory_items as $item): 
                    $rarityClass = 'common'; 
                    if (strtolower($item['difficulty']) == 'medium') $rarityClass = 'rare';
                    if (strtolower($item['difficulty']) == 'hard') $rarityClass = 'epic';

                    // Set icon and label dynamically
                    $icon = 'fa-gift';
                    $typeLabel = 'Reward';
                    if ($item['type'] === 'potion') {
                        $icon = 'fa-flask';
                        $typeLabel = 'Potion';
                    } elseif ($item['type'] === 'scroll') {
                        $icon = 'fa-scroll';
                        $typeLabel = 'Scroll';
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
                                    <span><i class="fas fa-tag"></i> <?php echo $typeLabel; ?></span>
                                    <span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars(ucfirst($item['difficulty'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="quest-rewards">
                            <div class="loot-tag xp-tag">
                                <?php if ($item['type'] === 'potion'): ?>
                                    <i class="fas fa-heart" style="color: var(--accent-red);"></i> Restores <?php echo htmlspecialchars($item['xp_reward']); ?> HP
                                <?php elseif ($item['type'] === 'scroll'): ?>
                                    <i class="fas fa-star" style="color: var(--primary-light);"></i> Grants <?php echo htmlspecialchars($item['xp_reward']); ?> XP
                                <?php else: ?>
                                    <i class="fas fa-gift"></i> Custom
                                <?php endif; ?>
                            </div>
                            <div class="loot-tag qty-tag">
                                x<?php echo htmlspecialchars($item['Quantity']); ?> Owned
                            </div>
                        </div>

                        <div class="action-row" style="width: 100%;">
                            <form action="Rewards/use_item.php" method="POST" style="width: 100%;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="inventory_id" value="<?php echo $item['inventory_id']; ?>">
                                <button type="submit" class="btn-use">
                                    Use Item
                                </button>
                            </form>
                        </div>
                    </div>

                <?php 
                endforeach; 
                ?>

                <!-- Empty State -->
                <?php if (count($inventory_items) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open" style="font-size: 3.5rem; margin-bottom: 15px; color: var(--text-muted); display: block;"></i>
                        <h3>Your inventory is empty.</h3>
                        <p>Complete quests to earn gold and buy items from the apothecary shop!</p>
                        <br>
                        <a href="Shop.php" class="btn-use" style="max-width: 200px; margin: 0 auto;">Go to Shop</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

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