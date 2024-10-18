<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}
require_once '../config/config.php';
require_once '../admin/display_products.php';

$currentPage = 'about';

$about_query = "SELECT * FROM about_content WHERE section IN ('Company Overview', 'Founders and Team', 'Story Behind the Brand', 'Contact Information')";
$about_result = $conn->query($about_query);

$faq_query = "SELECT * FROM faq_content";
$faq_result = $conn->query($faq_query);

$team_query = "SELECT * FROM team_members";
$team_result = $conn->query($team_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-commerce Site</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 text-gray-800">
    <!-- Navigation Bar -->
    <div class="navbar bg-transparent text-gray-800 sticky top-0 z-50 shadow-sm backdrop-blur-md">
        <div class="navbar-start">
            <div class="dropdown">
                <button tabindex="0" class="btn btn-ghost lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                    </svg>
                </button>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-white text-gray-800 rounded-box mt-3 p-2 shadow">
                    <li><a href="index.php" class="hover:text-gray-600">Home</a></li>
                    <li><a href="index.php#featured" class="hover:text-gray-600">New & Featured</a></li>
                    <li><a href="men.php" class="hover:text-gray-600">Men</a></li>
                    <li><a href="women.php" class="hover:text-gray-600">Women</a></li>
                    <li><a href="kids.php" class="hover:text-gray-600">Kids</a></li>
                    <li><a href="order-track.php" class="hover:text-gray-600">Order Tracking</a></li>
                </ul>
            </div>
            <a href="index.php" class="btn btn-ghost text-2xl font-bold hover:text-gray-600">Urban Store</a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1">
                <li><a href="index.php" class="hover:text-gray-600">Home</a></li>
                <li><a href="index.php#featured" class="hover:text-gray-600">New & Featured</a></li>
                <li><a href="men.php" class="hover:text-gray-600">Men</a></li>
                <li><a href="women.php" class="hover:text-gray-600">Women</a></li>
                <li><a href="kids.php" class="hover:text-gray-600">Kids</a></li>
                <li><a href="about.php" class="hover:text-gray-600">About & FAQs</a></li>
            </ul>
        </div>
        <div class="navbar-end flex items-center gap-4">
            <div class="dropdown">
                <button tabindex="0" class="btn btn-ghost btn-circle">
                    <i class="fas fa-search"></i>
                </button>
                <ul tabindex="0" class="dropdown-content bg-white rounded-box mt-2 p-2 shadow">
                    <form id="search-form" class="input input-bordered flex items-center gap-2">
                        <input type="text" id="search-input" class="grow" placeholder="Search" />
                        <button type="submit" class="bg-transparent border-0 p-0 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-4 w-4 opacity-70">
                                <path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </form>
                </ul>
            </div>
            <button onclick="openCartDrawer()" class="relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span id="cart-count" class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-white rounded-full px-1.5 py-0.5 text-xs leading-none">0</span>
            </button>
            <div class="dropdown dropdown-end">
                <div tabindex="0" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 h-10 rounded-full overflow-hidden">
                        <?php
                        $default_avatar = '../uploads/default_avatar.jpg';
                        $profile_picture = isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) 
                            ? $_SESSION['profile_picture'] 
                            : $default_avatar;

                        if (!file_exists($profile_picture)) {
                            $profile_picture = $default_avatar;
                        }

                        echo '<img src="' . htmlspecialchars($profile_picture) . '" alt="Profile Picture" class="w-full h-full object-cover" />';
                        ?>
                    </div>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-white rounded-box mt-3 w-52 p-2 shadow">
                    <?php if (isLoggedIn()): ?>
                        <li><a href="profile.php" class="hover:text-gray-600">Profile</a></li>
                        <li><a href="loginsystem/logout.php" class="hover:text-gray-600">Logout</a></li>
                    <?php else: ?>
                        <li><a href="loginsystem/registration.php" class="hover:text-gray-600">Registration</a></li>
                        <li><a href="loginsystem/login.php" class="hover:text-gray-600">Login</a></li>
                        <li><a href="admin/admin_login.php" class="hover:text-gray-600">Admin Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold mb-8 text-center">About Us</h1>

        <?php
        while ($row = $about_result->fetch_assoc()) {
            echo "<section class='mb-8'>";
            echo "<h2 class='text-3xl font-semibold mb-4'>{$row['section']}</h2>";
            echo "<div class='prose max-w-none bg-white p-6 rounded-lg shadow-sm'>" . nl2br($row['content']) . "</div>";
            echo "</section>";

            if ($row['section'] == 'Founders and Team') {
                echo "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'>";
                while ($team_row = $team_result->fetch_assoc()) {
                    echo "<div class='bg-white p-4 rounded-lg shadow-md text-center'>";
                    echo "<img src='../uploads/group-members/{$team_row['photo']}' alt='{$team_row['name']}' class='w-full h-auto mb-4 rounded-lg object-cover'>";
                    echo "<h3 class='text-xl font-semibold'>{$team_row['name']}</h3>";
                    echo "<p class='text-gray-600'>{$team_row['position']}</p>";
                    echo "</div>";
                }
                echo "</div>";
            }
        }
        ?>

        <h2 class="text-3xl font-semibold mb-4 mt-12">Frequently Asked Questions</h2>
        <div class="mb-8">
            <?php
            while ($faq_row = $faq_result->fetch_assoc()) {
                echo "<div class='collapse collapse-arrow bg-white rounded-lg shadow-sm mb-2'>";
                echo "<input type='checkbox' />";
                echo "<div class='collapse-title text-lg font-medium text-gray-800'>{$faq_row['question']}</div>";
                echo "<div class='collapse-content'>";
                echo "<p class='text-gray-700'>" . nl2br($faq_row['answer']) . "</p>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    <!-- drawer -->
    <div id="cartDrawer" class="fixed inset-y-0 right-0 w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Your Cart</h2>
            <button onclick="closeCartDrawer()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="cartContents"></div>
    </div>
</div>

    <!-- footer -->
    <?php include('footer.php'); ?>
    <!-- footer ends -->
    <script>document.getElementById('search-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const query = document.getElementById('search-input').value;
  performSearch(query);
});

function performSearch(query) {
  fetch(`search.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => displayResults(data))
    .catch(error => console.error('Error:', error));
}

function displayResults(results) {
  const resultsContainer = document.getElementById('search-results');
  resultsContainer.innerHTML = '';
  
  if (results.length === 0) {
    resultsContainer.innerHTML = '<p class="text-white">No results found.</p>';
    return;
  }
  
  const ul = document.createElement('ul');
  ul.className = 'list-none p-0';
  results.forEach(product => {
    const li = document.createElement('li');
    li.className = 'mb-2';
    li.innerHTML = `<a href="product.php?id=${product.id}" class="text-white hover:underline">${product.name} - $${product.price}</a>`;
    ul.appendChild(li);
  });
  
  resultsContainer.appendChild(ul);

}
function openCartDrawer() {
    document.getElementById('cartDrawer').classList.remove('translate-x-full');
    loadCartContents();
}

function closeCartDrawer() {
    document.getElementById('cartDrawer').classList.add('translate-x-full');
}

function loadCartContents() {
    fetch('../admin/cart.php?action=get_cart_data')
        .then(response => response.json())
        .then(data => {
            const cartContents = document.getElementById('cartContents');
            if (data.cartItems.length === 0) {
                cartContents.innerHTML = '<p class="text-center py-4">Your cart is empty.</p>';
            } else {
                let html = '<div class="space-y-4">';
                data.cartItems.forEach(item => {
                    html += `
                        <div class="flex items-center justify-between border-b pb-2">
                            <div class="flex items-center space-x-4">
                                <img src="../uploads/${item.image_file || 'default.jpg'}" alt="${item.name}" class="w-16 h-16 object-cover">
                                <div>
                                    <h3 class="font-semibold">${item.name}</h3>
                                    <p class="text-gray-600">$${parseFloat(item.price).toFixed(2)}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="updateQuantity(${item.id}, -1)" class="bg-gray-200 px-2 py-1 rounded">-</button>
                                <span>${item.quantity}</span>
                                <button onclick="updateQuantity(${item.id}, 1)" class="bg-gray-200 px-2 py-1 rounded">+</button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                html += `
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold">Subtotal:</span>
                            <span>$${parseFloat(data.total).toFixed(2)}</span>
                        </div>
                       <a href="checkout.php" class=" btn btn-info bg-green-500 text-white py-2 px-4 rounded w-full  font-semibold">Proceed to Checkout</a>
                    </div>
                `;
                cartContents.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('cartContents').innerHTML = '<p class="text-center py-4">Error loading cart contents.</p>';
        });
}

function updateQuantity(itemId, change) {
    fetch('../admin/update_cart_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}&change=${change}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCartContents(); // Reload cart contents
            updateCartCount(); // Update the cart count
        } else {
            alert('Failed to update quantity: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
function checkout() {
    // Implement checkout functionality
    alert('Checkout functionality to be implemented');
}
function updateCartCount() {
    fetch('../admin/get_cart_count.php')
        .then(response => response.text()) // Changed to text()
        .then(count => {
            document.getElementById('cart-count').textContent = count;
        })
        .catch(error => console.error('Error:', error));
}

// Call this function when the page loads and after adding/removing items from the cart
document.addEventListener('DOMContentLoaded', updateCartCount);
// Call this function when the page loads and after adding/removing items from the cart
document.addEventListener('DOMContentLoaded', updateCartCount);

// Load cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch('../admin/get_cart_count.php')
        .then(response => response.text())
        .then(count => {
            document.getElementById('cart-count').textContent = count;
        })
        .catch(error => console.error('Error:', error));
});
</script>
  </body>
</html>
