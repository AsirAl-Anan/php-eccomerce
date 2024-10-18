<?php
session_start();
require_once '../config/config.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle form submissions for adding, updating, and deleting team members
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_team_member'])) {
        $name = sanitize_input($_POST['name']);
        $position = sanitize_input($_POST['position']);

        // Handle image upload
        if ($_FILES['image']['name']) {
            $target_dir = "../uploads/group-members/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is an actual image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_name = basename($_FILES["image"]["name"]);
                    $stmt = $conn->prepare("INSERT INTO team_members (name, position, image) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $name, $position, $image_name);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    } elseif (isset($_POST['update_team_member'])) {
        $id = $_POST['member_id'];
        $name = sanitize_input($_POST['name']);
        $position = sanitize_input($_POST['position']);

        $stmt = $conn->prepare("UPDATE team_members SET name = ?, position = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $position, $id);
        $stmt->execute();
        $stmt->close();

        // Handle image upload
        if ($_FILES['image']['name']) {
            $target_dir = "../uploads/group-members/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is an actual image
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
    } elseif (isset($_POST['delete_team_member'])) {
        $id = $_POST['member_id'];
        $stmt = $conn->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all team members
$team_query = "SELECT * FROM team_members";
$team_result = $conn->query($team_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team Members</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
      

        <!-- Display Team Members -->
        <h1 class="text-3xl font-bold mb-8">Manage Team Members</h1>

<div class="overflow-x-auto">
    <table class="min-w-full bg-white border rounded-lg shadow-md">
        <thead>
            <tr class="bg-gray-800 text-white">
                <th class="py-2 px-4">ID</th>
                <th class="py-2 px-4">Image</th>
                <th class="py-2 px-4">Name</th>
                <th class="py-2 px-4">Position</th>
                <th class="py-2 px-4">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($member = $team_result->fetch_assoc()): ?>
                <tr class="border-b">
                    <td class="py-2 px-4 text-center"><?php echo $member['id']; ?></td>
                    <td class="py-2 px-4 text-center">
                        <img src="../uploads/group-members/<?php echo htmlspecialchars($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="h-12 w-12 object-cover rounded-full">
                    </td>
                    <td class="py-2 px-4 text-center"><?php echo htmlspecialchars($member['name']); ?></td>
                    <td class="py-2 px-4 text-center"><?php echo htmlspecialchars($member['position']); ?></td>
                    <td class="py-2 px-4 text-center">
                        <a href="edit_teammember.php?id=<?php echo $member['id']; ?>" class="text-blue-500 hover:underline">Edit</a> |
                        <a href="delete_teammember.php?id=<?php echo $member['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this member?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

        <!-- Add New Team Member -->
        <section class="mt-">
            <h2 class="text-2xl font-semibold mb-4">Add New Team Member</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Name" class="w-full p-2 border rounded mb-2" required>
                <input type="text" name="position" placeholder="Position" class="w-full p-2 border rounded mb-2" required>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit" name="add_team_member" class="mt-4 bg-green-500 text-white px-4 py-2 rounded">Add Team Member</button>
            </form>
        </section>
    </div>
</body>
</html>
