<?php
include 'config/DBconfig.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: Auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "success"; // 'success' or 'error'

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRF($_POST['csrf_token'] ?? '');
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Fetch current details including password hash
    $stmt = $conn->prepare("SELECT password, email FROM users WHERE uid = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_pass, $db_email);
    $stmt->fetch();
    $stmt->close();
    
    // Verify current password
    if (!password_verify($current_password, $db_pass)) {
        $msg = "Incorrect current password. Profile not updated.";
        $msg_type = "error";
    } elseif (empty($name) || empty($email)) {
        $msg = "Username and Email cannot be empty.";
        $msg_type = "error";
    } else {
        // Check if email is being changed and is already taken
        $email_taken = false;
        if ($email !== $db_email) {
            $stmt = $conn->prepare("SELECT uid FROM users WHERE email = ? AND uid != ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $email_taken = true;
            }
            $stmt->close();
        }
        
        if ($email_taken) {
            $msg = "Email is already in use by another account.";
            $msg_type = "error";
        } else {
            // Update details
            if (!empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $msg = "New passwords do not match.";
                    $msg_type = "error";
                } elseif (strlen($new_password) < 6) {
                    $msg = "New password must be at least 6 characters.";
                    $msg_type = "error";
                } else {
                    $hashed_pass = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE uid = ?");
                    $stmt->bind_param("sssi", $name, $email, $hashed_pass, $user_id);
                    if ($stmt->execute()) {
                        $msg = "Profile and password updated successfully!";
                        $_SESSION['user_name'] = $name;
                    } else {
                        $msg = "Failed to update profile. Please try again.";
                        $msg_type = "error";
                    }
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE uid = ?");
                $stmt->bind_param("ssi", $name, $email, $user_id);
                if ($stmt->execute()) {
                    $msg = "Profile updated successfully!";
                    $_SESSION['user_name'] = $name;
                } else {
                    $msg = "Failed to update profile. Please try again.";
                    $msg_type = "error";
                }
                $stmt->close();
            }
        }
    }
}

// Fetch user data again for display
$stmt = $conn->prepare("SELECT name, email, gold, hp, xp, level FROM users WHERE uid = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($u_name, $u_email, $u_gold, $u_hp, $u_xp, $u_level);
$stmt->fetch();
$stmt->close();

// Character title calculation
$char_title = "Novice Adventurer";
if ($u_level >= 20) {
    $char_title = "Legendary Master";
} elseif ($u_level >= 15) {
    $char_title = "Grand Magus";
} elseif ($u_level >= 10) {
    $char_title = "Elite Warrior";
} elseif ($u_level >= 5) {
    $char_title = "Experienced Scholar";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Momentum</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Main Styles -->
    <link rel="stylesheet" href="styles/styles.css">
    
    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
            padding-bottom: 40px;
        }
        @media (max-width: 900px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
        .profile-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 30px;
            box-shadow: var(--card-shadow);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .profile-header-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-avatar-circle {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 15px auto;
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 25px rgba(140, 122, 230, 0.3);
        }
        .profile-char-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: white;
            margin-bottom: 5px;
        }
        .profile-char-title {
            font-size: 0.9rem;
            color: var(--accent-gold);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .stat-details-list {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .stat-item-row {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .stat-item-header {
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .stat-progress-container {
            height: 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .stat-progress-bar {
            height: 100%;
            border-radius: 10px;
        }
        .profile-toast {
            position: fixed;
            top: 25px;
            right: 25px;
            padding: 18px 24px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 2000;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            animation: slideIn 0.3s ease-out forwards;
        }
        .profile-toast.success {
            background: rgba(46, 204, 113, 0.95);
            color: white;
        }
        .profile-toast.error {
            background: rgba(235, 77, 75, 0.95);
            color: white;
        }
        @keyframes slideIn {
            from { transform: translateX(120%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <?php $page = 5; include './components/sidebar.php'; ?>
    <main class="main-content">
        <?php include './components/header.php'; ?>
        
        <div class="dashboard-container">
            <div class="section-header" style="margin-bottom: 30px;">
                <h2><i class="fas fa-user-circle" style="color: var(--primary-light);"></i> Character Profile</h2>
            </div>
            
            <div class="profile-grid">
                <!-- Character Summary (RPG Info) -->
                <div class="profile-card">
                    <div class="profile-header-info">
                        <div class="profile-avatar-circle">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="profile-char-name"><?php echo htmlspecialchars($u_name); ?></div>
                        <div class="profile-char-title"><?php echo $char_title; ?></div>
                    </div>
                    
                    <div class="stat-details-list">
                        <div class="stat-item-row">
                            <div class="stat-item-header">
                                <span><i class="fas fa-heart" style="color: #ff7675;"></i> Health Points (HP)</span>
                                <span><?php echo $u_hp; ?> / 100</span>
                            </div>
                            <div class="stat-progress-container">
                                <div class="stat-progress-bar" style="width: <?php echo $u_hp; ?>%; background: linear-gradient(90deg, #ff7675, #d63031);"></div>
                            </div>
                        </div>

                        <div class="stat-item-row">
                            <div class="stat-item-header">
                                <span><i class="fas fa-star" style="color: var(--primary-light);"></i> Experience Points (XP)</span>
                                <span><?php echo $u_xp; ?> / <?php echo ($u_level * 100); ?></span>
                            </div>
                            <div class="stat-progress-container">
                                <?php $xp_percent = min(100, ($u_xp / ($u_level * 100)) * 100); ?>
                                <div class="stat-progress-bar" style="width: <?php echo $xp_percent; ?>%; background: linear-gradient(90deg, #a8c0ff, #3f2b96);"></div>
                            </div>
                        </div>

                        <div class="stat-item-row" style="margin-top: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255, 255, 255, 0.03); padding: 15px 20px; border-radius: 16px; border: 1px solid var(--border-color);">
                                <span style="font-weight: 700; color: var(--text-dark);"><i class="fas fa-medal" style="color: var(--accent-gold);"></i> Character Level</span>
                                <span style="font-weight: 800; color: var(--accent-gold); font-size: 1.2rem;">Lvl <?php echo $u_level; ?></span>
                            </div>
                        </div>

                        <div class="stat-item-row">
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255, 255, 255, 0.03); padding: 15px 20px; border-radius: 16px; border: 1px solid var(--border-color);">
                                <span style="font-weight: 700; color: var(--text-dark);"><i class="fas fa-coins" style="color: var(--accent-gold);"></i> Available Gold</span>
                                <span style="font-weight: 800; color: var(--accent-gold); font-size: 1.2rem;"><?php echo $u_gold; ?>g</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Settings Form -->
                <div class="profile-card">
                    <h3 style="margin-top: 0; margin-bottom: 25px; font-weight: 800; color: white;"><i class="fas fa-cog" style="color: var(--primary-light);"></i> Account Settings</h3>
                    
                    <form action="Profile.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="name" style="display: block; margin-bottom: 8px; font-weight: 800; color: var(--text-dark); font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Username</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($u_name); ?>" required placeholder="Adventurer Name" style="width: 100%; padding: 14px; border: 1px solid var(--border-color); border-radius: 14px; background: rgba(255,255,255,0.05); color: white; outline: none; font-size: 0.95rem; font-weight: 700;">
                        </div>

                        <div class="form-group" style="margin-bottom: 25px;">
                            <label for="email" style="display: block; margin-bottom: 8px; font-weight: 800; color: var(--text-dark); font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($u_email); ?>" required placeholder="alex@gmail.com" style="width: 100%; padding: 14px; border: 1px solid var(--border-color); border-radius: 14px; background: rgba(255,255,255,0.05); color: white; outline: none; font-size: 0.95rem; font-weight: 700;">
                        </div>

                        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 30px 0;">
                        
                        <h4 style="margin-top: 0; margin-bottom: 15px; font-weight: 800; color: white; font-size: 1rem;"><i class="fas fa-key" style="color: var(--accent-gold);"></i> Password & Verification</h4>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="new_password" style="display: block; margin-bottom: 8px; font-weight: 800; color: var(--text-dark); font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">New Password (Optional)</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current" style="width: 100%; padding: 14px; border: 1px solid var(--border-color); border-radius: 14px; background: rgba(255,255,255,0.05); color: white; outline: none; font-size: 0.95rem; font-weight: 700;">
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="confirm_password" style="display: block; margin-bottom: 8px; font-weight: 800; color: var(--text-dark); font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" style="width: 100%; padding: 14px; border: 1px solid var(--border-color); border-radius: 14px; background: rgba(255,255,255,0.05); color: white; outline: none; font-size: 0.95rem; font-weight: 700;">
                        </div>

                        <div class="form-group" style="margin-bottom: 25px;">
                            <label for="current_password" style="display: block; margin-bottom: 8px; font-weight: 800; color: var(--accent-gold); font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Current Password (Required to Save)</label>
                            <input type="password" id="current_password" name="current_password" required placeholder="Enter current password" style="width: 100%; padding: 14px; border: 1px solid rgba(241, 196, 15, 0.2); border-radius: 14px; background: rgba(241, 196, 15, 0.03); color: white; outline: none; font-size: 0.95rem; font-weight: 700;">
                        </div>

                        <input type="submit" value="Save Character Changes" style="width: 100%; padding: 16px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; border-radius: 14px; color: white; font-weight: 800; font-size: 1rem; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 15px rgba(140, 122, 230, 0.2);">
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast message notification -->
    <?php if (!empty($msg)): ?>
        <div class="profile-toast <?php echo $msg_type; ?>" id="toast">
            <i class="<?php echo $msg_type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'; ?>"></i>
            <span><?php echo htmlspecialchars($msg); ?></span>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('toast');
                if (toast) {
                    toast.style.animation = 'none';
                    toast.offsetHeight; /* trigger reflow */
                    toast.style.transition = 'opacity 0.5s ease';
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 500);
                }
            }, 4000);
        </script>
    <?php endif; ?>
</body>
</html>
