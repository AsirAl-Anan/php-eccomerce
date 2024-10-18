<?php 

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

    if ($user_id && $product_id && $name && $price) {
        require '../config/database.php';  // Assume you have a file for DB connection

        $stmt = $pdo->prepare('SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$user_id, $product_id]);
        $cart_item = $stmt->fetch();

        if ($cart_item) {
            $stmt = $pdo->prepare('UPDATE carts SET quantity = quantity + 1 WHERE id = ?');
            $stmt->execute([$cart_item['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO carts (user_id, product_id, name, price, quantity) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $product_id, $name, $price, 1]);
        }

        echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product data or not logged in']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

?>