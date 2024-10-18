<?php
require_once '../config/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new slide
                $stmt = $conn->prepare("INSERT INTO offer_slides (title, description, button_text, button_link, image_file, offer_end_time, slide_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi", $_POST['title'], $_POST['description'], $_POST['button_text'], $_POST['button_link'], $_FILES['image']['name'], $_POST['offer_end_time'], $_POST['slide_order']);
                $stmt->execute();
                move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/offer_slides/" . $_FILES['image']['name']);
                break;
            case 'edit':
                // Edit existing slide
                $stmt = $conn->prepare("UPDATE offer_slides SET title = ?, description = ?, button_text = ?, button_link = ?, offer_end_time = ?, slide_order = ? WHERE id = ?");
                $stmt->bind_param("sssssii", $_POST['title'], $_POST['description'], $_POST['button_text'], $_POST['button_link'], $_POST['offer_end_time'], $_POST['slide_order'], $_POST['id']);
                $stmt->execute();
                if ($_FILES['image']['name']) {
                    $stmt = $conn->prepare("UPDATE offer_slides SET image_file = ? WHERE id = ?");
                    $stmt->bind_param("si", $_FILES['image']['name'], $_POST['id']);
                    $stmt->execute();
                    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/offer_slides/" . $_FILES['image']['name']);
                }
                break;
            case 'delete':
                // Delete slide
                $stmt = $conn->prepare("DELETE FROM offer_slides WHERE id = ?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
                break;
        }
    }
}

// Fetch all offer slides
$result = $conn->query("SELECT * FROM offer_slides ORDER BY slide_order");
$offer_slides = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Offer Slides</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-6">Edit Offer Slides</h1>

    <!-- Add new slide form -->
    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-bold mb-4">Add New Slide</h2>
        <input type="hidden" name="action" value="add">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          
           
          
           
            <input type="file" name="image" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="datetime-local" name="offer_end_time"  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" name="slide_order" placeholder="Slide Order" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">Add Slide</button>
    </form>

    <!-- List of existing slides -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Existing Slides</h2>
        <?php foreach ($offer_slides as $slide): ?>
            <form action="" method="POST" enctype="multipart/form-data" class="border-b border-gray-200 pb-4 mb-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($slide['title']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="description" value="<?php echo htmlspecialchars($slide['description']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="button_text" value="<?php echo htmlspecialchars($slide['button_text']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="button_link" value="<?php echo htmlspecialchars($slide['button_link']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="file" name="image" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="datetime-local" name="offer_end_time" value="<?php echo date('Y-m-d\TH:i', strtotime($slide['offer_end_time'])); ?>"  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="number" name="slide_order" value="<?php echo $slide['slide_order']; ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mt-4 flex space-x-2">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">Update</button>
                    <button type="submit" name="action" value="delete" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">Delete</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</body>
</html>