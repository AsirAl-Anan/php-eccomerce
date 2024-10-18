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
        <h1 class="text-3xl font-bold mb-8">Manage Team Members</h1>

        <!-- Display Team Members -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Current Team Members</h2>
            <div class="grid grid-cols-3 gap-4">
                <?php while ($member = $team_result->fetch_assoc()): ?>
                    <div class="border p-4 rounded shadow">
                        <img src="../uploads/group-members/<?php echo htmlspecialchars($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-full h-40 object-cover mb-4 rounded">
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($member['name']); ?></h3>
                        <p class="text-gray-700"><?php echo htmlspecialchars($member['position']); ?></p>
                        <div class="mt-4 flex justify-between">
                            <form action="" method="POST">
                                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                <button type="submit" name="edit_team_member" class="bg-blue-500 text-white px-4 py-2 rounded">Edit</button>
                            </form>
                            <form action="" method="POST">
                                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                <button type="submit" name="delete_team_member" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Add New Team Member -->
        <section>
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
