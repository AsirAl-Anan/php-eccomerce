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
      <li><a href="pages/about.php" class="hover:underline"  href="about.php">About & FAQ's</a></li>
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
   require_once '../config/config.php';
   require_once '../admin/display_products.php';
   
   $currentPage = 'featured'; // Change this to the current page name
   $products = getProductsForPage($conn, $currentPage);
   ?>
  <?php displayProducts($products); ?> 
  </div>
  
    <!-- featured product section ends -->

    <!-- category section -->
    <section class="py-10">
      <div class="container mx-auto px-4">
        <div class="text-center mb-6">
          <h2 class="text-2xl font-bold">Shop by Categories</h2>
          <div class="mt-4">
            <label for="sortCategories" class="mr-2">Sort by:</label>
            <select
              id="sortCategories"
              class="p-2 border rounded bg-gray-100"
              onchange="sortCategories()"
            >
              <option value="alphabetical">Alphabetical</option>
              <option value="popularity">Popularity</option>
            </select>
          </div>
        </div>
        <div
          id="categoryContainer"
          class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
        >
          <!-- Repeat this category card for each category -->
          <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <img
              src="https://via.placeholder.com/300x300"
              alt="Category Image"
              class="w-full h-64 object-cover mb-4 rounded"
            />
            <h3 class="text-xl font-semibold mb-2">Men's Clothing</h3>
            <a
              href="#"
              class="inline-block bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-700"
              >Shop Now</a
            >
          </div>
          <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <img
              src="https://via.placeholder.com/300x300"
              alt="Category Image"
              class="w-full h-64 object-cover mb-4 rounded"
            />
            <h3 class="text-xl font-semibold mb-2">Women's Clothing</h3>
            <a
              href="#"
              class="inline-block bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-700"
              >Shop Now</a
            >
          </div>
          <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <img
              src="https://via.placeholder.com/300x300"
              alt="Category Image"
              class="w-full h-64 object-cover mb-4 rounded"
            />
            <h3 class="text-xl font-semibold mb-2">Kid's Clothing</h3>
            <a
              href="#"
              class="inline-block bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-700"
              >Shop Now</a
            >
          </div>
          <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <img
              src="https://via.placeholder.com/300x300"
              alt="Category Image"
              class="w-full h-64 object-cover mb-4 rounded"
            />
            <h3 class="text-xl font-semibold mb-2">Accessories</h3>
            <a
              href="#"
              class="inline-block bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-700"
              >Shop Now</a
            >
          </div>
          <!-- Add more category cards as needed -->
        </div>
      </div>
    </section>
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

    <!-- footer -->
    <footer class="footer bg-base-100 text-base-content p-10">
      <nav class="">
        <h6 class="footer-title">Services</h6>
        <a class="link link-hover">Branding</a>
        <a class="link link-hover">Blogs</a>
        <a class="link link-hover">Marketing</a>
        <a class="link link-hover">Advertisement</a>
      </nav>
      <nav>
        <h6 class="footer-title">Company</h6>
        <a class="link link-hover">About us</a>
        <a class="link link-hover">Contact</a>
        <a class="link link-hover">Jobs</a>
        <a class="link link-hover">Press kit</a>
      </nav>
      <nav>
        <h6 class="footer-title">Legal</h6>
        <a class="link link-hover">Terms of use</a>
        <a class="link link-hover">Privacy policy</a>
        <a class="link link-hover">Cookie policy</a>
      </nav>
    </footer>
    <footer
      class="footer bg-base-200 text-base-content border-base-300 border-t px-10 py-4"
    >
      <aside class="grid-flow-col items-center">
        <svg
          width="24"
          height="24"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
          fill-rule="evenodd"
          clip-rule="evenodd"
          class="fill-current"
        >
          <path
            d="M22.672 15.226l-2.432.811.841 2.515c.33 1.019-.209 2.127-1.23 2.456-1.15.325-2.148-.321-2.463-1.226l-.84-2.518-5.013 1.677.84 2.517c.391 1.203-.434 2.542-1.831 2.542-.88 0-1.601-.564-1.86-1.314l-.842-2.516-2.431.809c-1.135.328-2.145-.317-2.463-1.229-.329-1.018.211-2.127 1.231-2.456l2.432-.809-1.621-4.823-2.432.808c-1.355.384-2.558-.59-2.558-1.839 0-.817.509-1.582 1.327-1.846l2.433-.809-.842-2.515c-.33-1.02.211-2.129 1.232-2.458 1.02-.329 2.13.209 2.461 1.229l.842 2.515 5.011-1.677-.839-2.517c-.403-1.238.484-2.553 1.843-2.553.819 0 1.585.509 1.85 1.326l.841 2.517 2.431-.81c1.02-.33 2.131.211 2.461 1.229.332 1.018-.21 2.126-1.23 2.456l-2.433.809 1.622 4.823 2.433-.809c1.242-.401 2.557.484 2.557 1.838 0 .819-.51 1.583-1.328 1.847m-8.992-6.428l-5.01 1.675 1.619 4.828 5.011-1.674-1.62-4.829z"
          ></path>
        </svg>
        <p>
          ACME Industries Ltd.
          <br />
          Providing reliable tech since 1992
        </p>
      </aside>
      <nav class="md:place-self-center md:justify-self-end">
        <div class="grid grid-flow-col gap-4">
          <a>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              class="fill-current"
            >
              <path
                d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"
              ></path>
            </svg>
          </a>
          <a>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              class="fill-current"
            >
              <path
                d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"
              ></path>
            </svg>
          </a>
          <a>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              class="fill-current"
            >
              <path
                d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"
              ></path>
            </svg>
          </a>
        </div>
      </nav>
    </footer>
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
}</script>
  </body>
</html>
