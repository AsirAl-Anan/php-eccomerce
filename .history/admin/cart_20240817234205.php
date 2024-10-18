<?php
session_start();
require_once '../config/config.php';

function getCartItems($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT c.id, c.product_id, c.quantity, p.name, p.price, pi.image_file
        FROM cart c
        JOIN products p ON c.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id
        WHERE c.user_id = ?
        GROUP BY c.id
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function calculateTotal($cartItems) {
    return array_reduce($cartItems, function($carry, $item) {
        return $carry + ($item['price'] * $item['quantity']);
    }, 0);
}


$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

if (isset($_GET['action']) && $_GET['action'] === 'get_cart_data') {
    $cartItems = getCartItems($conn, $user_id);
    $total = calculateTotal($cartItems);
    
    header('Content-Type: application/json');
    echo json_encode([
        'cartItems' => $cartItems,
        'total' => $total
    ]);
    exit;
}
$cartItems = getCartItems($conn, $user_id);
$total = calculateTotal($cartItems);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
   

<script src="https://cdn.tailwindcss.com"></script>
   
  
  
</head>
<body class="bg-gray-100">
    

<div class="container mx-auto px-4 py-8 relative z-[60] mt-16">
    
        <h1 class="text-3xl font-bold mb-6">Shopping Cart</h1>
        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Product</th>
                            <th class="py-3 px-6 text-center">Quantity</th>
                            <th class="py-3 px-6 text-right">Price</th>
                            <th class="py-3 px-6 text-right">Total</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php foreach ($cartItems as $item): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if (!empty($item['image_file'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($item['image_file']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 object-cover mr-4">
                                        <?php else: ?>
                                            <div class="w-16 h-16 bg-gray-200 mr-4 flex items-center justify-center">No image</div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td class="py-3 px-6 text-right">
                                    $<?php echo number_format($item['price'], 2); ?>
                                </td>
                                <td class="py-3 px-6 text-right">
                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 text-right">
                <p class="text-xl font-bold">Total: $<?php echo number_format($total, 2); ?></p>
                <button onclick="checkout()" class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Checkout
                </button>
            </div>
        <?php endif; ?>
    </div>


echo '<div class="mt-4">';
echo '<a href="checkout.php" class="bg-green-500 text-white py-2 px-4 rounded">Proceed to Checkout</a>';
echo '</div>';

    <script>
   function removeFromCart(cartItemId) {
    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_item_id=${cartItemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to remove product from cart: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while removing the product from cart.');
    });
}
    </script>
</body>
</html>