<?php
session_start();
require_once '../config/config.php';

require_once 'fetch_admin_data.php';
// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Get team member ID from URL
if (!isset($_GET['id'])) {
    header("Location: manage_teammembers.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch team member details
$team_query = $conn->prepare("SELECT * FROM team_members WHERE id = ?");
$team_query->bind_param("i", $id);
$team_query->execute();
$team_result = $team_query->get_result();
$team_member = $team_result->fetch_assoc();

// Handle form submission for editing team member
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST['name']);
    $position = sanitize_input($_POST['position']);

    // Update member details in the database
    $stmt = $conn->prepare("UPDATE team_members SET name = ?, position = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $position, $id);
    $stmt->execute();
    $stmt->close();

    // Handle image upload
    if ($_FILES['image']['name']) {
        $target_dir = "../uploads/group-members/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_name = basename($_FILES["image"]["name"]);
                $stmt = $conn->prepare("UPDATE team_members SET image = ? WHERE id = ?");
                $stmt->bind_param("si", $image_name, $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: manage_teammembers.php");
    exit;
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Team Member</title>
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
    <h1 class="text-3xl font-bold mb-8">Edit Team Member</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($team_member['name']); ?>" class="w-full p-2 border rounded" required>
        </div>

        <div class="mb-4">
            <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
            <input type="text" name="position" id="position" value="<?php echo htmlspecialchars($team_member['position']); ?>" class="w-full p-2 border rounded" required>
        </div>

        <div class="mb-4">
            <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
            <input type="file" name="image" id="image" accept="image/*">
            <p class="text-sm text-gray-500">Current image: <img src="../uploads/group-members/<?php echo htmlspecialchars($team_member['image']); ?>" alt="<?php echo htmlspecialchars($team_member['name']); ?>" class="h-12 w-12 object-cover rounded-full mt-2"></p>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Team Member</button>
    </form>

    <div class="mt-8">
        <a href="manage_teammembers.php" class="text-blue-500 hover:underline">Back to Manage Team Members</a>
    </div>
</div>
<script>

    
            //admin-data and drop down
            document.getElementById('user-menu-button').addEventListener('click', function() {
        var menu = document.getElementById('user-menu');
        menu.classList.toggle('hidden');
    });
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        document.getElementById('current_image').src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
</script>
</body>
</html>
