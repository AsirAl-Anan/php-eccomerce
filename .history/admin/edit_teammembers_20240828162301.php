<?php
session_start();
require_once '../config/config.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Get team member ID from URL
if (!isset($_GET['id'])) {
    header("Location: manage_teammembers.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch team member details
$team_query = $conn->prepare("SELECT * FROM team_members WHERE id = ?");
$team_query->bind_param("i", $id);
$team_query->execute();
$team_result = $team_query->get_result();
$team_member = $team_result->fetch_assoc();

// Handle form submission for editing team member
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST['name']);
    $position = sanitize_input($_POST['position']);

    // Update member details in the database
    $stmt = $conn->prepare("UPDATE team_members SET name = ?, position = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $position, $id);
    $stmt->execute();
    $stmt->close();

    // Handle image upload
    if ($_FILES['image']['name']) {
        $target_dir = "../uploads/group-members/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_name = basename($_FILES["image"]["name"]);
                $stmt = $conn->prepare("UPDATE team_members SET image = ? WHERE id = ?");
                $stmt->bind_param("si", $image_name, $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: manage_teammembers.php");
    exit;
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Team Member</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<nav class="bg-gray-800 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Add your nav items here -->
        </div>
    </div>
</nav>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Edit Team Member</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($team_member['name']); ?>" class="w-full p-2 border rounded" required>
        </div>

        <div class="mb-4">
            <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
            <input type="text" name="position" id="position" value="<?php echo htmlspecialchars($team_member['position']); ?>" class="w-full p-2 border rounded" required>
        </div>

        <div class="mb-4">
            <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
            <input type="file" name="image" id="image" accept="image/*">
            <p class="text-sm text-gray-500">Current image: <img src="../uploads/group-members/<?php echo htmlspecialchars($team_member['image']); ?>" alt="<?php echo htmlspecialchars($team_member['name']); ?>" class="h-12 w-12 object-cover rounded-full mt-2"></p>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Team Member</button>
    </form>

    <div class="mt-8">
        <a href="manage_teammembers.php" class="text-blue-500 hover:underline">Back to Manage Team Members</a>
    </div>
</div>
</body>
</html>
