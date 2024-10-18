<?php
session_start();
require_once '../config/config.php';
require_once 'fetch_admin_data.php';
// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}


// add product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];
    $page = $_POST['page'];

    $sql = "INSERT INTO products (name, description, price, rating, page) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdds", $name, $description, $price, $rating, $page);

    if ($stmt->execute()) {
        $product_id = $conn->insert_id;

        // Handle image uploads
        // Handle image uploads
        if (isset($_FILES['product_images'])) {
            $upload_dir = '../uploads/'; // Make sure this directory exists and is writable

            foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['product_images']['name'][$key];
                $file_size = $_FILES['product_images']['size'][$key];
                $file_tmp = $_FILES['product_images']['tmp_name'][$key];
                $file_type = $_FILES['product_images']['type'][$key];

                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $extensions = array("jpeg", "jpg", "png");

                if (in_array($file_ext, $extensions)) {
                    $unique_name = uniqid() . '.' . $file_ext;
                    $file_path = $upload_dir . $unique_name;

                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $sql = "INSERT INTO product_images (product_id, image_file) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("is", $product_id, $unique_name);
                        $stmt->execute();
                    }
                }
            }
        }

        echo "Product added successfully!";
    } else {
        echo "Error adding product.";
    }
}
// Function to get all products
function getAllProducts($conn)
{
    $sql = "SELECT p.*, GROUP_CONCAT(pi.image_url) as images 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id 
            GROUP BY p.id";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$products = getAllProducts($conn);





?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 ">


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
                            <a href="admin-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Profile</a>
                            <a href="admin-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Profile</a>
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
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Admin Panel</h1>
     










    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-4">Add New Product</h2>
        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="name" class="block">Name:</label>
                <input type="text" id="name" name="name" required class="w-full p-2 border rounded">
            </div>
            <div>
                <label for="description" class="block">Description:</label>
                <textarea id="description" name="description" required class="w-full p-2 border rounded"></textarea>
            </div>
            <div>
                <label for="price" class="block">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required class="w-full p-2 border rounded">
            </div>
            <div>
                <label for="rating" class="block">Rating:</label>
                <input type="number" id="rating" name="rating" step="0.1" min="0" max="5" required
                    class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="edit_product_page" class="block text-gray-700 text-sm font-bold mb-2">Page:</label>
                <select id="edit_product_page" name="page"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="men">Men</option>
                    <option value="women">Women</option>
                    <option value="featured">Featured</option>
                    <option value="kids">Kids</option>
                </select>
            </div>
            <div>
                <div>
                    <label for="product_images" class="block">Product Images:</label>
                    <input type="file" id="product_images" name="product_images[]" accept=".jpg,.jpeg,.png" multiple
                        class="w-full p-2 border rounded">
                </div>
            </div>
            <button type="submit" name="add_product" class="bg-blue-500 text-white px-4 py-2 rounded">Add
                Product</button>
        </form>
        <h2 class="text-2xl font-bold mt-8 mb-4">Existing Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($products as $product): ?>
                <div class="border p-4 rounded">
                    <h3 class="font-bold"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Price: $<?php echo number_format($product['price'], 2); ?></p>
                    <p>Rating: <?php echo $product['rating']; ?>/5</p>
                    <p>Page: <?php echo htmlspecialchars($product['page']); ?></p>
                    <div class="mt-4">
                        <button onclick="openEditProductModal(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</button>
                        <form method="POST" action="" class="inline">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="delete_product"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"
                                onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Add this to handle product deletion -->
        <?php
        if (isset($_POST['delete_product'])) {
            $product_id = $_POST['product_id'];
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                echo "Product deleted successfully!";
            } else {
                echo "Error deleting product.";
            }
        }
        ?>

        <!-- Add this modal for editing products -->
        <div id="editProductModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <!-- Modal content -->
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form id="editProductForm" method="POST" action="">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Product</h3>
                            <div class="mt-2">
                                <input type="hidden" id="edit_product_id" name="product_id">
                                <div class="mb-4">
                                    <label for="edit_product_name"
                                        class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                                    <input type="text" id="edit_product_name" name="name"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_product_description"
                                        class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                                    <textarea id="edit_product_description" name="description"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="edit_product_price"
                                        class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
                                    <input type="number" id="edit_product_price" name="price" step="0.01"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_product_rating"
                                        class="block text-gray-700 text-sm font-bold mb-2">Rating:</label>
                                    <input type="number" id="edit_product_rating" name="rating" step="0.1" min="0"
                                        max="5"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_product_page"
                                        class="block text-gray-700 text-sm font-bold mb-2">Page:</label>
                                    <input type="text" id="edit_product_page" name="page"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" name="edit_product"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Save
                                Changes</button>
                            <button type="button" onclick="closeEditProductModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
       
        <script>
            document.getElementById('user-menu-button').addEventListener('click', function() {
        var menu = document.getElementById('user-menu');
        menu.classList.toggle('hidden');
    });
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        document.getElementById('current_image').src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Add this to your openEditSlideModal function
            document.getElementById('current_image').src = '../uploads/carousel/' + slide.image_file;

            // Add this event listener
            document.getElementById('edit_image_file').addEventListener('change', function () {
                previewImage(this);
            });
            function openEditSlideModal(slide) {
                // Populate the modal fields with the slide data
                document.getElementById('edit_slide_id').value = slide.id;
                document.getElementById('edit_title').value = slide.title;
                document.getElementById('edit_description').value = slide.description;
                document.getElementById('edit_button_text').value = slide.button_text;
                document.getElementById('edit_button_link').value = slide.button_link;
                document.getElementById('edit_slide_order').value = slide.slide_order;

                // Display the current image
                document.getElementById('current_image').src = '../uploads/carousel/' + slide.image_file;
                document.getElementById('current_image').style.display = 'block';

                // Clear the file input
                document.getElementById('edit_image_file').value = '';

                // Show the modal
                document.getElementById('editSlideModal').classList.remove('hidden');
            }

            function closeEditSlideModal() {
                // Hide the modal
                document.getElementById('editSlideModal').classList.add('hidden');
            }

            function openEditModal(user) {
                // Populate the modal fields with the user data
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_address').value = user.address;
                document.getElementById('edit_age').value = user.age;
                document.getElementById('edit_gender').value = user.gender;
                document.getElementById('edit_payment_info').value = user.payment_info;

                // Show the modal
                document.getElementById('editModal').classList.remove('hidden');
            }

            function closeEditModal() {
                // Hide the modal
                document.getElementById('editModal').classList.add('hidden');
            }

            function openEditProductModal(product) {
                // Populate the modal fields with the product data
                document.getElementById('edit_product_id').value = product.id;
                document.getElementById('edit_product_name').value = product.name;
                document.getElementById('edit_product_description').value = product.description;
                document.getElementById('edit_product_price').value = product.price;
                document.getElementById('edit_product_rating').value = product.rating;
                document.getElementById('edit_product_page').value = product.page;

                // Show the modal
                document.getElementById('editProductModal').classList.remove('hidden');
            }

            function closeEditProductModal() {
                // Hide the modal
                document.getElementById('editProductModal').classList.add('hidden');
            }



        </script>

        <?php
        // Handle product edit
        if (isset($_POST['edit_product'])) {
            $product_id = $_POST['product_id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $rating = $_POST['rating'];
            $page = $_POST['page'];

            $sql = "UPDATE products SET name = ?, description = ?, price = ?, rating = ?, page = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssddsi", $name, $description, $price, $rating, $page, $product_id);

            if ($stmt->execute()) {
                echo "Product updated successfully!";
            } else {
                echo "Error updating product.";
            }
        }
        ?>





</body>

</html>