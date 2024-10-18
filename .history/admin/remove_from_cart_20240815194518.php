<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_item_id = isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0;
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

    if ($cart_item_id && $user_id) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_item_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product removed from cart successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove product from cart']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}