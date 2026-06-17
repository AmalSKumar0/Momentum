<?php
include '../config/DBconfig.php';
// Session start is automatically called in DBconfig.php
if(!isset($_SESSION['user_id'])){
    header("Location: ../Auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['inventory_id'])){
    validateCSRF($_POST['csrf_token'] ?? '');
    
    $inventory_id = intval($_POST['inventory_id']);

    $conn->begin_transaction();
    try {
        // 1. Fetch item details, item type, and quantity
        $stmt = $conn->prepare("
            SELECT 
                i.Quantity,
                COALESCE(s.type, cs.type) AS type,
                COALESCE(s.xp_reward, cs.xp_reward) AS reward_value,
                COALESCE(s.title, cs.title) AS title
            FROM inventory i
            LEFT JOIN shop s ON i.sid = s.id
            LEFT JOIN customshop cs ON i.cid = cs.id
            WHERE i.id = ? AND i.user_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param("ii", $inventory_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();

        if (!$item) {
            handleError("Item not found in your inventory.");
        }

        $type = $item['type'] ?? 'custom';
        $reward_value = intval($item['reward_value']);
        $qty = intval($item['Quantity']);
        $title = $item['title'];

        // 2. Fetch user stats
        $stmt = $conn->prepare("SELECT hp, xp, level FROM users WHERE uid = ? FOR UPDATE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $hp = intval($user['hp']);
        $xp = intval($user['xp']);
        $level = intval($user['level']);
        $stmt->close();

        $msg = "Used " . $title . "!";
        $did_level_up = false;

        // 3. Process item effect
        if ($type === 'potion') {
            if ($hp >= 100) {
                $conn->rollback();
                $conn->close();
                header("Location: ../Inventory.php?msg=" . urlencode("Your health is already full!"));
                exit();
            }
            $hp = min(100, $hp + $reward_value);
            $msg = "Healed! Restored " . $reward_value . " HP.";
        } elseif ($type === 'scroll') {
            $xp += $reward_value;
            $msg = "Consumed scroll! Gained " . $reward_value . " XP.";
            while ($xp >= $level * 100) {
                $xp -= $level * 100;
                $level++;
                $hp = min(100, $hp + 25);
                $did_level_up = true;
            }
            if ($did_level_up) {
                $msg .= " Level Up! You are now Level " . $level . "!";
            }
        } else {
            // Custom item just consumed
            $msg = "Redeemed reward: " . $title;
        }

        // 4. Update user stats
        $stmt = $conn->prepare("UPDATE users SET hp = ?, xp = ?, level = ? WHERE uid = ?");
        $stmt->bind_param("iiii", $hp, $xp, $level, $user_id);
        $stmt->execute();
        $stmt->close();

        // 5. Update or delete inventory item
        if ($qty > 1) {
            $stmt = $conn->prepare("UPDATE inventory SET Quantity = Quantity - 1 WHERE id = ? AND user_id = ?");
        } else {
            $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ? AND user_id = ?");
        }
        $stmt->bind_param("ii", $inventory_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $conn->close();
        
        header("Location: ../Inventory.php?msg=" . urlencode($msg));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        handleError("Failed to use item: " . $e->getMessage());
    }
}
header("Location: ../Inventory.php");
exit();
?>