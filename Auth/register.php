<?php
include '../config/DBconfig.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$err = "";
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    }

    if (empty($err)) {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT uid FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $err = "This email address is already registered.";
        }
        $check_stmt->close();
    }

    if (empty($err)) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if($stmt->execute()){
            header("Location: login.php");
            exit();
        } else {
            $err = "Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Momentum Signup</title>
    <!-- Google Font: Poppins & Nunito (Rounder for games) -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <section id="login-screen">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-gamepad"></i>
            </div>
            <h1 class="login-title">Momentum</h1>
            <p class="login-subtitle" <?php if($err){echo "style='color:red;'"; } ?>><?php if($err){echo $err; }else echo "Signup to enter the Realm of Productivity"?></p>
            
            <form id="loginForm"  method="POST">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" id="username" class="form-input" placeholder="e.g. alexthegame" name="username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-input" placeholder="e.g. alex@game.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" placeholder="************" name="password" class="form-input">
                </div>
                <button type="submit" class="btn-login">
                    Start Adventure <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            <p style="margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary-light); font-weight: 700;">Login</a>
            </p>
        </div>
    </section>

</body>
</html>