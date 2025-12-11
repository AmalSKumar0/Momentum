<?php
include 'config/DBconfig.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Auth/login.php");
}
$stmt = $conn->prepare("SELECT gold,xp,name FROM users WHERE uid = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($gold, $xp, $name);
$stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Over - Momentum</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/gameover.css">
</head>
<body>

    <div class="death-card">
        <div class="icon-container">
            <i class="fas fa-heart-broken" style="color: #ff7675;"></i>
        </div>

        <h1>Game Over!</h1>
        <p>Your health has reached zero. Don't give up! Respawn now to keep your streak alive.</p>

        <div class="stats-row">
            <div class="stat-badge">
                <i class="fas fa-trophy"></i> XP <?php echo $xp; ?>
            </div>
            <div class="stat-badge">
                <i class="fas fa-coins"></i> <?php echo $gold; ?> Coins
            </div>
        </div>

        <!-- Action Form -->
        <form action="Shop.php" method="POST">
            <button type="submit" class="btn-respawn">
                <i class="fas fa-redo"></i> Buy Potion
            </button>
        </form>

        <a href="Dashboard.php" class="link-home">Back to Main Menu</a>
    </div>

</body>
</html>