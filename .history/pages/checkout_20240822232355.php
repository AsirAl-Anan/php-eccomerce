<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../loginsystem/login.php');
    exit();
}

$user_id = $_SESSION['id'];

// Check if it's a direct purchase or from cart
if (isset($_GET['product_id'])) {
    // Direct purchase
    $product_id = $_GET['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $items = [['product' => $product, 'quantity' => 1]];
    $total = $product['price'];
} else {
    // From cart
    $stmt = $conn->prepare("SELECT c.*, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $total = array_reduce($items, function($carry, $item) {
        return $carry + ($item['price'] * $item['quantity']);
    }, 0);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process the order
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'Processing', ?)");
        $stmt->bind_param("ids", $user_id, $total, $_POST['shipping_address']);
        $stmt->execute();
        $order_id = $conn->insert_id;

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Clear the cart if checkout is from cart
        if (!isset($_GET['product_id'])) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }

        $conn->commit();
        header('Location: order-track.php?order_id=' . $order_id);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "An error occurred while processing your order. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Checkout</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="shipping_address">
                    Shipping Address
                </label>
                <textarea name="shipping_address" id="shipping_address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
            </div>
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-2">Order Summary</h2>
                <?php foreach ($items as $item): ?>
                    <div class="flex justify-between mb-2">
                        <span><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="font-bold mt-2">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Place Order
                </button>
            </div>
        </form>
    </div>
</body>
</html>