<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($product_id && $user_id) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product removed from cart successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove product from cart']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product data or user not logged in']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}