<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['order_details'])) {
    header('Location: checkout.php');
    exit();
}

$order_details = $_SESSION['order_details'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $errors = [];

    if ($payment_method == 'credit_card') {
        // Validate credit card information
        if (strlen($_POST['card_number']) != 16) {
            $errors[] = "Credit card number must be 16 digits.";
        }
        if (strlen($_POST['cvv']) != 3) {
            $errors[] = "CVV must be 3 digits.";
        }
        // Add more validation as needed
    } elseif ($payment_method == 'paypal') {
        // Validate PayPal information
        if (!filter_var($_POST['paypal_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid PayPal email address.";
        }
        // Add more validation as needed
    } elseif ($payment_method == 'cash_on_delivery') {
        // Add $5 to the total for cash on delivery
        $order_details['total_amount'] += 5;
    }

    if (!isset($_POST['terms_accepted'])) {
        $errors[] = "You must accept the terms and conditions.";
    }

    if (empty($errors)) {
        // Process the order
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'Processing', ?)");
            $stmt->bind_param("ids", $order_details['user_id'], $order_details['total_amount'], $order_details['shipping_address']);
            $stmt->execute();
            $order_id = $conn->insert_id;

            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($order_details['items'] as $item) {
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }

            // Clear the cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $order_details['user_id']);
            $stmt->execute();

            $conn->commit();
            unset($_SESSION['order_details']);
            echo "<script>alert('Your order is placed successfully. An order cannot be cancelled after 30 minutes.'); window.location.href = 'order-track.php?order_id=" . $order_id . "';</script>";
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "An error occurred while processing your order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceed to Pay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function showPaymentForm(method) {
            document.getElementById('credit_card_form').style.display = 'none';
            document.getElementById('paypal_form').style.display = 'none';
            document.getElementById('cash_on_delivery_form').style.display = 'none';
            document.getElementById(method + '_form').style.display = 'block';
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Proceed to Pay</h1>
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-4">Select Payment Method</h2>
                <div class="flex mb-4">
                    <button onclick="showPaymentForm('credit_card')" class="mr-2 px-4 py-2 bg-blue-500 text-white rounded">Credit/Debit Card</button>
                    <button onclick="showPaymentForm('paypal')" class="mr-2 px-4 py-2 bg-blue-500 text-white rounded">PayPal</button>
                    <button onclick="showPaymentForm('cash_on_delivery')" class="px-4 py-2 bg-blue-500 text-white rounded">Cash on Delivery</button>
                </div>
            </div>
            <form method="POST" id="payment_form">
                <div id="credit_card_form" style="display: none;">
                    <h3 class="text-lg font-bold mb-2">Credit/Debit Card Information</h3>
                    <div class="mb-4">
                        <label for="card_number" class="block text-gray-700 text-sm font-bold mb-2">Card Number</label>
                        <input type="text" id="card_number" name="card_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label for="expiry_date" class="block text-gray-700 text-sm font-bold mb-2">Expiry Date</label>
                        <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <div class="mb-4">
                        <label for="cvv" class="block text-gray-700 text-sm font-bold mb-2">CVV</label>
                        <input type="text" id="cvv" name="cvv" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                </div>
                <div id="paypal_form" style="display: none;">
                    <h3 class="text-lg font-bold mb-2">PayPal Information</h3>
                    <div class="mb-4">
                        <label for="paypal_email" class="block text-gray-700 text-sm font-bold mb-2">PayPal Email</label>
                        <input type="email" id="paypal_email" name="paypal_email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                </div>
                <div id="cash_on_delivery_form" style="display: none;">
                    <h3 class="text-lg font-bold mb-2">Cash on Delivery</h3>
                    <p class="mb-4">An additional $5 will be charged for Cash on Delivery.</p>
                </div>
                <div class="mb-4">
                    <input type="checkbox" id="terms_accepted" name="terms_accepted" required>
                    <label for="terms_accepted" class="ml-2 text-gray-700">I accept the terms and conditions</label>
                </div>
                <input type="hidden" name="payment_method" id="payment_method" value="">
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                    Confirm Order
                </button>
            </form>
        </div>
        <div class="mt-6">
            <h2 class="text-xl font-bold mb-4">Order Details</h2>
            <?php foreach ($order_details['items'] as $item): ?>
                <div class="flex justify-between items-center bg-gray-50 p-4 mb-4 rounded-lg">
                    <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                    <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="flex justify-between items-center font-bold text-xl mt-4">
                <span>Total:</span>
                <span>$<?php echo number_format($order_details['total_amount'], 2); ?></span>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('payment_form').addEventListener('submit', function(e) {
            var paymentMethod = '';
            if (document.getElementById('credit_card_form').style.display !== 'none') {
                paymentMethod = 'credit_card';
            } else if (document.getElementById('paypal_form').style.display !== 'none') {
                paymentMethod = 'paypal';
            } else if (document.getElementById('cash_on_delivery_form').style.display !== 'none') {
                paymentMethod = 'cash_on_delivery';
            }
            document.getElementById('payment_method').value = paymentMethod;
        });
    </script>
</body>
</html>