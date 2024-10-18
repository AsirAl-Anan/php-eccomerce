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
  <body class="bg-white text-black">
    <!-- navigation bar -->
    <!-- ... (Keep the existing navigation bar code) ... -->

    <!-- Profile content -->
    <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg">
  <!-- Header Section -->
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center">
      <img class="h-20 w-20 rounded-full object-cover" src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture">
      <div class="ml-4">
        <h2 class="text-2xl font-semibold text-gray-800"><?php echo $user['username']; ?></h2>
        <p class="text-gray-600"><?php echo $user['email']; ?></p>
      </div>
    </div>
    <a href="edit_profile.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Edit Profile</a>
  </div>

  <!-- Profile Details -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Personal Information -->
    <div class="bg-gray-100 p-4 rounded-lg">
      <h3 class="text-lg font-medium text-gray-700 mb-4">Personal Information</h3>
      <p class="text-gray-600"><strong>Email:</strong> <?php echo $user['email']; ?></p>
      <!-- <p class="text-gray-600 mt-2"><strong>Phone:</strong> <?php echo $user['phone']; ?></p> -->
      <p class="text-gray-600 mt-2"><strong>Address:</strong> <?php echo $user['address']; ?></p>
    </div>

    <!-- Account Settings -->
    <div class="bg-gray-100 p-4 rounded-lg">
      <h3 class="text-lg font-medium text-gray-700 mb-4">Account Settings</h3>
      <a href="change_password.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded w-full text-left">Change Password</a>
      <a href="notification_settings.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded w-full text-left mt-4">Notification Settings</a>
    </div>
  </div>

  <!-- Recent Orders -->
  <div class="bg-gray-100 p-4 rounded-lg">
    <h3 class="text-lg font-medium text-gray-700 mb-4">Recent Orders</h3>
    <ul class="divide-y divide-gray-300">
      <?php foreach ($orders as $order): ?>
      <li class="py-2 flex justify-between">
        <span><?php echo $order['order_number']; ?></span>
        <span class="text-gray-600"><?php echo $order['order_date']; ?></span>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Footer Section -->
  <div class="mt-6 flex justify-between items-center">
    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Log Out</a>
    <a href="support.php" class="text-blue-500 hover:underline">Need help?</a>
  </div>
</div>

    <!-- footer -->
    <!-- ... (Keep the existing footer code) ... -->
  </body>
</html>
