<?php
include '../config/DBconfig.php';
// Session start is automatically called in DBconfig.php
if(!isset($_SESSION['user_id'])){
    header("Location: ../Auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])){
    validateCSRF($_POST['csrf_token'] ?? '');
    
    $id = intval($_POST['id']);
    $type = $_POST['type'] ?? '';

    if ($type !== 'shop' && $type !== 'custom') {
        handleError("Invalid reward type.");
    }

    $conn->begin_transaction();
    try {
        // 1. Fetch item cost from database
        $cost = 0;
        if ($type === 'shop') {
            $stmt = $conn->prepare("SELECT gold_cost FROM shop WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $cost = intval($row['gold_cost']);
            } else {
                handleError("Item not found in shop.");
            }
            $stmt->close();
        } else { // custom
            $stmt = $conn->prepare("SELECT gold_cost FROM customshop WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $cost = intval($row['gold_cost']);
            } else {
                handleError("Custom reward not found.");
            }
            $stmt->close();
        }

        // 2. Fetch user gold and lock the row
        $stmt = $conn->prepare("SELECT gold FROM users WHERE uid = ? FOR UPDATE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $gold = intval($user['gold']);
        $stmt->close();

        if ($gold < $cost) {
            $conn->commit(); // release lock
            $conn->close();
            header("Location: ../Shop.php?msg=Insufficient+gold+to+purchase+item");
            exit();
        }

        // 3. Check if already in inventory for this user
        if ($type === 'shop') {
            $stmt = $conn->prepare("SELECT id, Quantity FROM inventory WHERE user_id = ? AND sid = ? FOR UPDATE");
        } else {
            $stmt = $conn->prepare("SELECT id, Quantity FROM inventory WHERE user_id = ? AND cid = ? FOR UPDATE");
        }
        $stmt->bind_param("ii", $user_id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $inv_row = $result->fetch_assoc();
        $stmt->close();

        if ($inv_row) {
            // Update quantity
            $inv_id = $inv_row['id'];
            $stmt = $conn->prepare("UPDATE inventory SET Quantity = Quantity + 1 WHERE id = ?");
            $stmt->bind_param("i", $inv_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insert new inventory item
            if ($type === 'shop') {
                $stmt = $conn->prepare("INSERT INTO inventory(user_id, sid, Quantity, baught) VALUES (?, ?, 1, CURDATE())");
            } else {
                $stmt = $conn->prepare("INSERT INTO inventory(user_id, cid, Quantity, baught) VALUES (?, ?, 1, CURDATE())");
            }
            $stmt->bind_param("ii", $user_id, $id);
            $stmt->execute();
            $stmt->close();
        }

        // 4. Deduct user gold
        $stmt = $conn->prepare("UPDATE users SET gold = gold - ? WHERE uid = ?");
        $stmt->bind_param("ii", $cost, $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $conn->close();
        header("Location: ../Inventory.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        handleError("Transaction failed: " . $e->getMessage());
    }
}
header("Location: ../Inventory.php");
exit();
?>