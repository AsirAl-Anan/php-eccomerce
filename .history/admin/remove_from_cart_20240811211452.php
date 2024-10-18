<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($product_id && isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] === $product_id) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index the array
                echo json_encode(['success' => true, 'message' => 'Product removed from cart successfully']);
                exit;
            }
        }
    }

    echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}