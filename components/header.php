<?php
$stmt = $conn->prepare("SELECT gold,xp,name FROM users WHERE uid = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($gold, $xp, $name);
$stmt->fetch();
if ($xp == 0 && $page<3) {
    header("Location: died.php");
    exit();
}
?>
<?php
echo "<style> .life-fill {
            width: " . $xp . "%;
        } </style>";
?>
<link rel="stylesheet" href="styles/header.css">

<header class="header">
    <div class="header-left">
        <i class="fas fa-bars menu-btn" onclick="toggleSidebar()"></i>
        
        <div class="header-greeting">
            <h1>Hi, <span><?php echo htmlspecialchars(strtoupper($name)); ?>!</span></h1>
        </div>
    </div>

    <div class="player-box">
        <span class="player-text">❤️ <?php echo $xp; ?>/100</span>

        <div class="bar-box">
            <div class="health-bar-container">
                <div class="health-bar-fill" style="width: <?php echo $xp; ?>%;"></div>
            </div>
        </div>

        <div class="stat-box">
            🪙 <?php echo number_format($gold); ?> </div>
    </div>
</header>