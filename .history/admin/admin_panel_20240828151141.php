<?php
session_start();
require_once '../config/config.php';
echo "<!-- Debug: admin_data contents: ";
var_export($admin_data);
echo " -->";

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}
require_once 'fetch_admin_data.php';

// Function to get all users
function getAllUsers($conn)
{
    $sql = "SELECT id, username, email, address, age, gender, payment_info, created_at FROM users";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $conn->query("SET foreign_key_checks = 0");

    try {
        $sql = "DELETE FROM orders WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        echo "User and related data deleted successfully";
    } catch (Exception $e) {
        echo "Error deleting user: " . $e->getMessage();
    } finally {
        $conn->query("SET foreign_key_checks = 1");
    }
}
// Handle user edit
if (isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $payment_info = $_POST['payment_info'];

    $sql = "UPDATE users SET username = ?, email = ?, address = ?, age = ?, gender = ?, payment_info = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssissi", $username, $email, $address, $age, $gender, $payment_info, $user_id);

    if ($stmt->execute()) {
        echo "User updated successfully!";
    } else {
        echo "Error updating user: " . $conn->error;
    }
}
$users = getAllUsers($conn);

// add product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];
    $page = $_POST['page'];

    $sql = "INSERT INTO products (name, description, price, rating, page) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdds", $name, $description, $price, $rating, $page);

    if ($stmt->execute()) {
        $product_id = $conn->insert_id;

        // Handle image uploads
        // Handle image uploads
        if (isset($_FILES['product_images'])) {
            $upload_dir = '../uploads/'; // Make sure this directory exists and is writable

            foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['product_images']['name'][$key];
                $file_size = $_FILES['product_images']['size'][$key];
                $file_tmp = $_FILES['product_images']['tmp_name'][$key];
                $file_type = $_FILES['product_images']['type'][$key];

                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $extensions = array("jpeg", "jpg", "png");

                if (in_array($file_ext, $extensions)) {
                    $unique_name = uniqid() . '.' . $file_ext;
                    $file_path = $upload_dir . $unique_name;

                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $sql = "INSERT INTO product_images (product_id, image_file) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("is", $product_id, $unique_name);
                        $stmt->execute();
                    }
                }
            }
        }

        echo "Product added successfully!";
    } else {
        echo "Error adding product.";
    }
}
// Function to get all products
function getAllProducts($conn)
{
    $sql = "SELECT p.*, GROUP_CONCAT(pi.image_url) as images 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id 
            GROUP BY p.id";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$products = getAllProducts($conn);


// Function to get all carousel slides
function getAllCarouselSlides($conn)
{
    $sql = "SELECT * FROM carousel_slides ORDER BY slide_order";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$carousel_slides = getAllCarouselSlides($conn);

// Handle adding new slide
if (isset($_POST['add_slide'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $slide_order = $_POST['slide_order'];

    $image_file = uploadImage($_FILES['image_file']);
    if ($image_file === false) {
        echo "Error uploading image.";
    } else {
        $sql = "INSERT INTO carousel_slides (image_file, title, description, button_text, button_link, slide_order) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $image_file, $title, $description, $button_text, $button_link, $slide_order);
        if ($stmt->execute()) {
            echo "Slide added successfully!";
        } else {
            echo "Error adding slide.";
        }
    }
}
// Handle updating slide
if (isset($_POST['update_slide'])) {
    $id = $_POST['slide_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $slide_order = $_POST['slide_order'];

    if ($_FILES['image_file']['size'] > 0) {
        $image_file = uploadImage($_FILES['image_file']);
        if ($image_file === false) {
            echo "Error uploading image.";
            exit;
        }
        $sql = "UPDATE carousel_slides SET image_file = ?, title = ?, description = ?, button_text = ?, button_link = ?, slide_order = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssii", $image_file, $title, $description, $button_text, $button_link, $slide_order, $id);
    } else {
        $sql = "UPDATE carousel_slides SET title = ?, description = ?, button_text = ?, button_link = ?, slide_order = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $title, $description, $button_text, $button_link, $slide_order, $id);
    }

    if ($stmt->execute()) {
        echo "Slide updated successfully!";
    } else {
        echo "Error updating slide.";
    }
}
function uploadImage($file)
{
    $target_dir = "../uploads/carousel/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_file_name = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_file_name;

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }

    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }

    // Allow certain file formats
    if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_file_name;
    } else {
        return false;
    }
}
// Handle deleting slide
if (isset($_POST['delete_slide'])) {
    $id = $_POST['slide_id'];
    $sql = "DELETE FROM carousel_slides WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$carousel_slides = getAllCarouselSlides($conn);



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
}

// Fetch all orders
$stmt = $conn->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Get counts for each table
function getCount($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Function to get data from a table
function getData($conn, $table, $limit = 5) {
    $sql = "SELECT * FROM $table LIMIT $limit";
    $result = $conn->query($sql);
    $data = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Get counts and data for each table
$tables = ['users', 'products', 'orders', 'team_members', 'admin_users'];
$dashboardData = [];

foreach ($tables as $table) {
    $dashboardData[$table] = [
        'count' => getCount($conn, $table),
        'data' => getData($conn, $table)
    ];
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 ">


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
                        <a href="manage_users.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Users</a>
                        <a href="manage_product.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Products</a>
                        <a href="order_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Orders</a>
                        <a href="edit_carousel.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Carousel</a>
                        <a href="edit_about_page.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Edit About page</a>
                        <a href="edit_offer_page.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Edit offer section</a>
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
        <h1 class="text-3xl font-bold mb-6">Admin Panel</h1>
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Users Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2">Total Users</h2>
                <p class="text-4xl font-bold text-blue-600"><?php echo $userCount; ?></p>
            </div>
            
            <!-- Products Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2">Total Products</h2>
                <p class="text-4xl font-bold text-green-600"><?php echo $productCount; ?></p>
            </div>
            
            <!-- Orders Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2">Total Orders</h2>
                <p class="text-4xl font-bold text-yellow-600"><?php echo $orderCount; ?></p>
            </div>
            
            <!-- Team Members Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2">Team Members</h2>
                <p class="text-4xl font-bold text-purple-600"><?php echo $teamMemberCount; ?></p>
            </div>
            
            <!-- Admin Users Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2">Admin Users</h2>
                <p class="text-4xl font-bold text-red-600"><?php echo $adminCount; ?></p>
            </div>
        </div>
    </div>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $colors = [
                'users' => 'blue',
                'products' => 'green',
                'orders' => 'yellow',
                'team_members' => 'purple',
                'admin_users' => 'red'
            ];

            foreach ($dashboardData as $table => $data): 
            ?>
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-2"><?= ucfirst(str_replace('_', ' ', $table)) ?></h2>
                <p class="text-4xl font-bold text-<?= $colors[$table] ?>-600 mb-4"><?= $data['count'] ?></p>
                <button 
                    @click="open = !open" 
                    class="bg-<?= $colors[$table] ?>-500 text-white px-4 py-2 rounded hover:bg-<?= $colors[$table] ?>-600 transition duration-300"
                >
                    View Details
                </button>
                <div x-show="open" class="mt-4 overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <?php foreach (array_keys($data['data'][0]) as $column): ?>
                                <th class="py-2 px-4 text-left"><?= ucfirst(str_replace('_', ' ', $column)) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['data'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                <td class="py-2 px-4"><?= htmlspecialchars($value) ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow-md rounded mb-4">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Username</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-left">Address</th>
                        <th class="py-3 px-6 text-center">Age</th>
                        <th class="py-3 px-6 text-center">Gender</th>
                        <th class="py-3 px-6 text-left">Payment Info</th>

                        <th class="py-3 px-6 text-center">Created At</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php foreach ($users as $user): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo $user['id']; ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['address']); ?></td>
                            <td class="py-3 px-6 text-center"><?php echo $user['age']; ?></td>
                            <td class="py-3 px-6 text-center"><?php echo htmlspecialchars($user['gender']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['payment_info']); ?></td>

                            <td class="py-3 px-6 text-center"><?php echo $user['created_at']; ?></td>
                            <td class="py-3 px-6 text-center">
                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</button>
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"
                                        onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>










    <!-- Edit User Modal -->
    <div id="editModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editForm" method="POST" action="">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit User</h3>
                        <div class="mt-2">
                            <input type="hidden" id="edit_user_id" name="user_id">
                            <div class="mb-4">
                                <label for="edit_username"
                                    class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                                <input type="text" id="edit_username" name="username"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="mb-4">
                                <label for="edit_email"
                                    class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                                <input type="email" id="edit_email" name="email"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="mb-4">
                                <label for="edit_address"
                                    class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
                                <textarea id="edit_address" name="address"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="edit_age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                                <input type="number" id="edit_age" name="age"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="mb-4">
                                <label for="edit_gender"
                                    class="block text-gray-700 text-sm font-bold mb-2">Gender:</label>
                                <select id="edit_gender" name="gender"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="edit_payment_info"
                                    class="block text-gray-700 text-sm font-bold mb-2">Payment Info:</label>
                                <input type="text" id="edit_payment_info" name="payment_info"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">
                                    <input type="checkbox" id="edit_email_verified" name="email_verified"
                                        class="mr-2 leading-tight">
                                    <span class="text-sm">Email Verified</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</button>

                        <button type="button" onclick="closeEditModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-4">Add New Product</h2>
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="name" class="block">Name:</label>
                <input type="text" id="name" name="name" required class="w-full p-2 border rounded">
            </div>
            <div>
                <label for="description" class="block">Description:</label>
                <textarea id="description" name="description" required class="w-full p-2 border rounded"></textarea>
            </div>
            <div>
                <label for="price" class="block">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required class="w-full p-2 border rounded">
            </div>
            <div>
                <label for="rating" class="block">Rating:</label>
                <input type="number" id="rating" name="rating" step="0.1" min="0" max="5" required
                    class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="edit_product_page" class="block text-gray-700 text-sm font-bold mb-2">Page:</label>
                <select id="edit_product_page" name="page"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="men">Men</option>
                    <option value="women">Women</option>
                    <option value="featured">Featured</option>
                    <option value="kids">Kids</option>
                </select>
            </div>
            <div>
                <div>
                    <label for="product_images" class="block">Product Images:</label>
                    <input type="file" id="product_images" name="product_images[]" accept=".jpg,.jpeg,.png" multiple
                        class="w-full p-2 border rounded">
                </div>
            </div>
            <button type="submit" name="add_product" class="bg-blue-500 text-white px-4 py-2 rounded">Add
                Product</button>
        </form>
        <h2 class="text-2xl font-bold mt-8 mb-4">Existing Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($products as $product): ?>
                <div class="border p-4 rounded">
                    <h3 class="font-bold"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Price: $<?php echo number_format($product['price'], 2); ?></p>
                    <p>Rating: <?php echo $product['rating']; ?>/5</p>
                    <p>Page: <?php echo htmlspecialchars($product['page']); ?></p>
                    <div class="mt-4">
                        <button onclick="openEditProductModal(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</button>
                        <form method="POST" action="" class="inline">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="delete_product"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"
                                onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Add this to handle product deletion -->
        <?php
        if (isset($_POST['delete_product'])) {
            $product_id = $_POST['product_id'];
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                echo "Product deleted successfully!";
            } else {
                echo "Error deleting product.";
            }
        }
        ?>

        <!-- Add this modal for editing products -->
        <div id="editProductModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <!-- Modal content -->
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form id="editProductForm" method="POST" action="">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Product</h3>
                            <div class="mt-2">
                                <input type="hidden" id="edit_product_id" name="product_id">
                                <div class="mb-4">
                                    <label for="edit_product_name"
                                        class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                                    <input type="text" id="edit_product_name" name="name"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_product_description"
                                        class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                                    <textarea id="edit_product_description" name="description"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="edit_product_price"
                                        class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
                                    <input type="number" id="edit_product_price" name="price" step="0.01"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_product_rating"
                                        class="block text-gray-700 text-sm font-bold mb-2">Rating:</label>
                                    <input type="number" id="edit_product_rating" name="rating" step="0.1" min="0"
                                        max="5"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_product_page"
                                        class="block text-gray-700 text-sm font-bold mb-2">Page:</label>
                                    <input type="text" id="edit_product_page" name="page"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" name="edit_product"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Save
                                Changes</button>
                            <button type="button" onclick="closeEditProductModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <h2 class="text-2xl font-bold mt-8 mb-4">Manage Carousel Slides</h2>

        <!-- Add new slide form -->
        <form method="POST" action="" enctype="multipart/form-data" class="mb-8">
            <h3 class="text-xl font-bold mb-2">Add New Slide</h3>
            <div class="grid grid-cols-2 gap-4">
                <input type="file" name="image_file" accept="image/*" required class="p-2 border rounded">
                <input type="text" name="title" placeholder="Title" required class="p-2 border rounded">
                <textarea name="description" placeholder="Description" class="p-2 border rounded"></textarea>
                <input type="text" name="button_text" placeholder="Button Text" class="p-2 border rounded">
                <input type="text" name="button_link" placeholder="Button Link" class="p-2 border rounded">
                <input type="number" name="slide_order" placeholder="Slide Order" required class="p-2 border rounded">
            </div>
            <button type="submit" name="add_slide" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Add
                Slide</button>
        </form>

        <!-- Existing slides -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($carousel_slides as $slide): ?>
                <div class="border p-4 rounded">
                    <!-- <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" alt="Slide image" class="w-full h-40 object-cover mb-2"> -->
                    <h3 class="font-bold"><?php echo htmlspecialchars($slide['title']); ?></h3>
                    <p><?php echo htmlspecialchars($slide['description']); ?></p>
                    <p>Button: <?php echo htmlspecialchars($slide['button_text']); ?></p>
                    <p>Order: <?php echo $slide['slide_order']; ?></p>
                    <div class="mt-4">
                        <button onclick="openEditSlideModal(<?php echo htmlspecialchars(json_encode($slide)); ?>)"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</button>
                        <form method="POST" action="" class="inline">
                            <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                            <button type="submit" name="delete_slide"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"
                                onclick="return confirm('Are you sure you want to delete this slide?')">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Edit slide modal (similar to your product edit modal) -->
        <!-- Edit Carousel Slide Modal -->
        <div id="editSlideModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form id="editSlideForm" method="POST" action="" enctype="multipart/form-data">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Carousel Slide
                            </h3>
                            <div class="mt-2">
                                <input type="hidden" id="edit_slide_id" name="slide_id">
                                <div class="mb-4">
                                    <label for="edit_image_file"
                                        class="block text-gray-700 text-sm font-bold mb-2">Image File:</label>
                                    <input type="file" id="edit_image_file" name="image_file" accept="image/*"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <img id="current_image" src="" alt="Current Image" class="mt-2 max-w-full h-auto"
                                        style="display: none;">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_title"
                                        class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                                    <input type="text" id="edit_title" name="title"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_description"
                                        class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                                    <textarea id="edit_description" name="description"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="edit_button_text"
                                        class="block text-gray-700 text-sm font-bold mb-2">Button Text:</label>
                                    <input type="text" id="edit_button_text" name="button_text"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_button_link"
                                        class="block text-gray-700 text-sm font-bold mb-2">Button Link:</label>
                                    <input type="text" id="edit_button_link" name="button_link"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_slide_order"
                                        class="block text-gray-700 text-sm font-bold mb-2">Slide Order:</label>
                                    <input type="number" id="edit_slide_order" name="slide_order"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" name="update_slide"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save Changes
                            </button>
                            <button type="button" onclick="closeEditSlideModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Manage Orders</h1>
            <div class="grid gap-4">
                <?php foreach ($orders as $order): ?>
                    <div class="bg-white shadow-md rounded px-4 py-2">
                        <h2 class="text-xl font-bold">Order #<?php echo $order['id']; ?></h2>
                        <p>User: <?php echo $order['username']; ?></p>
                        <p>Total: $<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p>Date: <?php echo $order['created_at']; ?></p>
                        <form method="POST" class="mt-2">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="new_status" class="border rounded px-2 py-1">
                                <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>
                                    Processing</option>
                                <option value="Processed" <?php echo $order['status'] == 'Processed' ? 'selected' : ''; ?>>
                                    Processed</option>
                                <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped
                                </option>
                                <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>
                                    Delivered</option>
                            </select>
                            <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded ml-2">Update
                                Status</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <script>

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

            // Add this to your openEditSlideModal function
            document.getElementById('current_image').src = '../uploads/carousel/' + slide.image_file;

            // Add this event listener
            document.getElementById('edit_image_file').addEventListener('change', function () {
                previewImage(this);
            });
            function openEditSlideModal(slide) {
                // Populate the modal fields with the slide data
                document.getElementById('edit_slide_id').value = slide.id;
                document.getElementById('edit_title').value = slide.title;
                document.getElementById('edit_description').value = slide.description;
                document.getElementById('edit_button_text').value = slide.button_text;
                document.getElementById('edit_button_link').value = slide.button_link;
                document.getElementById('edit_slide_order').value = slide.slide_order;

                // Display the current image
                document.getElementById('current_image').src = '../uploads/carousel/' + slide.image_file;
                document.getElementById('current_image').style.display = 'block';

                // Clear the file input
                document.getElementById('edit_image_file').value = '';

                // Show the modal
                document.getElementById('editSlideModal').classList.remove('hidden');
            }

            function closeEditSlideModal() {
                // Hide the modal
                document.getElementById('editSlideModal').classList.add('hidden');
            }

            function openEditModal(user) {
                // Populate the modal fields with the user data
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_address').value = user.address;
                document.getElementById('edit_age').value = user.age;
                document.getElementById('edit_gender').value = user.gender;
                document.getElementById('edit_payment_info').value = user.payment_info;

                // Show the modal
                document.getElementById('editModal').classList.remove('hidden');
            }

            function closeEditModal() {
                // Hide the modal
                document.getElementById('editModal').classList.add('hidden');
            }

            function openEditProductModal(product) {
                // Populate the modal fields with the product data
                document.getElementById('edit_product_id').value = product.id;
                document.getElementById('edit_product_name').value = product.name;
                document.getElementById('edit_product_description').value = product.description;
                document.getElementById('edit_product_price').value = product.price;
                document.getElementById('edit_product_rating').value = product.rating;
                document.getElementById('edit_product_page').value = product.page;

                // Show the modal
                document.getElementById('editProductModal').classList.remove('hidden');
            }

            function closeEditProductModal() {
                // Hide the modal
                document.getElementById('editProductModal').classList.add('hidden');
            }



        </script>

        <?php
        // Handle product edit
        if (isset($_POST['edit_product'])) {
            $product_id = $_POST['product_id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $rating = $_POST['rating'];
            $page = $_POST['page'];

            $sql = "UPDATE products SET name = ?, description = ?, price = ?, rating = ?, page = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssddsi", $name, $description, $price, $rating, $page, $product_id);

            if ($stmt->execute()) {
                echo "Product updated successfully!";
            } else {
                echo "Error updating product.";
            }
        }
        ?>





</body>

</html>