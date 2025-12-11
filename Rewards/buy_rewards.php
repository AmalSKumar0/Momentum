<?php
session_start();
include '../config/DBconfig.php';
if(!isset($_SESSION['user_id'])){
    header("Location: Auth/login.php");
}
$user_id = $_SESSION['user_id'];
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])){
    $type = $_GET['type'];
    $cost = $_GET['cost'];
    $stmt = $conn->prepare("SELECT gold FROM users WHERE uid = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($gold);
    $stmt->fetch();
    if($gold < $cost){
        header("Location: ../Shop.php?msg=Insufficient+gold+to+purchase+item");
        exit();
    }
    if($type == 'shop'){
        $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE sid = ?");
    } else if($type == 'custom'){
        $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE cid = ?");
    }
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0){
        if($type == 'shop'){
            $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity + 1 WHERE sid = ?");
        } else if($type == 'custom'){
            $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity + 1 WHERE cid = ?");
        }
        $stmt->bind_param("i", $_GET['id']);
    } else {
        if($type == 'shop'){
            $stmt = $conn->prepare("INSERT INTO inventory(user_id, sid, quantity) VALUES (?, ?, 1)");
        } else if($type == 'custom'){
            $stmt = $conn->prepare("INSERT INTO inventory(user_id, cid, quantity) VALUES (?, ?, 1)");
        }
        $stmt->bind_param("ii", $user_id, $_GET['id']);
    }
    $stmt->execute();


    $stmt = $conn->prepare("update users set gold = gold - ? WHERE uid = ?");
    $stmt->bind_param("is", $cost, $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header("Location: ../Inventory.php");
    exit();
}
header("Location: ../Inventory.php");
exit();