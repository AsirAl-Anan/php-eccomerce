<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($product_id && $name && $price && $user_id) {
        // Check if the product is already in the cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update quantity if product already exists in cart
            $row = $result->fetch_assoc();
            $new_quantity = $row['quantity'] + 1;
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_quantity, $row['id']);
        } else {
            // Insert new product into cart
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, name, price, quantity, date_added) VALUES (?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("iissd", $user_id, $product_id, $name, $price);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product data or user not logged in']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}