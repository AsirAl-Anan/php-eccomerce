<?php
session_start();
require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

// Function to get all users
function getAllUsers($conn)
{
    $sql = "SELECT id, username, email, address, age, gender, payment_info, created_at FROM users";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $conn->query("SET foreign_key_checks = 0");

    try {
        $sql = "DELETE FROM orders WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        echo "User and related data deleted successfully";
    } catch (Exception $e) {
        echo "Error deleting user: " . $e->getMessage();
    } finally {
        $conn->query("SET foreign_key_checks = 1");
    }
}
// Handle user edit
if (isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $payment_info = $_POST['payment_info'];

    $sql = "UPDATE users SET username = ?, email = ?, address = ?, age = ?, gender = ?, payment_info = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssissi", $username, $email, $address, $age, $gender, $payment_info, $user_id);

    if ($stmt->execute()) {
        echo "User updated successfully!";
    } else {
        echo "Error updating user: " . $conn->error;
    }
}
$users = getAllUsers($conn);


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
                        <img class="h-8 w-8" src="https://tailwindui.com/img/logos/workflow-mark-indigo-500.svg"
                            alt="Workflow">
                    </a>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium bg-gray-900">Dashboard</a>
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Users</a>
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Products</a>
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Orders</a>
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Carousel</a>
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
                                <button
                                    class="max-w-xs bg-gray-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="h-8 w-8 rounded-full"
                                        src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                        alt="">
                                </button>
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
        <h1 class="text-3xl font-bold mb-6">Manage User</h1>
        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow-md rounded mb-4">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Username</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-left">Address</th>
                        <th class="py-3 px-6 text-center">Age</th>
                        <th class="py-3 px-6 text-center">Gender</th>
                        <th class="py-3 px-6 text-left">Payment Info</th>

                        <th class="py-3 px-6 text-center">Created At</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <?php foreach ($users as $user): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo $user['id']; ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['address']); ?></td>
                            <td class="py-3 px-6 text-center"><?php echo $user['age']; ?></td>
                            <td class="py-3 px-6 text-center"><?php echo htmlspecialchars($user['gender']); ?></td>
                            <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($user['payment_info']); ?></td>

                            <td class="py-3 px-6 text-center"><?php echo $user['created_at']; ?></td>
                            <td class="py-3 px-6 text-center">
                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</button>
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"
                                        onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>










 
        <script>
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