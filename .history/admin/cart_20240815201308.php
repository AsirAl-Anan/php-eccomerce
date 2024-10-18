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
   
   
  
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    

<nav class="bg-white border-b border-gray-200">
        <div class="container mx-auto flex items-center justify-between py-4 px-6">
            <!-- Logo -->
            <div class="text-lg font-bold">
                Urban Store
            </div>

            <!-- Links -->
            <div class="flex space-x-6 text-sm font-medium text-gray-600">
                <a href="#" class="hover:text-gray-900">Home</a>
                <a href="#" class="hover:text-gray-900">New & featured</a>
                <a href="#" class="hover:text-gray-900">Men</a>
                <a href="#" class="hover:text-gray-900">Women</a>
                <a href="#" class="hover:text-gray-900">Kids</a>
                <a href="#" class="hover:text-gray-900">Order Tracking</a>
                <a href="#" class="hover:text-gray-900">About & FAQ's</a>
            </div>

            <!-- Search and Icons -->
            <div class="flex items-center space-x-4">
                <button class="p-2 rounded-full bg-gray-100 hover:bg-gray-200">
                    <!-- Search Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3.5a7.5 7.5 0 006.15 12.15z" />
                    </svg>
                </button>

                <button class="relative p-2 rounded-full bg-gray-100 hover:bg-gray-200">
                    <!-- Cart Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3h16.5M4.5 3l1.28 8.55a2.25 2.25 0 002.24 1.95h7.96a2.25 2.25 0 002.24-1.95L19.5 3m-4.42 13.5a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-10.5 0a2.25 2.25 0 114.5 0 2.25 2.25 0 01-4.5 0z" />
                    </svg>
                    <!-- Notification Badge -->
                    <span class="absolute top-0 right-0 flex items-center justify-center h-4 w-4 bg-red-500 text-white text-xs font-bold rounded-full">1</span>
                </button>

                <!-- Profile Dropdown -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 h-10 rounded-full overflow-hidden">
                            <?php
                            $profile_picture = isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : '../uploads/default_avatar.jpg';
                            echo '<img src="' . htmlspecialchars($profile_picture) . '" alt="Profile Picture" class="w-full h-full object-cover" />';
                            ?>
                        </div>
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content bg-gray-400 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                        <?php if (isLoggedIn()): ?>
                            <li><a class="hover:underline" href="profile.php">Profile</a></li>
                            <li><a class="hover:underline" href="../loginsystem/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a class="hover:underline" href="../loginsystem/registration.php">Registration</a></li>
                            <li><a class="hover:underline" href="../loginsystem/login.php">Login</a></li>
                            <li><a href="../admin/admin_login.php" class="hover:underline">Admin Login</a></li>
                        <?php endif; ?>
                    </ul>
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