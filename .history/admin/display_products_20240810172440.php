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
    ?>
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="border rounded-lg overflow-hidden shadow-lg">
                    <div class="relative h-64">
                        <?php
                        $images = explode(',', $product['images']);
                        foreach ($images as $index => $image):
                            $imagePath = "../uploads/" . htmlspecialchars($image);
                        ?>
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="absolute inset-0 w-full h-full object-cover <?php echo $index === 0 ? '' : 'hidden'; ?>">
                        <?php endforeach; ?>
                        <?php if (count($images) > 1): ?>
                            <button class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 p-2 rounded-r" onclick="changeImage(this, -1)">❮</button>
                            <button class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-50 p-2 rounded-l" onclick="changeImage(this, 1)">❯</button>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-xl mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-700 text-base mb-2"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="text-gray-900 font-bold text-xl mb-2">$<?php echo number_format($product['price'], 2); ?></p>
                        <div class="flex items-center mb-4">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-5 h-5 fill-current <?php echo $i <= $product['rating'] ? 'text-yellow-500' : 'text-gray-400'; ?>" viewBox="0 0 24 24">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                            <?php endfor; ?>
                            <span class="ml-2 text-gray-600"><?php echo $product['rating']; ?></span>
                        </div>
                        <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>); console.log('Add to Cart clicked');" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
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