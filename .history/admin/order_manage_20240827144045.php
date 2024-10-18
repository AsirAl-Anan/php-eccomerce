<?php
session_start();
require_once '../config/config.php';
require_once 'fetch_admin_data.php';
// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

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
                            <a href="users_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Users</a>
                            <a href="manage_product.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Products</a>
                            <a href="order_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Orders</a>
                            <a href="edit_carousel.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Carousel</a>

                            <a href="edit_about_page.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Edit About page</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <button class="p-1 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                            <span class="sr-only">View notifications</span>
                            <i class="fas fa-bell"></i>
                        </button>
                        <div class="ml-3 relative">
                            <div>
                                <button class="max-w-xs bg-gray-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Admin Panel</h1>
     



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
         


        </script>






</body>

</html>