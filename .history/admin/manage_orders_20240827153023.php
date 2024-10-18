<?php
session_start();
require_once '../config/config.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit();
}

// Update order status
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
    <title>Manage Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
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
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Profile</a>
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
<body class="bg-gray-100">
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
                            <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="Processed" <?php echo $order['status'] == 'Processed' ? 'selected' : ''; ?>>Processed</option>
                            <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                        <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded ml-2">Update Status</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>