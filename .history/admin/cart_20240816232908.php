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
$cartItems = getCartItems($conn, $user_id);
$total = calculateTotal($cartItems);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
   
<link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.tailwindcss.com"></script>
   
  
  
</head>
<body class="bg-gray-100">
    
<nav class="navbar bg-base-100 px-4 py-2">
        <div class="flex-1">
            <a class="text-xl font-semibold" href="#">Urban Store</a>
        </div>
        <div class="flex-none hidden lg:flex space-x-4">
            <a class="btn btn-ghost normal-case text-sm" href="#">Home</a>
            <a class="btn btn-ghost normal-case text-sm" href="#">New &amp; Featured</a>
            <a class="btn btn-ghost normal-case text-sm" href="#">Men</a>
            <a class="btn btn-ghost normal-case text-sm" href="#">Women</a>
            <a class="btn btn-ghost normal-case text-sm" href="#">Kids</a>
            <a class="btn btn-ghost normal-case text-sm" href="#">Order Tracking</a>
            <a class="btn btn-ghost normal-case text-sm" href="#">About &amp; FAQ's</a>
        </div>
        <div class="flex-none space-x-4">
            <button class="btn btn-ghost btn-circle">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4a4 4 0 100 8 4 4 0 000-8zm8 8a4 4 0 100-8 4 4 0 000 8zm-6 8a4 4 0 100-8 4 4 0 000 8zm4 0a4 4 0 100-8 4 4 0 000 8zm-4-4h4" />
                </svg>
            </button>
            <button class="btn btn-ghost btn-circle">
                <div class="indicator">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v2H3zm2 16h14v-2H5v2zm14-6H5v2h14v-2zM3 9h18V7H3v2zm5 10h2v-2H8v2zm8-2v2h2v-2h-2zM5 13h2v-2H5v2zm2 0h10v-2H7v2zm4 2h6v-2h-6v2zm-4 0v2h2v-2H7z" />
                    </svg>
                    <span class="badge badge-sm indicator-item">2</span>
                </div>
            </button>
            <div class="avatar">
                <div class="w-8 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                    <img src="https://via.placeholder.com/150" />
                </div>
            </div>
        </div>
    </nav>

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