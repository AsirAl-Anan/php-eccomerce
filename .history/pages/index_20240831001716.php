<?php
// Start the session at the beginning of your PHP file
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}
require_once '../config/config.php';
require_once '../admin/display_products.php';


$currentPage = 'main'; // Change this to the current page name
$products = getProductsForPage($conn, $currentPage);




// Fetch offer slides from the database$sql = "SELECT * FROM carousel_slides ORDER BY slide_order";
// Fetch offer slides from the database
$result = $conn->query("SELECT * FROM offer_slides ORDER BY slide_order");
$offer_slides = $result->fetch_all(MYSQLI_ASSOC);

// Get the first offer's end time for the timer
$first_offer_end_time = !empty($offer_slides) ? strtotime($offer_slides[0]['offer_end_time']) : 0;

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
    <style>
       
    </style>
    <script src="js/script.js"></script>
  
    
  </head>
  <body class="bg-white text-black">
    <!-- navigation bar -->
     <!-- navigation bar -->
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
         
        </li>
        <li>
          <a class="hover:underline" href="women.php">Women</a>
        
        </li>
        <li><a class="hover:underline" href="kids.php">Kids</a></li>
       
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

<!-- cart -->
<button onclick="openCartDrawer()" class="relative m-5">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
    </svg>
    <span id="cart-count" class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-white rounded-full px-1.5 py-0.5 text-xs leading-none">0</span>
</button>
    
  </div>
  <div class="dropdown dropdown-end ">
    <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
    <div class="w-10 h-10 rounded-full overflow-hidden">
    <?php
$default_avatar = '../uploads/default_avatar.jpg'; // Use an absolute path
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

    <!-- navigation bar ends -->

    <?php
require_once '../config/config.php';

function getCarouselSlides($conn) {
    $sql = "SELECT * FROM carousel_slides ORDER BY slide_order";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$carousel_slides = getCarouselSlides($conn);
?>

<!-- carousel starts -->
<div class="carousel w-full h-[70vh] sm:h-[100vh]">
    <?php foreach ($carousel_slides as $index => $slide): ?>
        <div id="slide<?php echo $index + 1; ?>" class="carousel-item relative w-full">
            <img src="../uploads/carousel/<?php echo htmlspecialchars($slide['image_file']); ?>" class="w-full object-cover" alt="product image unavailable" />
            <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-900 bg-opacity-50 text-white p-5 rounded text-center">
                <h2 class="text-lg sm:text-2xl md:text-3xl font-bold"><?php echo htmlspecialchars($slide['title']); ?></h2>
                <p class="mt-2 text-sm sm:text-base md:text-lg"><?php echo htmlspecialchars($slide['description']); ?></p>
                <a href="<?php echo htmlspecialchars($slide['button_link']); ?>" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"><?php echo htmlspecialchars($slide['button_text']); ?></a>
            </div>

            <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
                <a href="#slide<?php echo ($index === 0) ? count($carousel_slides) : $index; ?>" class="btn btn-circle">❮</a>
                <a href="#slide<?php echo ($index === count($carousel_slides) - 1) ? 1 : $index + 2; ?>" class="btn btn-circle">❯</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<!-- carousel ends -->

<div class=""  id="featured">
  <p class="text-2xl font-bold text-center m-5">Featured</p>
   <?php 
  $current_page = isset($_GET['page']) ? $_GET['page'] : 'men'; // Default to 'men' if no page is specified
  $products = getProductsForPage($conn, $current_page, $sort, $min_price, $max_price, $date_filter);
  
  // Display the filter form
  displayFilterForm($current_page, $sort, $min_price, $max_price, $date_filter);
  
  // Display the products
  displayProducts($products);
   ?>

  </div>
  
    <!-- featured product section ends -->

    <!-- category section -->
    <!-- offer section -->

    
    <div class="flex w-full h-[50vh]">
    <div class="w-[85%] h-full relative overflow-hidden">
        <div id="carousel" class="flex transition-transform duration-300 ease-in-out h-full">
            <?php foreach ($offer_slides as $index => $slide): ?>
                <div class="w-full h-full flex-shrink-0">
                    <img src="../uploads/offer_slides/<?php echo htmlspecialchars($slide['image_file']); ?>" class="w-full h-full object-cover" alt="offer image" />
                </div>
            <?php endforeach; ?>
        </div>
        <button onclick="moveSlide(-1)" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white font-bold py-2 px-4 rounded-full transition duration-300">❮</button>
        <button onclick="moveSlide(1)" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white font-bold py-2 px-4 rounded-full transition duration-300">❯</button>
    </div>
    <div class="w-[15%] bg-gray-200 flex flex-col items-center justify-center">
        <h3 class="text-xl font-bold mb-2">Offer Ends In:</h3>
        <div id="offerTimer" class="text-2xl font-bold"></div>
    </div>
</div>


    <!-- category section ends -->

    <!-- services section -->

    <section class="mb-20">
      <div class="bg-white py-16">
        <h2 class="text-3xl font-bold text-center mb-12">Our Services:</h2>
        <div
          class="flex flex-wrap justify-center space-y-8 md:space-y-0 md:space-x-8 lg:space-x-32"
        >
          <div class="w-full md:w-auto text-center mb-8 md:mb-0">
            <i class="fas fa-shipping-fast text-4xl mb-4"></i>
            <p class="mt-2 text-lg md:text-xl font-medium">
              Free Shipping World Wide
            </p>
          </div>
          <div class="w-full md:w-auto text-center mb-8 md:mb-0">
            <i class="fas fa-truck text-4xl mb-4"></i>
            <p class="mt-2 text-lg md:text-xl font-medium">
              2 Days Ultra Fast Delivery
            </p>
          </div>
          <div class="w-full md:w-auto text-center mb-8 md:mb-0">
            <i class="fas fa-undo text-4xl mb-4"></i>
            <p class="mt-2 text-lg md:text-xl font-medium">
              30 Days Easy Return. No question asked.
            </p>
          </div>
          <div class="w-full md:w-auto text-center">
            <i class="fas fa-gift text-4xl mb-4"></i>
            <p class="mt-2 text-lg md:text-xl font-medium">
              40% Off on Gift Cards.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- services section ends -->

    <!-- Subscribe to Newsletter Section -->
    <section class="py-10 bg-blue-100 bg-[url('path/to/image')]">
      <div class="container mx-auto px-4">
        <div class="text-center mb-6">
          <h2 class="text-2xl font-bold">Subscribe to Our Newsletter</h2>
          <p class="mt-2 text-gray-700">
            Get the latest updates on new products and upcoming sales
          </p>
        </div>
        <div class="flex flex-col md:flex-row justify-center items-center">
          <input
            type="email"
            placeholder="Enter your email"
            class="p-3 border rounded w-full md:w-2/3 lg:w-1/2 mb-4 md:mb-0 md:mr-4"
          />
          <button
            class="bg-blue-500 text-white py-3 px-6 rounded hover:bg-blue-700"
          >
            Subscribe
          </button>
        </div>
      </div>
    </section>
    <!-- newsletter section ends -->
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
    <script>
let currentSlide = 0;
const carousel = document.getElementById('carousel');
const slideCount = <?php echo count($offer_slides); ?>;

function moveSlide(direction) {
    currentSlide = (currentSlide + direction + slideCount) % slideCount;
    carousel.style.transform = `translateX(-${currentSlide * 100}%)`;
}

function autoSlide() {
    moveSlide(1);
}

// Set interval for auto-sliding (change slides every 5 seconds)
let slideInterval = setInterval(autoSlide, 5000);

// Pause auto-sliding when hovering over the carousel
carousel.addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
});

// Resume auto-sliding when mouse leaves the carousel
carousel.addEventListener('mouseleave', () => {
    slideInterval = setInterval(autoSlide, 5000);
});

// Timer functionality
function updateTimer() {
    const now = new Date().getTime();
    const endTime = <?php echo $first_offer_end_time * 1000; ?>;
    const timeLeft = endTime - now;

    if (timeLeft > 0) {
        const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

        document.getElementById('offerTimer').innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
    } else {
        document.getElementById('offerTimer').innerHTML = "Offer Ended";
    }
}

setInterval(updateTimer, 1000);
updateTimer(); // Initial call

      //offer slide

// Timer functionality
function updateTimer() {
    const now = new Date().getTime();
    const endTime = <?php echo $first_offer_end_time * 1000; ?>;
    const timeLeft = endTime - now;

    if (timeLeft > 0) {
        const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

        document.getElementById('offerTimer').innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
    } else {
        document.getElementById('offerTimer').innerHTML = "Offer Ended";
    }
}

setInterval(updateTimer, 1000);
updateTimer(); // Initial call
      // search box
    document.getElementById('search-form').addEventListener('submit', function(e) {
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
  ul.className = 'list-none p-0 bg-slate-200';
  results.forEach(product => {
    const li = document.createElement('li');
    li.className = 'mb-2';
    li.innerHTML = `<a class='color-black' href="product.php?id=${product.id}" class="text-white hover:underline">${product.name} - $${product.price}</a>`;
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
