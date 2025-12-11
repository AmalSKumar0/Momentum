<?php
session_start();
include '../config/DBconfig.php';
if(!isset($_SESSION['user_id'])){
    header("Location: Auth/login.php");
}
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT gold,xp FROM users WHERE uid = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($gold,$xp);
    $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['inventory_id'])){
    $inventory_id = $_GET['inventory_id'];
    $xp = $_GET['xp'];
    $qty = $_GET['qty'];
    $stmt = $conn->prepare("update users set xp = xp + ? WHERE uid = ?");
    $stmt->bind_param("is", $xp, $user_id);
    $stmt->execute();
    if($qty > 1){
        $stmt = $conn->prepare("UPDATE inventory SET Quantity = Quantity - 1 WHERE id = ? AND user_id = ?");
    }
    else{
        $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ? AND user_id = ?");
    }
    $stmt->bind_param("ii", $inventory_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header("Location: ../Inventory.php");
    exit();
}
header("Location: ../Inventory.php");
exit();