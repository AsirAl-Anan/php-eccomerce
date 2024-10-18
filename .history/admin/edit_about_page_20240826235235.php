<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Assume a connection to the database is already established
function getAboutContent($section_name) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT content FROM about_page WHERE section_name = ?");
    $stmt->execute([$section_name]);
    return $stmt->fetchColumn();
}
?>

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_about'])) {
        // Update each section
        $sections = ['company_overview', 'story_behind_brand', 'customer_commitment', 'contact_information'];
        
        foreach ($sections as $section) {
            $content = $_POST[$section];
            $stmt = $pdo->prepare("UPDATE about_page SET content = ? WHERE section_name = ?");
            $stmt->execute([$content, str_replace('_', ' ', $section)]);
        }
    }
    
    if (isset($_POST['add_team_member'])) {
        // Handle image upload and insert a new team member
        $name = $_POST['name'];
        $position = $_POST['position'];
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/group-members/" . $image);
        
        $stmt = $pdo->prepare("INSERT INTO team_members (name, position, image) VALUES (?, ?, ?)");
        $stmt->execute([$name, $position, $image]);
    }
    
    if (isset($_POST['add_faq'])) {
        // Insert a new FAQ
        $question = $_POST['question'];
        $answer = $_POST['answer'];
        
        $stmt = $pdo->prepare("INSERT INTO faq (question, answer) VALUES (?, ?)");
        $stmt->execute([$question, $answer]);
    }
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
