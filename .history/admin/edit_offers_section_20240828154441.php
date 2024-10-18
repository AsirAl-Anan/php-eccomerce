<?php
require_once '../config/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new slide
                $stmt = $conn->prepare("INSERT INTO offer_slides (image_file, offer_end_time, slide_order) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $_FILES['image']['name'], $_POST['offer_end_time'], $_POST['slide_order']);
                $stmt->execute();
                move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/offer_slides/" . $_FILES['image']['name']);
                break;
            case 'edit':
                // Edit existing slide
                $stmt = $conn->prepare("UPDATE offer_slides SET offer_end_time = ?, slide_order = ? WHERE id = ?");
                $stmt->bind_param("sii", $_POST['offer_end_time'], $_POST['slide_order'], $_POST['id']);
                $stmt->execute();
                if ($_FILES['image']['name']) {
                    $stmt = $conn->prepare("UPDATE offer_slides SET image_file = ? WHERE id = ?");
                    $stmt->bind_param("si", $_FILES['image']['name'], $_POST['id']);
                    $stmt->execute();
                    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/offer_slides/" . $_FILES['image']['name']);
                }
                break;
            case 'delete':
                // Delete slide
                $stmt = $conn->prepare("DELETE FROM offer_slides WHERE id = ?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
                break;
        }
    }
}

// Fetch all offer slides
$result = $conn->query("SELECT * FROM offer_slides ORDER BY slide_order");
$offer_slides = $result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Offer Slides</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    









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
                            <a href="create_admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Create Admin</a>
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














    <h1 class="text-3xl font-bold mb-6">Edit Offer Slides</h1>

    <!-- Add new slide form -->
    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-bold mb-4">Add New Slide</h2>
    <input type="hidden" name="action" value="add">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="file" name="image" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="datetime-local" name="offer_end_time"  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="number" name="slide_order" placeholder="Slide Order" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">Add Slide</button>
</form>

    <!-- List of existing slides -->
    <?php foreach ($offer_slides as $slide): ?>
    <form action="" method="POST" enctype="multipart/form-data" class="border-b border-gray-200 pb-4 mb-4">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="file" name="image" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="datetime-local" name="offer_end_time" value="<?php echo date('Y-m-d\TH:i', strtotime($slide['offer_end_time'])); ?>"  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" name="slide_order" value="<?php echo $slide['slide_order']; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mt-4 flex space-x-2">
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">Update</button>
            <button type="submit" name="action" value="delete" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">Delete</button>
        </div>
    </form>
<?php endforeach; ?>
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