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