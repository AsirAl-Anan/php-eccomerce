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
    <!-- ... (Keep the existing navigation bar code) ... -->

    <!-- Profile content -->
    <div class="container mx-auto px-4 py-12 flex">
      <!-- Sidebar -->
      <div class="w-1/4 bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-8">
          <img
            src="<?php echo htmlspecialchars($user['profile_picture']); ?>"
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
            <a href="#" class="text-gray-700 hover:text-gray-900">Orders</a>
          </li>
          <li>
            <a href="#" class="text-gray-700 hover:text-gray-900">Wishlist</a>
          </li>
          <li>
            <a href="#" class="text-gray-700 hover:text-gray-900">Settings</a>
          </li>
          <li>
            <a href="#" class="text-gray-700 hover:text-gray-900">Logout</a>
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
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg"
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
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg"
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
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg"
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
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg"
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
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg"
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
                class="mt-2 block w-full rounded-lg text-lg text-gray-700"
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
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg"
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
                class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 text-lg"
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

    <!-- footer -->
    <!-- ... (Keep the existing footer code) ... -->
  </body>
</html>
