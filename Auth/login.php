<?php
include '../config/DBconfig.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$err = "";
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT uid,name,email,password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['uid'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: ../Dashboard.php");
            exit();
        }
    }
    $err = "Invalid email or password.";
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Momentum Login</title>
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
            <p class="login-subtitle" <?php if($err){echo "style='color:red;'"; } ?>><?php if($err){echo $err; }else echo "Login to enter the Realm of Productivity"?></p>
            
            <form id="loginForm"  method="POST">
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
                Don't have an account? <a href="register.php" style="color: var(--primary-light); font-weight: 700;">Sign Up</a>
            </p>
        </div>
    </section>

</body>
</html>