<?php
// Start the session at the beginning of your PHP file
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

// Function to check if user is logged in
function isLoggedIn() {
  return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Check if user is logged in
if (!isLoggedIn()) {
  header("Location: ../loginsystem/login.php");
  exit();
}

$user_id = $_SESSION['id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the user exists
if (!$user) {
  // User not found, handle this situation (e.g., redirect or show an error)
  echo "<p class='text-red-500 text-sm'>User not found. Please log in again.</p>";
  // Alternatively, redirect back to login or another page
   //header("Location: ../loginsystem/login.php");
   //exit();
}
// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    // Update user information
    $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, address = ?, age = ?, gender = ? WHERE id = ?");
    $update_stmt->bind_param("sssisi", $username, $email, $address, $age, $gender, $user_id);
    $update_stmt->execute();

    // Handle password update
    if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $password_stmt->bind_param("si", $new_password, $user_id);
            $password_stmt->execute();
        } else {
            $password_error = "Passwords do not match";
        }
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
              $picture_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
              $picture_stmt->bind_param("si", $upload_path, $user_id);
              $picture_stmt->execute();
              
              // Update session with new profile picture path
              $_SESSION['profile_picture'] = $upload_path;
          }
        }
    }

    // Refresh user data
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Profile - E-commerce Site</title>
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
  <body class="bg-gray-100 text-gray-800 font-sans">
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
        <li><a class="hover:underline" href="index.php">Home</a></li>
        <li><a href="index.php#featured" class="hover:underline">New & featured</a></li>
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
       
        <li><a href="about.php" class="hover:underline" >About & FAQ's</a></li>
      </ul>
    </div>
    <!-- responsive nav-bar ends -->
    <a class="btn btn-ghost text-xl hover:underline">Urban Store</a>
  </div>
  <div class="navbar-center hidden lg:flex">
    <ul class="menu menu-horizontal px-1 z-20">
      <li><a class="hover:underline" href="index.php">Home</a></li>
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
    <!-- ... (Keep the existing navigation bar code) ... -->

    <!-- Profile content -->
    <div class="container mx-auto px-4 py-12 flex">
      <!-- Sidebar -->
      <div class="w-1/4 bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-8">
          <img
            src="<?php echo htmlspecialchars($user['profile_picture'] ? $user['profile_picture'] : '../uploads'); ?>"
            alt="Profile Picture"
            class="w-32 h-32 rounded-full mx-auto shadow-md"
          />
          <h2 class="mt-4 text-xl font-bold text-gray-900">
            <?php echo htmlspecialchars($user['username']); ?>
          </h2>
          <p class="text-sm text-gray-600">
            <?php echo htmlspecialchars($user['email']); ?>
          </p>
        </div>
        <ul class="space-y-4 text-lg">
          <li>
            <a href="#" class="text-indigo-600 hover:text-indigo-800">Dashboard</a>
          </li>
          <li>
            <a href="order-track.php" class="text-gray-700 hover:text-gray-900">Orders</a>
          </li>
          
          <li>
            <a href="#" class="text-gray-700 hover:text-gray-900">Payment</a>
          </li>
          <li>
            <a href="../loginsystem/logout.php" class="text-gray-700 hover:text-gray-900">Logout</a>
          </li>
        </ul>
      </div>

      <!-- Main Profile Form -->
      <div class="w-3/4 ml-8 bg-white rounded-lg shadow-md p-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-900">Edit Profile</h1>
        <form
          action="profile.php"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6"
        >
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label
                for="username"
                class="block text-lg font-medium text-gray-700"
                >Username:</label
              >
              <input
                type="text"
                id="username"
                name="username"
                value="<?php echo htmlspecialchars($user['username']); ?>"
                required
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg text-white"
              />
            </div>
            <div>
              <label for="email" class="block text-lg font-medium text-gray-700"
                >Email:</label
              >
              <input
                type="email"
                id="email"
                name="email"
                value="<?php echo htmlspecialchars($user['email']); ?>"
                required
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg text-white"
              />
              <?php if (isset($email_error)): ?>
              <p class="text-red-500 text-sm mt-2"><?php echo $email_error; ?></p>
              <?php endif; ?>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="address" class="block text-lg font-medium text-gray-700"
                >Address:</label
              >
              <input
                type="text"
                id="address"
                name="address"
                value="<?php echo htmlspecialchars($user['address']); ?>"
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg text-white"
              />
            </div>
            <div>
              <label for="age" class="block text-lg font-medium text-gray-700"
                >Age:</label
              >
              <input
                type="number"
                id="age"
                name="age"
                value="<?php echo htmlspecialchars($user['age']); ?>"
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg text-white"
              />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="gender" class="block text-lg font-medium text-gray-700"
                >Gender:</label
              >
              <select
                id="gender"
                name="gender"
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg text-white"
              >
                <option
                  value="male"
                  <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>
                >
                  Male
                </option>
                <option
                  value="female"
                  <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>
                >
                  Female
                </option>
                <option
                  value="other"
                  <?php echo $user['gender'] == 'other' ? 'selected' : ''; ?>
                >
                  Other
                </option>
              </select>
            </div>
            <div>
              <label
                for="profile_picture"
                class="block text-lg font-medium text-gray-700"
                >Profile Picture:</label
              >
              <input
                type="file"
                id="profile_picture"
                name="profile_picture"
                accept="image/*"
                class="mt-2 block w-full rounded-lg text-lg text-white"
              />
              <?php if (!empty($user['profile_picture'])): ?>
              <div class="mt-4">
                <img
                  src="<?php echo htmlspecialchars($user['profile_picture']); ?>"
                  alt="Profile Picture"
                  class="w-32 h-32 rounded-full shadow-md"
                />
              </div>
              <?php endif; ?>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label
                for="new_password"
                class="block text-lg font-medium text-gray-700"
                >New Password:</label
              >
              <input
                type="password"
                id="new_password"
                name="new_password"
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg text-white"
              />
            </div>
            <div>
              <label
                for="confirm_password"
                class="block text-lg font-medium text-gray-700"
                >Confirm Password:</label
              >
              <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg text-white"
              />
              <?php if (isset($password_error)): ?>
              <p class="text-red-500 text-sm mt-2"><?php echo $password_error; ?></p>
              <?php endif; ?>
            </div>
          </div>
          <div class="pt-6">
            <button
              type="submit"
              class="w-full inline-flex justify-center py-3 px-6 border border-transparent shadow-sm text-lg font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
            >
              Update Profile
            </button>
          </div>
        </form>
      </div>
    </div>
<!--drawer-->
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


    
    <script>
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
