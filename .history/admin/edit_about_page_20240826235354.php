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
// $sql = "SELECT * FROM about_page_content";
// $result = $conn->query($sql);
// $aboutContent = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Page - Admin</title>
    <!-- <script src="https://cdn.tailwindcss.com"></script>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="css/index.css"> -->
</head>
<body class="bg-white text-black">
   <!-- Admin Panel Form -->
<form method="POST" enctype="multipart/form-data">
    <h2>Update About Page</h2>
    
    <!-- Company Overview -->
    <label>Company Overview</label>
    <textarea name="company_overview"><?php echo getAboutContent('Company Overview'); ?></textarea>
    
    <!-- Story Behind the Brand -->
    <label>Story Behind the Brand</label>
    <textarea name="story_behind_brand"><?php echo getAboutContent('Story Behind the Brand'); ?></textarea>
    
    <!-- Customer Commitment -->
    <label>Customer Commitment</label>
    <textarea name="customer_commitment"><?php echo getAboutContent('Customer Commitment'); ?></textarea>
    
    <!-- Contact Information -->
    <label>Contact Information</label>
    <textarea name="contact_information"><?php echo getAboutContent('Contact Information'); ?></textarea>
    
    <button type="submit" name="update_about">Update About Page</button>
    
    <h2>Add Team Member</h2>
    <input type="text" name="name" placeholder="Name" required />
    <input type="text" name="position" placeholder="Position" required />
    <input type="file" name="image" required />
    <button type="submit" name="add_team_member">Add Team Member</button>
    
    <h2>Add FAQ</h2>
    <textarea name="question" placeholder="Question" required></textarea>
    <textarea name="answer" placeholder="Answer" required></textarea>
    <button type="submit" name="add_faq">Add FAQ</button>
</form>
</body>
</html>
