<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

function getCartCount($conn, $user_id) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

if ($user_id) {
    $count = getCartCount($conn, $user_id);
} else {
    // For guest users, you might want to use a session-based approach
    $count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
}

echo json_encode($count);