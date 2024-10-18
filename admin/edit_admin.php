<?php
session_start();
require_once '../config/config.php';

require_once 'fetch_admin_data.php';
// Function to get admin user by ID
function getAdminUser($conn, $id) {
    $sql = "SELECT * FROM admin_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to update admin user
function updateAdminUser($conn, $id, $username, $fullname, $email, $admin_id, $profile_picture) {
    $sql = "UPDATE admin_users SET username = ?, fullname = ?, email = ?, admin_id = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $username, $fullname, $email, $admin_id, $profile_picture, $id);
    return $stmt->execute();
}

$message = '';
$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : 0);
$admin_user = getAdminUser($conn, $id);

if (!$admin_user) {
    $message = "Admin user not found.";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $admin_id = $_POST['admin_id'];
    
    $profile_picture = $_POST['current_profile_picture'];
    
    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . "." . $filetype;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], "../uploads/".$new_filename)) {
                $profile_picture = $new_filename;
            }
        }
    }

    if (updateAdminUser($conn, $id, $username, $fullname, $email, $admin_id, $profile_picture)) {
        $message = "Admin user updated successfully.";
        $admin_user = getAdminUser($conn, $id); // Refresh admin user data
    } else {
        $message = "Error updating admin user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<nav class="bg-gray-800 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <a href="#" class="flex-shrink-0">
                    <img class="h-8 w-8" src="https://tailwindui.com/img/logos/workflow-mark-indigo-500.svg" alt="Workflow">
                </a>
                <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                        <a href="admin_panel.php" class="px-3 py-2 rounded-md text-sm font-medium bg-gray-900">Dashboard</a>
                        <a href="users_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Users</a>
                        <a href="manage_product.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Products</a>
                        <a href="order_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Orders</a>
                        <a href="edit_carousel.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Carousel</a>
                        <a href="manage_admin.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Admins</a>
                        <a href="manage_teammembers.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Team members</a>
                        <a href="edit_about_page.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Edit About page</a>
                        <a href="edit_offers_section.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Edit offer section</a>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    <button
                        class="p-1 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                        <span class="sr-only">View notifications</span>
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="ml-3 relative">
                        <div>
                            <button id="user-menu-button" class="max-w-xs bg-gray-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                                <span class="sr-only">Open user menu</span>
                                <?php
$profile_picture_path = isset($admin_data['profile_picture']) ? '../uploads/' . $admin_data['profile_picture'] : '../uploads/default_avatar.jpg';
?>
                                <img class="h-8 w-8 rounded-full" src="<?php echo htmlspecialchars($profile_picture_path); ?>" alt="Admin profile">
                            </button>
                        </div>
                        <!-- Dropdown menu -->
                        <div id="user-menu" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 hidden z-10">
                            <a href="admin-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Profile</a>
                            <a href="create_admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Create new admin</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="-mr-2 flex md:hidden">
                <button type="button"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</nav>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Admin User</h1>
        <?php if ($message): ?>
            <div class="<?php echo strpos($message, 'successfully') !== false ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>
        <?php if ($admin_user): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <input type="hidden" name="id" value="<?php echo $admin_user['id']; ?>">
                <input type="hidden" name="current_profile_picture" value="<?php echo $admin_user['profile_picture']; ?>">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                        Username
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" type="text" name="username" value="<?php echo htmlspecialchars($admin_user['username']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="fullname">
                        Full Name
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="fullname" type="text" name="fullname" value="<?php echo htmlspecialchars($admin_user['fullname']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" type="email" name="email" value="<?php echo htmlspecialchars($admin_user['email']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="admin_id">
                        Admin ID
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="admin_id" type="text" name="admin_id" value="<?php echo htmlspecialchars($admin_user['admin_id']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="profile_picture">
                        Profile Picture
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="profile_picture" type="file" name="profile_picture">
                    <?php if ($admin_user['profile_picture']): ?>
                        <p class="text-sm text-gray-500 mt-1">Current: <?php echo htmlspecialchars($admin_user['profile_picture']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Update Admin User
                    </button>
                    <a href="manage_admin.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Back to Admin List
                    </a>
                </div>
            </form>
        <?php else: ?>
            <p class="text-red-500">Admin user not found. <a href="manage.admin.php" class="text-blue-500 hover:text-blue-800">Return to admin list</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>