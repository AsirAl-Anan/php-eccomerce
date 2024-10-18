<?php
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : PHP_FLOAT_MAX;
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
getProductsForPage function to include filtering and sorting
function getProductsForPage($conn, $page, $sort, $min_price, $max_price, $date_filter) {
    $sql = "SELECT p.*, GROUP_CONCAT(pi.image_file) as images 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id 
            WHERE p.page = ? AND p.price BETWEEN ? AND ?";

    if ($date_filter !== 'all') {
        $sql .= " AND p.created_at >= DATE_SUB(NOW(), INTERVAL 1 " . $date_filter . ")";
    }

    $sql .= " GROUP BY p.id";

    switch ($sort) {
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'date_desc':
            $sql .= " ORDER BY p.created_at DESC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY p.name DESC";
            break;
        case 'name_asc':
        default:
            $sql .= " ORDER BY p.name ASC";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdd", $page, $min_price, $max_price);
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
                <div class="p-4 flex flex-col flex-grow">
                    <div class="h-16 mb-2">
                        <h3 class="font-bold text-xl line-clamp-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                    </div>
                    <div class="h-20 mb-4">
                        <p class="text-gray-700 text-base line-clamp-3"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                    <div class="mt-auto">
                        <p class="text-gray-900 font-bold text-xl mb-2">$<?php echo number_format($product['price'], 2); ?></p>
                        <div class="flex items-center mb-4">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-5 h-5 fill-current <?php echo $i <= $product['rating'] ? 'text-yellow-500' : 'text-gray-400'; ?>" viewBox="0 0 24 24">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>
                            <?php endfor; ?>
                            <span class="ml-2 text-gray-600"><?php echo $product['rating']; ?></span>
                        </div>
                        <div class="flex space-x-2">
                        <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex-grow">
    Add to Cart
</button>

                    <button onclick="buyNow(<?php echo $product['id']; ?>)" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex-grow">
                        Buy Now
                    </button>
                </div>
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