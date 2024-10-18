<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_name = $_POST['section_name'];
    $section_content = $_POST['section_content'];
    $image_path = '';

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/group-members/';
        $filename = basename($_FILES['image']['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert or update the content
    $sql = "INSERT INTO about_page_content (section_name, section_content, image_path)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
            section_content = VALUES(section_content),
            image_path = VALUES(image_path)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $section_name, $section_content, $image_path);
    $stmt->execute();

    header('Location: edit_about_page.php');
    exit;
}

// Fetch the existing content
$sql = "SELECT * FROM about_page_content";
$result = $conn->query($sql);
$aboutContent = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Page - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>
<body class="bg-white text-black">
    <div class="container mx-auto py-12">
        <h1 class="text-4xl font-bold mb-8">Edit About Page</h1>

        <form action="edit_about_page.php" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="section_name" class="block text-lg font-semibold">Section Name</label>
                <input type="text" name="section_name" id="section_name" class="w-full p-2 border border-gray-300 rounded" required>
            </div>

            <div class="mb-4">
                <label for="section_content" class="block text-lg font-semibold">Section Content</label>
                <textarea name="section_content" id="section_content" rows="6" class="w-full p-2 border border-gray-300 rounded" required></textarea>
            </div>

            <div class="mb-4">
                <label for="image" class="block text-lg font-semibold">Image (optional)</label>
                <input type="file" name="image" id="image" class="w-full p-2 border border-gray-300 rounded">
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
        </form>

        <h2 class="text-2xl font-bold mt-8">Existing Sections</h2>
        <?php foreach ($aboutContent as $section): ?>
            <div class="mb-4">
                <h3 class="text-xl font-semibold"><?= htmlspecialchars($section['section_name']) ?></h3>
                <p><?= nl2br(htmlspecialchars($section['section_content'])) ?></p>
                <?php if (!empty($section['image_path'])): ?>
                    <img src="<?= htmlspecialchars($section['image_path']) ?>" alt="<?= htmlspecialchars($section['section_name']) ?>" class="w-full h-auto mb-2">
                <?php endif; ?>
                <a href="delete_section.php?id=<?= $section['id'] ?>" class="text-red-500">Delete</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
