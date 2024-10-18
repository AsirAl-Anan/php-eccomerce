<?php
require_once 'config/config.php';

function addToCart($userId, $productId, $quantity) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
    $stmt->bind_param("iiii", $userId, $productId, $quantity, $quantity);
    $stmt->execute();
    $stmt->close();
}

function removeFromCart($userId, $productId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->close();
}

function updateCartQuantity($userId, $productId, $quantity) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $userId, $productId);
    $stmt->execute();
    $stmt->close();
}

function getCart($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $cart;
}