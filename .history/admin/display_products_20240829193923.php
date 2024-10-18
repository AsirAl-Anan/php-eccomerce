<?php

function getProductsForPage($conn, $page) {
    $sql = "SELECT p.*, GROUP_CONCAT(pi.image_file) as images 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id 
            WHERE p.page = ?
            GROUP BY p.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $page);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function displayProducts($products) {
    $isLoggedIn = isset($_SESSION['user_id']);
    ?>
    
    <div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($products as $product): ?>
            <div class="border rounded-lg overflow-hidden shadow-lg flex flex-col h-full">
                <div class="relative h-64 bg-white flex items-center justify-center">
                    <?php
                    $images = explode(',', $product['images']);
                    $imagePath = "../uploads/" . htmlspecialchars($images[0]); // Use only the first image for display
                    ?>
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-auto object-contain">
                </div>
                <div class="p-4 flex flex-col flex-grow">
                    <h3 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($product['description']); ?></p>
                    <p class="text-gray-900 font-bold text-lg mb-4">$<?php echo number_format($product['price'], 2); ?></p>
                    <div class="flex space-x-2 mt-auto">
                        <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center justify-center flex-grow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.38 6.9A2 2 0 007.59 22h8.82a2 2 0 001.97-1.62L17 13M7 13l-1-2M9 15v6M15 15v6M5 6h14" />
                            </svg>
                            Add to Cart
                        </button>
                        <button onclick="buyNow(<?php echo $product['id']; ?>)" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex-grow">
                            Buy Now
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

    <script>
        function buyNow(productId) {
    window.location.href = `product.php?id=${productId}`;
}
    function changeImage(button, direction) {
        const container = button.closest('.relative');
        const images = container.querySelectorAll('img');
        let currentIndex = Array.from(images).findIndex(img => !img.classList.contains('hidden'));
        images[currentIndex].classList.add('hidden');
        currentIndex = (currentIndex + direction + images.length) % images.length;
        images[currentIndex].classList.remove('hidden');
    }

    function addToCart(productId, productName, productPrice) {
        console.log('Adding to cart:', productId, productName, productPrice);

        fetch('../admin/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&name=${encodeURIComponent(productName)}&price=${productPrice}`
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showAlert('Product added to cart successfully!');
                updateCartCount();
            } else {
                showAlert('Failed to add product to cart.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while adding to cart.');
        });
    }

    function showLoginAlert() {
        showAlert('Please log in to add items to your cart.');
    }

    function showAlert(message) {
        const alertBox = document.createElement('div');
        alertBox.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
        alertBox.textContent = message;
        document.body.appendChild(alertBox);
        setTimeout(() => {
            alertBox.remove();
        }, 3000);
    }

    function updateCartCount() {
        fetch('../admin/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
            }
        });
    }

    // Initial cart count update
    updateCartCount();
    </script>
    <?php
}
?>