<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

    if ($product_id && $name && $price) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $product_id) {
                $item['quantity']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product_id,
                'name' => $name,
                'price' => $price,
                'quantity' => 1
            ];
        }

        echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
