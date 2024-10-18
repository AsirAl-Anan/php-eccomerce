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
<div class="navbar text-black sticky top-0 z-50 !bg-white !text-black">
  <div class="navbar-start">
    <!-- responsive nav-bar -->
    <div class="dropdown">
      <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          title="menu"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h8m-8 6h16"
          />
        </svg>
      </div>
      <ul
        tabindex="0"
        class="menu menu-sm dropdown-content bg-white text-black rounded-box z-[1] mt-3 w-52 p-2 shadow"
      >
        <li><a class="hover:underline">Home</a></li>
        <li><a href="#featured" class="hover:underline">New & featured</a></li>
        <li>
          <a href="men.php" class="hover:underline">Men</a>
          <ul class="p-2">
            <li><a class="hover:underline">Submenu 1</a></li>
            <li><a class="hover:underline">Submenu 2</a></li>
          </ul>
        </li>
        <li>
          <a class="hover:underline" href="women.php">Women</a>
          <ul class="p-2">
            <li><a class="hover:underline">Submenu 1</a></li>
            <li><a class="hover:underline">Submenu 2</a></li>
          </ul>
        </li>
        <li><a class="hover:underline" href="kids.php">Kids</a></li>
        <li><a class="hover:underline" href="order-track.php">Order Tracking</a></li>
        <li><a href="about.php" class="hover:underline" >About & FAQ's</a></li>
      </ul>
    </div>
    <!-- responsive nav-bar ends -->
    <a class="btn btn-ghost text-xl hover:underline">Urban Store</a>
  </div>
  <div class="navbar-center hidden lg:flex">
    <ul class="menu menu-horizontal px-1 z-20">
      <li><a class="hover:underline">Home</a></li>
      <li><a href="#featured" class="hover:underline">New & featured</a></li>
      <li>
       <a href="men.php">Men</a>
      <li>
      <a href="women.php">Women</a>
      </li>
      <li><a class="hover:underline"  href="kids.php">Kids</a></li>
      <li><a class="hover:underline"  href="order-track.php">Order Tracking</a></li>
      <li><a href="about.php" class="hover:underline"  href="about.php">About & FAQ's</a></li>
    </ul>
  </div>
  <div class="navbar-end">

  <div class="dropdown">
  <div tabindex="0" role="button" class="btn !bg-gray-100 border-none !text-black m-1"><i class="fas fa-search"></i></div>
  <ul tabindex="0" class="dropdown-content menu !bg-white-500 rounded-box z-[1] w-70 p-2 shadow absolute left-1/2 transform -translate-x-1/2 mt-2">

  <form id="search-form" class="input input-bordered flex items-center gap-2">
  <input type="text" id="search-input" class="grow text-white" placeholder="Search" />
  <button type="submit" class="bg-transparent border-0 p-0 cursor-pointer">
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 16 16"
      fill="currentColor"
      class="h-4 w-4 opacity-70">
      <path
        fill-rule="evenodd"
        d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
        clip-rule="evenodd" />
    </svg>
  </button>
</form>
<div id="search-results"></div>
  </ul>
</div>

<a href="../admin/cart.php" class="relative m-5">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
    </svg>
    <span id="cart-count" href="../admin/get_cart_count.php" class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-white rounded-full px-1.5 py-0.5 text-xs leading-none">0</span>
</a>

    
  </div>
  <div class="dropdown dropdown-end ">
    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
    <div class="w-10 h-10 rounded-full overflow-hidden">
      <?php
      $profile_picture = isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : '../uploads/default_avatar.jpg';
      echo '<img src="' . htmlspecialchars($profile_picture) . '" alt="Profile Picture" class="w-full h-full object-cover" />';
      ?>
    </div>
    </div>
    <ul
      tabindex="0"
      class="menu menu-sm dropdown-content bg-gray-400 rounded-box z-[1] mt-3 w-52 p-2 shadow"
    >
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