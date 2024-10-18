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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Proceed to Pay</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2">
                <div class="flex border-b border-gray-200 mb-4">
                    <div class="flex-1 py-3 text-center cursor-pointer border-b-2 border-orange-500 font-semibold text-orange-600" onclick="showPaymentForm('credit_card')">
                        Credit/Debit Card
                    </div>
                    <div class="flex-1 py-3 text-center cursor-pointer text-gray-700 hover:text-orange-500 hover:border-b-2 hover:border-orange-500" onclick="showPaymentForm('paypal')">
                        PayPal
                    </div>
                    <div class="flex-1 py-3 text-center cursor-pointer text-gray-700 hover:text-orange-500 hover:border-b-2 hover:border-orange-500" onclick="showPaymentForm('cash_on_delivery')">
                        Cash On Delivery
                    </div>
                </div>

                <form method="POST" id="payment_form">
                    <div id="credit_card_form" class="block">
                        <div class="flex mb-4">
                            <img src="visa.png" alt="Visa" class="h-6 mr-3">
                            <img src="mastercard.png" alt="MasterCard" class="h-6 mr-3">
                            <img src="amex.png" alt="Amex" class="h-6">
                        </div>
                        <div class="mb-4">
                            <label for="card_number" class="block mb-2 font-semibold text-gray-700">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="expiry_date" class="block mb-2 font-semibold text-gray-700">Expiry Date</label>
                                <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label for="cvv" class="block mb-2 font-semibold text-gray-700">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>

                    <div id="paypal_form" class="hidden">
                        <div class="mb-4">
                            <label for="paypal_email" class="block mb-2 font-semibold text-gray-700">PayPal Email</label>
                            <input type="email" id="paypal_email" name="paypal_email" placeholder="email@example.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>

                    <div id="cash_on_delivery_form" class="hidden">
                        <p class="mb-4 text-gray-700">An additional $5 will be charged for Cash on Delivery.</p>
                    </div>

                    <div class="mb-6">
                        <input type="checkbox" id="terms_accepted" name="terms_accepted" class="mr-2">
                        <label for="terms_accepted" class="text-gray-700">I accept the terms and conditions</label>
                    </div>

                    <input type="hidden" name="payment_method" id="payment_method" value="credit_card">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Confirm Order
                    </button>
                </form>
            </div>

            <div>
                <div class="bg-gray-100 p-6 rounded-lg text-right">
                    <div>Subtotal: $<?php echo number_format($order_details['total_amount'], 2); ?></div>
                    <div>Shipping: $0.00</div>
                    <div class="text-2xl text-orange-500">Total: $<?php echo number_format($order_details['total_amount'], 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showPaymentForm(method) {
            document.getElementById('credit_card_form').classList.add('hidden');
            document.getElementById('paypal_form').classList.add('hidden');
            document.getElementById('cash_on_delivery_form').classList.add('hidden');

            document.getElementById(method + '_form').classList.remove('hidden');
            document.getElementById('payment_method').value = method;

            var tabs = document.querySelectorAll('.flex-1');
            tabs.forEach(function (tab) {
                tab.classList.remove('border-b-2', 'border-orange-500', 'font-semibold', 'text-orange-600');
                tab.classList.add('text-gray-700');
            });

            document.querySelector('.flex-1[onclick="showPaymentForm(\'' + method + '\')"]').classList.add('border-b-2', 'border-orange-500', 'font-semibold', 'text-orange-600');
            document.querySelector('.flex-1[onclick="showPaymentForm(\'' + method + '\')"]').classList.remove('text-gray-700');
        }

        document.getElementById('payment_form').addEventListener('submit', function(e) {
            var paymentMethod = document.getElementById('payment_method').value;
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method.');
            }
        });
    </script>
</body>
</html>
