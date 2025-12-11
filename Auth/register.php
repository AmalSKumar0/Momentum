<?php
include '../config/DBconfig.php';
session_start();
$err = "";
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if($stmt->execute()){
        header("Location: login.php");
        exit();
    } else {
        $err = "Error: " . $stmt->error;
        
    }

    $stmt->close();
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
        </div>
    </section>

</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <?php if($err): ?>
        <p style="color:red;"><?php echo $err; ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="username">Username:</label>
        <br><br>
        <label for="email">Email id:</label>
        <input type="text" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Register">
    </form>
</body>
</html>