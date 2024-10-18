<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

    if ($product_id) {
        // Check if the product is already in the cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update quantity if product is already in cart
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
        } else {
            // Insert new product into cart
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->bind_param("ii", $user_id, $product_id);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Proddsfsduct added to cart successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}