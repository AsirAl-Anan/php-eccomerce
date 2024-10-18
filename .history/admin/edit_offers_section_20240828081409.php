<?php
require_once '../config/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new slide
                $stmt = $pdo->prepare("INSERT INTO offer_slides (title, description, button_text, button_link, image_file, offer_end_time, slide_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['title'], $_POST['description'], $_POST['button_text'], $_POST['button_link'], $_FILES['image']['name'], $_POST['offer_end_time'], $_POST['slide_order']]);
                move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/offer_slides/" . $_FILES['image']['name']);
                break;
            case 'edit':
                // Edit existing slide
                $stmt = $pdo->prepare("UPDATE offer_slides SET title = ?, description = ?, button_text = ?, button_link = ?, offer_end_time = ?, slide_order = ? WHERE id = ?");
                $stmt->execute([$_POST['title'], $_POST['description'], $_POST['button_text'], $_POST['button_link'], $_POST['offer_end_time'], $_POST['slide_order'], $_POST['id']]);
                if ($_FILES['image']['name']) {
                    $stmt = $pdo->prepare("UPDATE offer_slides SET image_file = ? WHERE id = ?");
                    $stmt->execute([$_FILES['image']['name'], $_POST['id']]);
                    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/offer_slides/" . $_FILES['image']['name']);
                }
                break;
            case 'delete':
                // Delete slide
                $stmt = $pdo->prepare("DELETE FROM offer_slides WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                break;
        }
    }
}

// Fetch all offer slides
$stmt = $pdo->query("SELECT * FROM offer_slides ORDER BY slide_order");
$offer_slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Offer Slides</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-6">Edit Offer Slides</h1>

    <!-- Add new slide form -->
    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow mb-8">
        <h2 class="text-xl font-bold mb-4">Add New Slide</h2>
        <input type="hidden" name="action" value="add">
        <div class="grid grid-cols-2 gap-4">
            <input type="text" name="title" placeholder="Title" required class="border p-2 rounded">
            <input type="text" name="description" placeholder="Description" required class="border p-2 rounded">
            <input type="text" name="button_text" placeholder="Button Text" required class="border p-2 rounded">
            <input type="text" name="button_link" placeholder="Button Link" required class="border p-2 rounded">
            <input type="file" name="image" required class="border p-2 rounded">
            <input type="datetime-local" name="offer_end_time" required class="border p-2 rounded">
            <input type="number" name="slide_order" placeholder="Slide Order" required class="border p-2 rounded">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4">Add Slide</button>
    </form>

    <!-- List of existing slides -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Existing Slides</h2>
        <?php foreach ($offer_slides as $slide): ?>
            <form action="" method="POST" enctype="multipart/form-data" class="border-b pb-4 mb-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $slide['id']; ?>">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($slide['title']); ?>" required class="border p-2 rounded">
                    <input type="text" name="description" value="<?php echo htmlspecialchars($slide['description']); ?>" required class="border p-2 rounded">
                    <input type="text" name="button_text" value="<?php echo htmlspecialchars($slide['button_text']); ?>" required class="border p-2 rounded">
                    <input type="text" name="button_link" value="<?php echo htmlspecialchars($slide['button_link']); ?>" required class="border p-2 rounded">
                    <input type="file" name="image" class="border p-2 rounded">
                    <input type="datetime-local" name="offer_end_time" value="<?php echo date('Y-m-d\TH:i', strtotime($slide['offer_end_time'])); ?>" required class="border p-2 rounded">
                    <input type="number" name="slide_order" value="<?php echo $slide['slide_order']; ?>" required class="border p-2 rounded">
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
                    <button type="submit" name="action" value="delete" class="bg-red-500 text-white px-4 py-2 rounded ml-2">Delete</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</body>
</html>