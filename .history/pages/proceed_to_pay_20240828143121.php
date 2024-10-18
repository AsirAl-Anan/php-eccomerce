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
    <style>
        /* Custom styles using Tailwind CSS's @apply directive */
        .tab-container {
            @apply flex border-b border-gray-200 mb-4;
        }

        .tab {
            @apply flex-1 py-3 text-center cursor-pointer border border-gray-200 bg-gray-100 text-gray-700 font-medium;
        }

        .tab.active {
            @apply bg-white font-semibold border-t-2 border-orange-500;
        }

        .payment-form {
            @apply hidden;
        }

        .payment-form.active {
            @apply block;
        }

        .form-group label {
            @apply block mb-2 font-semibold text-gray-700;
        }

        .form-group input {
            @apply w-full px-4 py-2 border border-gray-300 rounded-lg;
        }

        .card-icons img {
            @apply h-6 mr-3;
        }

        .order-summary {
            @apply bg-gray-100 p-6 rounded-lg text-right;
        }

        .order-summary .total {
            @apply text-2xl text-orange-500;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Proceed to Pay</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2">
                <div class="tab-container">
                    <div class="tab active" onclick="showPaymentForm('credit_card')">Credit/Debit Card</div>
                    <div class="tab" onclick="showPaymentForm('paypal')">PayPal</div>
                    <div class="tab" onclick="showPaymentForm('cash_on_delivery')">Cash On Delivery</div>
                </div>

                <form method="POST" id="payment_form">
                    <div id="credit_card_form" class="payment-form active">
                        <div class="card-icons flex mb-4">
                            <img src="visa.png" alt="Visa">
                            <img src="mastercard.png" alt="MasterCard">
                            <img src="amex.png" alt="Amex">
                        </div>
                        <div class="form-group mb-4">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                    </div>

                    <div id="paypal_form" class="payment-form">
                        <div class="form-group mb-4">
                            <label for="paypal_email">PayPal Email</label>
                            <input type="email" id="paypal_email" name="paypal_email" placeholder="email@example.com">
                        </div>
                    </div>

                    <div id="cash_on_delivery_form" class="payment-form">
                        <p class="mb-4">An additional $5 will be charged for Cash on Delivery.</p>
                    </div>

                    <div class="terms mb-6">
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
                <div class="order-summary">
                    <div>Subtotal: $<?php echo number_format($order_details['total_amount'], 2); ?></div>
                    <div>Shipping: $0.00</div>
                    <div class="total">Total: $<?php echo number_format($order_details['total_amount'], 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showPaymentForm(method) {
            var tabs = document.querySelectorAll('.tab');
            tabs.forEach(function (tab) {
                tab.classList.remove('active');
            });

            var forms = document.querySelectorAll('.payment-form');
            forms.forEach(function (form) {
                form.classList.remove('active');
            });

            document.getElementById(method + '_form').classList.add('active');
            document.getElementById('payment_method').value = method;
            document.querySelector('.tab[onclick="showPaymentForm(\'' + method + '\')"]').classList.add('active');
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
