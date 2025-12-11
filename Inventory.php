<?php
include 'config/DBconfig.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Fetch User Stats
$stmt = $conn->prepare("SELECT gold, xp, name FROM users WHERE uid = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($gold, $xp, $name);
$stmt->fetch();
$stmt->close();

// 2. Fetch Inventory Items
$stmt = $conn->prepare("SELECT * FROM inventory WHERE user_id = ?");
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
        /* Inventory Specific Overrides */
        .btn-use {
            background: #20bf6b; /* Green for 'Use' action */
            color: #fff;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            font-weight: 800;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 4px 0 #009432;
            transition: all 0.1s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        .btn-use:hover { transform: translateY(-2px); box-shadow: 0 6px 0 #009432; color: white; }
        .btn-use:active { transform: translateY(4px); box-shadow: none; }

        /* Quantity Tag */
        .qty-tag {
            background: #2d3436;
            color: white;
            font-weight: 800;
        }
        
        .xp-tag {
            background: #e0dcfc;
            color: #6c5ce7;
            font-weight: 800;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 20px;
            color: #636e72;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }

        /* --- NOTIFICATION TOAST --- */
        .notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #2d3436;
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .notification.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .notification i { color: #20bf6b; }
    </style>
</head>

<body>
    
    <!-- Sidebar -->
    <?php $page = 4; // Highlight Inventory in sidebar
    include './components/sidebar.php'; ?>

    <main class="main-content">
        <!-- Header -->
        <?php include './components/header.php'; ?>

        <div class="dashboard-container">
            
            <!-- Section Header -->
            <div class="section-header" style="margin-bottom: 30px;">
                <h2 style="font-size: 1.5rem; color: #2d3436;"><i class="fas fa-box-open" style="color:#6c5ce7;"></i> Your Inventory</h2>
                
                <a href="Shop.php" class="ai-brief-btn" style="text-decoration:none; background:#6c5ce7; color:white; padding:10px 20px; border-radius:15px; font-weight:bold; box-shadow:0 4px 10px rgba(108, 92, 231, 0.3); display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-store"></i> Visit Shop
                </a>
            </div>

            <!-- Inventory Grid -->
            <div class="course-list"> 
                
                <?php foreach ($inventory_items as $item): 
                    // Fetch Details logic
                    $itemDetails = null;
                    if($item['sid']){
                        $sql = "SELECT * FROM shop WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $item['sid']);
                    } else if($item['cid']){
                        $sql = "SELECT * FROM customshop WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $item['cid']);
                    }
                    
                    if(isset($stmt)){
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $itemDetails = $res->fetch_assoc();
                        $stmt->close();
                    }

                    if($itemDetails):
                        // Determine visual style based on difficulty
                        $rarityClass = 'common'; 
                        if (strtolower($itemDetails['difficulty']) == 'medium') $rarityClass = 'rare';
                        if (strtolower($itemDetails['difficulty']) == 'hard') $rarityClass = 'epic';
                ?>
                    
                    <div class="quest-card <?php echo $rarityClass; ?>">
                        <div class="quest-header">
                            <div class="icon-box">
                                <i class="fas fa-cube"></i>
                            </div>
                            <div class="quest-info">
                                <h3><?php echo htmlspecialchars($itemDetails['title']); ?></h3>
                                <div class="quest-meta">
                                    <span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($itemDetails['difficulty']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="quest-rewards">
                            <!-- XP and Quantity -->
                            <div class="loot-tag xp-tag">
                                <i class="fas fa-star"></i> <?php echo htmlspecialchars($itemDetails['xp_reward']); ?> XP Value
                            </div>
                            <div class="loot-tag qty-tag">
                                x<?php echo htmlspecialchars($item['Quantity']); ?> Owned
                            </div>
                        </div>

                        <div class="action-row">
                            <a href="Rewards/use_item.php?qty=<?php echo $item['Quantity']; ?>&xp=<?php echo $itemDetails['xp_reward']; ?>&inventory_id=<?php echo $item['id']; ?>" class="btn-use">
                                Use Item
                            </a>
                        </div>
                    </div>

                <?php 
                    endif; // End if itemDetails exists
                endforeach; 
                ?>

                <!-- Empty State -->
                <?php if (count($inventory_items) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; color: #b2bec3;"></i>
                        <h3>Your inventory is empty.</h3>
                        <p>Complete quests to earn gold and buy items from the shop!</p>
                        <br>
                        <a href="Shop.php" class="btn-use" style="max-width: 200px; margin: 0 auto; background: #6c5ce7; box-shadow: 0 4px 0 #5849be;">Go to Shop</a>
                    </div>
                <?php endif; ?>

            </div> <!-- End Grid -->
        </div>
    </main>

    <!-- Notification Element -->
    <div id="notification" class="notification">
        <i class="fas fa-check-circle"></i> 
        <span id="notif-message">Notification</span>
    </div>

    <!-- Notification Logic -->
    <script>
        function showNotification(msg) {
            const notif = document.getElementById('notification');
            const msgSpan = document.getElementById('notif-message');
            
            msgSpan.textContent = msg;
            notif.classList.add('show');

            setTimeout(() => {
                notif.classList.remove('show');
            }, 2000);
        }

        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const msg = urlParams.get('msg');
            
            if (msg) {
                showNotification(msg);
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({path: newUrl}, '', newUrl);
            }
        });
    </script>

</body>
</html>