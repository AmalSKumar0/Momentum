<?php
include 'config/DBconfig.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: Auth/login.php");
    exit();
}
$stmt = $conn->prepare("SELECT gold, hp, level, name FROM users WHERE uid = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($gold, $hp, $level, $name);
$stmt->fetch();
$stmt->close();

if ($hp > 0) {
    header("Location: Dashboard.php");
    exit();
}
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
            <i class="fas fa-heart-broken"></i>
        </div>

        <h1>Game Over!</h1>
        <p>Your health has reached zero. Don't give up! Visit the apothecary shop to buy a Health Potion and respawn.</p>

        <div class="stats-row">
            <div class="stat-badge">
                <i class="fas fa-trophy" style="color: var(--primary-light);"></i> Level <?php echo $level; ?>
            </div>
            <div class="stat-badge">
                <i class="fas fa-coins" style="color: var(--gold);"></i> <?php echo number_format($gold); ?> Coins
            </div>
        </div>

        <!-- Action Form -->
        <form action="Shop.php" method="GET">
            <button type="submit" class="btn-respawn">
                <i class="fas fa-store"></i> Visit Shop
            </button>
        </form>

        <a href="Dashboard.php" class="link-home">Back to Menu</a>
    </div>

</body>
</html>