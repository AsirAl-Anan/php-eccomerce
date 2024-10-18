<?php
// Start the session at the beginning of your PHP file
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}
require_once '../config/config.php';
require_once '../admin/display_products.php';

$currentPage = 'about';

// Fetch About content
$about_query = "SELECT * FROM about_content WHERE section IN ('Company Overview', 'Founders and Team', 'Story Behind the Brand', 'Contact Information')";
$about_result = $conn->query($about_query);

// Fetch FAQ content
$faq_query = "SELECT * FROM faq_content";
$faq_result = $conn->query($faq_query);

// Fetch team members
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
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
      integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css"
      rel="stylesheet"
      type="text/css"
    />
    <link rel="stylesheet" href="css/index.css" />
    <script src="js/script.js"></script>
   
    
  </head>
  <body class="bg-white text-black">
    <!-- navigation bar -->
     <!-- navigation bar -->
<?php 
include "nav.php"
?>
    <!-- navigation bar ends -->

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">About Us</h1>

        <?php
        while ($row = $about_result->fetch_assoc()) {
            echo "<section class='mb-8'>";
            echo "<h2 class='text-2xl font-semibold mb-4'>{$row['section']}</h2>";
            echo "<div class='prose max-w-none'>" . nl2br($row['content']) . "</div>";
            echo "</section>";

            if ($row['section'] == 'Founders and Team') {
                echo "<div class='grid grid-cols-1 md:grid-cols-3 gap-6 mb-8'>";
                while ($member = $team_result->fetch_assoc()) {
                    echo "<div class='text-center'>";
                    echo "<img src='../uploads/group-members/{$member['image']}' alt='{$member['name']}' class='w-32 h-32 rounded-full mx-auto mb-4'>";
                    echo "<h3 class='font-semibold'>{$member['name']}</h3>";
                    echo "<p>{$member['position']}</p>";
                    echo "</div>";
                }
                echo "</div>";
            }
        }
        ?>

        <!-- FAQ Section -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Frequently Asked Questions</h2>
            <div class="space-y-4">
                <?php
                while ($faq = $faq_result->fetch_assoc()) {
                    echo "<details class='bg-gray-100 p-4 rounded-lg'>";
                    echo "<summary class='font-semibold cursor-pointer'>{$faq['question']}</summary>";
                    echo "<p class='mt-2'>{$faq['answer']}</p>";
                    echo "</details>";
                }
                ?>
            </div>
        </section>
    </div>


<!-- About Us Content -->


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
