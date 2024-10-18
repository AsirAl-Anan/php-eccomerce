<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../loginsystem/login.php');
    exit();
}

$user_id = $_SESSION['id'];

//fetch full user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if it's a direct purchase or from cart
if (isset($_GET['product_id'])) {
    // Direct purchase
    $product_id = $_GET['product_id'];
    $stmt = $conn->prepare("
        SELECT p.*, pi.image_file, pi.image_url 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id 
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $cartItems = [['product_id' => $product['id'], 'name' => $product['name'], 'price' => $product['price'], 'quantity' => 1, 'image_file' => $product['image_file'], 'image_url' => $product['image_url']]];
    $total = $product['price'];
} else {
    // From cart
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, pi.image_file, pi.image_url 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id 
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    $total = array_reduce($cartItems, function ($carry, $item) {
        return $carry + ($item['price'] * $item['quantity']);
    }, 0);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process the order
    $conn->begin_transaction();
    try {
        $shipping_address = isset($_POST['new_address']) ? $_POST['new_address'] : $user['address'];

        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'Processing', ?)");
        $stmt->bind_param("ids", $user_id, $total, $shipping_address);
        $stmt->execute();
        $order_id = $conn->insert_id;

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Clear the cart if checkout is from cart
        if (!isset($_GET['product_id'])) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }

        $conn->commit();
        header('Location: order-track.php?order_id=' . $order_id);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "An error occurred while processing your order. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleAddressInput() {
            var newAddressInput = document.getElementById('new_address_input');
            var useNewAddress = document.getElementById('use_new_address');
            newAddressInput.style.display = useNewAddress.checked ? 'block' : 'none';
        }
    </script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Checkout</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left section (Product Details) -->
            <div class="md:col-span-2">
                <form method="POST" class="bg-white shadow-md rounded-lg p-6">
                    <!-- Shipping Address -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="shipping_address">
                            Shipping Address
                        </label>
                        <p class="mb-2"><?php echo htmlspecialchars($user['address']); ?></p>
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="use_new_address" name="use_new_address" onchange="toggleAddressInput()" class="mr-2">
                            <label for="use_new_address" class="text-gray-700">Use a different address</label>
                        </div>
                        <div id="new_address_input" style="display: none;">
                            <textarea name="new_address" id="new_address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="flex justify-between items-center bg-gray-50 p-4 mb-4 rounded-lg">
                            <div class="flex items-center">
                                <?php
                                $imageSrc = '';
                                if (!empty($item['image_file'])) {
                                    $imageSrc = "../uploads/" . htmlspecialchars($item['image_file']);
                                } elseif (!empty($item['image_url'])) {
                                    $imageSrc = htmlspecialchars($item['image_url']);
                                }

                                if ($imageSrc):
                                    ?>
                                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        class="w-16 h-16 object-cover mr-4 rounded-lg">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 mr-4 flex items-center justify-center rounded-lg">No image</div>
                                <?php endif; ?>
                                <div>
                                    <span class="font-semibold"><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                                    <br>
                                    <a href="product.php?id=<?php echo $item['product_id']; ?>"
                                        class="text-blue-500 hover:underline text-sm">View Product</a>
                                </div>
                            </div>
                            <span class="text-lg font-bold text-gray-800">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </form>
            </div>

            <!-- Right section (Order Summary) -->
            <div>
                <div class="bg-white shadow-md rounded-lg p-6 sticky top-8">
                    <h2 class="text-xl font-bold mb-6">Order Summary</h2>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-700">Subtotal:</span>
                        <span class="text-gray-900 font-semibold">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-700">Shipping:</span>
                        <span class="text-gray-900 font-semibold">$0.00</span>
                    </div>
                    <div class="flex justify-between items-center font-bold text-xl mb-6">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <button type="submit" form="order-form" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
