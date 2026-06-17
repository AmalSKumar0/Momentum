<?php
$stmt = $conn->prepare("SELECT gold, hp, xp, level, name FROM users WHERE uid = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($gold, $hp, $xp, $level, $name);
$stmt->fetch();
$stmt->close();

if ($hp == 0 && $page < 3) {
    header("Location: died.php");
    exit();
}
?>

<link rel="stylesheet" href="styles/header.css">

<style>
/* Header status bar styling */
.player-status-container {
    display: flex;
    align-items: center;
    gap: 20px;
    font-family: 'Nunito', 'Poppins', sans-serif;
}
.player-status-bar-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.status-label {
    font-weight: 800;
    font-size: 0.9rem;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
    color: var(--text-dark);
}
.xp-ratio-text {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-weight: 700;
    white-space: nowrap;
}
.status-bar-container {
    width: 140px;
    height: 12px;
    background-color: var(--bar-bg, rgba(0, 0, 0, 0.1));
    border: 1.5px solid var(--bar-border, rgba(0, 0, 0, 0.15));
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}
.status-bar-fill {
    height: 100%;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.health-bar .status-bar-fill {
    background: linear-gradient(90deg, #ff7675, #d63031);
    box-shadow: 0 0 6px rgba(255, 118, 117, 0.4);
}
.xp-bar .status-bar-fill {
    background: linear-gradient(90deg, #6c5ce7, #a29bfe);
    box-shadow: 0 0 6px rgba(108, 92, 231, 0.4);
}
.gold-box {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--gold-box-bg, rgba(255, 255, 255, 0.6));
    padding: 6px 14px;
    border-radius: 12px;
    border: 1.5px solid var(--gold-box-border, rgba(0, 0, 0, 0.05));
    font-weight: 800;
    font-size: 0.95rem;
    box-shadow: var(--gold-box-shadow, 0 4px 10px rgba(0,0,0,0.02));
    color: var(--text-dark);
}

@media (max-width: 768px) {
    .player-status-container {
        gap: 10px;
    }
    .status-bar-container {
        width: 80px;
        height: 10px;
    }
    .xp-ratio-text {
        display: none;
    }
    .status-label {
        font-size: 0.75rem;
    }
}
</style>

<header class="header">
    <div class="header-left">
        <i class="fas fa-bars menu-btn" onclick="toggleSidebar()"></i>
        <div class="header-greeting">
            <h1>Hi, <span><?php echo htmlspecialchars(strtoupper($name)); ?>!</span></h1>
        </div>
    </div>

    <div class="player-status-container">
        <!-- Health Bar -->
        <div class="player-status-bar-group">
            <span class="status-label"><i class="fas fa-heart" style="color:#ff7675;"></i> <?php echo $hp; ?>/100</span>
            <div class="status-bar-container health-bar">
                <div class="status-bar-fill" style="width: <?php echo $hp; ?>%;"></div>
            </div>
        </div>

        <!-- Level & XP Bar -->
        <div class="player-status-bar-group">
            <span class="status-label"><i class="fas fa-star" style="color:#6c5ce7;"></i> LVL <?php echo $level; ?></span>
            <div class="status-bar-container xp-bar">
                <?php 
                    $max_xp = $level * 100;
                    $xp_percent = min(100, ($xp / $max_xp) * 100);
                ?>
                <div class="status-bar-fill" style="width: <?php echo $xp_percent; ?>%;"></div>
            </div>
            <span class="xp-ratio-text"><?php echo $xp; ?>/<?php echo $max_xp; ?> XP</span>
        </div>

        <!-- Gold Box -->
        <div class="gold-box">
            <span>🪙 <?php echo number_format($gold); ?></span>
        </div>
    </div>
</header>