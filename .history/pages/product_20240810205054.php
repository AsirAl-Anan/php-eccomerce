<?php
require_once '../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$sql = "SELECT p.*, GROUP_CONCAT(pi.image_file) as images 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id 
        WHERE p.id = ?
        GROUP BY p.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: index.php');
    exit();
}

// Your HTML structure here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="product-images">
                <?php
                $images = explode(',', $product['images']);
                foreach ($images as $image):
                    $imagePath = "uploads/" . htmlspecialchars($image);
                ?>
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-auto object-cover mb-4">
                <?php endforeach; ?>
            </div>
            <div class="product-details">
                <p class="text-xl font-bold mb-2">$<?php echo number_format($product['price'], 2); ?></p>
                <p class="mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                <div class="flex items-center mb-4">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg class="w-5 h-5 fill-current <?php echo $i <= $product['rating'] ? 'text-yellow-500' : 'text-gray-400'; ?>" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                    <?php endfor; ?>
                    <span class="ml-2 text-gray-600"><?php echo $product['rating']; ?></span>
                </div>
                <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <script>
    // Add your addToCart and other necessary JavaScript functions here
    </script>
</body>
</html>