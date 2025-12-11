<?php
include 'config/DBconfig.php';
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: Dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Momentum - Gamified Productivity</title>
    <!-- Google Font: Poppins & Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/home.css">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="brand-logo">
            <div class="brand-icon"><i class="fas fa-gamepad"></i></div>
            Momentum
        </div>
        <a href="Auth/register.php" class="nav-btn">
            Join us
        </a>
    </nav>

    <!-- HERO SECTION -->
    <header class="hero-section">
        <div class="hero-badge">
            <i class="fas fa-bolt"></i> 
            Be more productive while gaming!!
        </div>
        
        <h1 class="hero-title">
            Level Up Your Life<br>
            With <span>Epic Habits</span>
        </h1>
        
        <p class="hero-subtitle">
            Stop checking off boring to-do lists. Start completing quests, earning XP, and unlocking rewards in the real world.
        </p>
        
        <div class="cta-group">
            <a href="Auth/login.php" class="cta-btn">
                Login 
            </a>
            <a href="Auth/register.php" class="secondary-btn">
                Sign Up
            </a>
        </div>

        <!-- Optional: Feature Highlights below hero -->
        <div class="features-grid">
            <div class="feature-card">
                <div class="f-icon" style="background:#e0dcfc; color:#6c5ce7;"><i class="fas fa-scroll"></i></div>
                <h3>Daily Quests</h3>
                <p style="color:#636e72; font-size:0.9rem;">Turn habits into daily missions.</p>
            </div>
            <div class="feature-card">
                <div class="f-icon" style="background:#fff4d9; color:#feca57;"><i class="fas fa-coins"></i></div>
                <h3>Earn Loot</h3>
                <p style="color:#636e72; font-size:0.9rem;">Get gold for your consistency.</p>
            </div>
            <div class="feature-card">
                <div class="f-icon" style="background:#dff9fb; color:#00b894;"><i class="fas fa-chart-line"></i></div>
                <h3>Track Stats</h3>
                <p style="color:#636e72; font-size:0.9rem;">Watch your level grow.</p>
            </div>
        </div>
    </header>

</body>
</html>