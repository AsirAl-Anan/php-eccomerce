<?php
session_start();
require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}


// Function to get all carousel slides
function getAllCarouselSlides($conn)
{
    $sql = "SELECT * FROM carousel_slides ORDER BY slide_order";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$carousel_slides = getAllCarouselSlides($conn);

// Handle adding new slide
if (isset($_POST['add_slide'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $slide_order = $_POST['slide_order'];

    $image_file = uploadImage($_FILES['image_file']);
    if ($image_file === false) {
        echo "Error uploading image.";
    } else {
        $sql = "INSERT INTO carousel_slides (image_file, title, description, button_text, button_link, slide_order) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $image_file, $title, $description, $button_text, $button_link, $slide_order);
        if ($stmt->execute()) {
            echo "Slide added successfully!";
        } else {
            echo "Error adding slide.";
        }
    }
}
// Handle updating slide
if (isset($_POST['update_slide'])) {
    $id = $_POST['slide_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $slide_order = $_POST['slide_order'];

    if ($_FILES['image_file']['size'] > 0) {
        $image_file = uploadImage($_FILES['image_file']);
        if ($image_file === false) {
            echo "Error uploading image.";
            exit;
        }
        $sql = "UPDATE carousel_slides SET image_file = ?, title = ?, description = ?, button_text = ?, button_link = ?, slide_order = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssii", $image_file, $title, $description, $button_text, $button_link, $slide_order, $id);
    } else {
        $sql = "UPDATE carousel_slides SET title = ?, description = ?, button_text = ?, button_link = ?, slide_order = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $title, $description, $button_text, $button_link, $slide_order, $id);
    }

    if ($stmt->execute()) {
        echo "<script>
        alert("A")
        </script>";
    } else {
        echo "Error updating slide.";
    }
}
function uploadImage($file)
{
    $target_dir = "../uploads/carousel/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_file_name = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_file_name;

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }

    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }

    // Allow certain file formats
    if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_file_name;
    } else {
        return false;
    }
}
// Handle deleting slide
if (isset($_POST['delete_slide'])) {
    $id = $_POST['slide_id'];
    $sql = "DELETE FROM carousel_slides WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$carousel_slides = getAllCarouselSlides($conn);



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
}

// Fetch all orders
$stmt = $conn->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium bg-gray-900">Dashboard</a>
                            <a href="users_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Users</a>
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Products</a>
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Orders</a>
                            <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Carousel</a>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <button class="p-1 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                            <span class="sr-only">View notifications</span>
                            <i class="fas fa-bell"></i>
                        </button>
                        <div class="ml-3 relative">
                            <div>
                                <button class="max-w-xs bg-gray-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Admin Panel</h1>
      









  
        <h2 class="text-2xl font-bold mt-8 mb-4">Manage Carousel Slides</h2>

        <!-- Add new slide form -->
        <form method="POST" action="" enctype="multipart/form-data" class="mb-8">
            <h3 class="text-xl font-bold mb-2">Add New Slide</h3>
            <div class="grid grid-cols-2 gap-4">
                <input type="file" name="image_file" accept="image/*" required class="p-2 border rounded">
                <input type="text" name="title" placeholder="Title" required class="p-2 border rounded">
                <textarea name="description" placeholder="Description" class="p-2 border rounded"></textarea>
                <input type="text" name="button_text" placeholder="Button Text" class="p-2 border rounded">
                <input type="text" name="button_link" placeholder="Button Link" class="p-2 border rounded">
                <input type="number" name="slide_order" placeholder="Slide Order" required class="p-2 border rounded">
            </div>
            <button type="submit" name="add_slide" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Add
                Slide</button>
        </form>

        <!-- Existing slides -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($carousel_slides as $slide): ?>
                <div class="border p-4 rounded">
                    <!-- <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" alt="Slide image" class="w-full h-40 object-cover mb-2"> -->
                    <h3 class="font-bold"><?php echo htmlspecialchars($slide['title']); ?></h3>
                    <p><?php echo htmlspecialchars($slide['description']); ?></p>
                    <p>Button: <?php echo htmlspecialchars($slide['button_text']); ?></p>
                    <p>Order: <?php echo $slide['slide_order']; ?></p>
                    <div class="mt-4">
                        <button onclick="openEditSlideModal(<?php echo htmlspecialchars(json_encode($slide)); ?>)"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</button>
                        <form method="POST" action="" class="inline">
                            <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                            <button type="submit" name="delete_slide"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"
                                onclick="return confirm('Are you sure you want to delete this slide?')">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Edit slide modal (similar to your product edit modal) -->
        <!-- Edit Carousel Slide Modal -->
        <div id="editSlideModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form id="editSlideForm" method="POST" action="" enctype="multipart/form-data">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Carousel Slide
                            </h3>
                            <div class="mt-2">
                                <input type="hidden" id="edit_slide_id" name="slide_id">
                                <div class="mb-4">
                                    <label for="edit_image_file"
                                        class="block text-gray-700 text-sm font-bold mb-2">Image File:</label>
                                    <input type="file" id="edit_image_file" name="image_file" accept="image/*"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <img id="current_image" src="" alt="Current Image" class="mt-2 max-w-full h-auto"
                                        style="display: none;">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_title"
                                        class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                                    <input type="text" id="edit_title" name="title"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_description"
                                        class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                                    <textarea id="edit_description" name="description"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="edit_button_text"
                                        class="block text-gray-700 text-sm font-bold mb-2">Button Text:</label>
                                    <input type="text" id="edit_button_text" name="button_text"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_button_link"
                                        class="block text-gray-700 text-sm font-bold mb-2">Button Link:</label>
                                    <input type="text" id="edit_button_link" name="button_link"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div class="mb-4">
                                    <label for="edit_slide_order"
                                        class="block text-gray-700 text-sm font-bold mb-2">Slide Order:</label>
                                    <input type="number" id="edit_slide_order" name="slide_order"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" name="update_slide"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save Changes
                            </button>
                            <button type="button" onclick="closeEditSlideModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
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

        

            
       

        </script>

     





</body>

</html>