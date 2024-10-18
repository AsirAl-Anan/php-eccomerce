<?php
session_start();
include '../config/config.php';  // Update with the correct path to your database connection file

// Ensure the user is logged in and is an admin


// Handle file upload
function uploadImage($file) {
    $target_dir = "../uploads/offers/";
    $image_file = basename($file["name"]);
    $target_file = $target_dir . $image_file;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $image_file;
    }
    return null;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $button_text = mysqli_real_escape_string($conn, $_POST['button_text']);
    $button_link = mysqli_real_escape_string($connection, $_POST['button_link']);
    $start_date = mysqli_real_escape_string($connection, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($connection, $_POST['end_date']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    switch ($_POST['action']) {
        case 'add':
            $image_file = uploadImage($_FILES['image_file']);

            if ($image_file) {
                $query = "INSERT INTO offer_carousel (image_file, title, description, button_text, button_link, start_date, end_date, is_active) 
                          VALUES ('$image_file', '$title', '$description', '$button_text', '$button_link', '$start_date', '$end_date', $is_active)";
                mysqli_query($connection, $query);
            }
            break;

        case 'edit':
            $id = mysqli_real_escape_string($connection, $_POST['id']);
            $image_update = '';

            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
                $image_file = uploadImage($_FILES['image_file']);
                if ($image_file) {
                    $image_update = "image_file = '$image_file',";
                }
            }

            $query = "UPDATE offer_carousel SET 
                      $image_update
                      title = '$title', 
                      description = '$description', 
                      button_text = '$button_text', 
                      button_link = '$button_link', 
                      start_date = '$start_date', 
                      end_date = '$end_date', 
                      is_active = $is_active 
                      WHERE id = $id";
            mysqli_query($connection, $query);
            break;

        case 'delete':
            $id = mysqli_real_escape_string($connection, $_POST['id']);
            $query = "DELETE FROM offer_carousel WHERE id = $id";
            mysqli_query($connection, $query);
            break;
    }
}

// Fetch all slides
$query = "SELECT * FROM offer_carousel ORDER BY start_date DESC";
$result = mysqli_query($conn, $query);
$slides = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carousel Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Carousel Admin</h1>

        <!-- Add New Slide Form -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Add New Slide</h2>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div>
                    <label for="title" class="block mb-1">Title:</label>
                    <input type="text" id="title" name="title" required class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label for="description" class="block mb-1">Description:</label>
                    <textarea id="description" name="description" class="w-full px-3 py-2 border rounded"></textarea>
                </div>
                <div>
                    <label for="button_text" class="block mb-1">Button Text:</label>
                    <input type="text" id="button_text" name="button_text" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label for="button_link" class="block mb-1">Button Link:</label>
                    <input type="text" id="button_link" name="button_link" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label for="image_file" class="block mb-1">Image:</label>
                    <input type="file" id="image_file" name="image_file" required class="w-full px-3 py-2 border rounded">
                </div>
                <>
                    <label for="start_date" class="block mb-1">Start Date:</label>
                    <input type="datetime-local" id="start_date" name="start_date"  class="w-full px-3 py-2 border rounded">
</div>
                <div>
                    <label for="end_date" class="block mb-1">End Date:</label>
                    <input type="datetime-local" id="end_date" name="end_date"  class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked class="form-checkbox">
                        <span class="ml-2">Active</span>
                    </label>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Slide</button>
            </form>
        </div>

        <!-- Existing Slides -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Existing Slides</h2>
            <div class="space-y-6">
                <?php foreach ($slides as $slide): ?>
                    <div class="border p-4 rounded">
                        <h3 class="font-bold"><?php echo htmlspecialchars($slide['title']); ?></h3>
                        <p><?php echo htmlspecialchars($slide['description']); ?></p>
                        <p>Start: <?php echo $slide['start_date']; ?></p>
                        <p>End: <?php echo $slide['end_date']; ?></p>
                        <p>Active: <?php echo $slide['is_active'] ? 'Yes' : 'No'; ?></p>
                        <div class="mt-2">
                            <button onclick="editSlide(<?php echo $slide['id']; ?>)" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600" onclick="return confirm('Are you sure you want to delete this slide?')">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function editSlide(id) {
            // Fetch slide data and populate edit form
            // This would typically be done with AJAX, but for simplicity, we'll use a page reload
            window.location.href = `?edit=${id}`;
        }
    </script>
</body>
</html>
