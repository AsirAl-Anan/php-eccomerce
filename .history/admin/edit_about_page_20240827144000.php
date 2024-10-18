<?php
session_start();
require_once '../config/config.php';
require_once 'fetch_admin_data.php';
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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_about'])) {
        $section = sanitize_input($_POST['section']);
        $content = sanitize_input($_POST['content']);
        
        $stmt = $conn->prepare("UPDATE about_content SET content = ? WHERE section = ?");
        $stmt->bind_param("ss", $content, $section);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['add_faq'])) {
        $question = sanitize_input($_POST['question']);
        $answer = sanitize_input($_POST['answer']);
        
        $stmt = $conn->prepare("INSERT INTO faq_content (question, answer) VALUES (?, ?)");
        $stmt->bind_param("ss", $question, $answer);
        $stmt->execute();
        $stmt->close();
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
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            
            // Check if image file is an actual image or fake image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check !== false) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_name = basename($_FILES["image"]["name"]);
                    $stmt = $conn->prepare("UPDATE team_members SET image = ? WHERE id = ?");
                    $stmt->bind_param("si", $image_name, $id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }
}

// Fetch About content
$about_query = "SELECT * FROM about_content";
$about_result = $conn->query($about_query);

// Fetch FAQ content
$faq_query = "SELECT * FROM faq_content";
$faq_result = $conn->query($faq_query);

// Fetch team members
$team_query = "SELECT * FROM team_members";
$team_result = $conn->query($team_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
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
                            <a href="manage_product.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Products</a>
                            <a href="order_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Orders</a>
                            <a href="edit_carousel.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Carousel</a>
                            <a href="edit_about_page.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Edit About page</a>
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
        <h1 class="text-3xl font-bold mb-8">Edit About Page</h1>

        <!-- Edit About Sections -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Edit About Sections</h2>
            <?php while ($row = $about_result->fetch_assoc()): ?>
                <form action="" method="POST" class="mb-4">
                    <input type="hidden" name="section" value="<?php echo $row['section']; ?>">
                    <label class="block mb-2 font-semibold"><?php echo $row['section']; ?></label>
                    <textarea name="content" rows="4" class="w-full p-2 border rounded"><?php echo $row['content']; ?></textarea>
                    <button type="submit" name="update_about" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Update</button>
                </form>
            <?php endwhile; ?>
        </section>

        <!-- Edit FAQ Section -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Edit FAQ</h2>
            <?php while ($faq = $faq_result->fetch_assoc()): ?>
                <form action="" method="POST" class="mb-4">
                    <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                    <input type="text" name="question" value="<?php echo $faq['question']; ?>" class="w-full p-2 border rounded mb-2">
                    <textarea name="answer" rows="3" class="w-full p-2 border rounded"><?php echo $faq['answer']; ?></textarea>
                    <button type="submit" name="update_faq" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Update</button>
                </form>
            <?php endwhile; ?>
            
            <form action="" method="POST" class="mt-4">
                <h3 class="font-semibold mb-2">Add New FAQ</h3>
                <input type="text" name="question" placeholder="Question" class="w-full p-2 border rounded mb-2" required>
                <textarea name="answer" rows="3" placeholder="Answer" class="w-full p-2 border rounded" required></textarea>
                <button type="submit" name="add_faq" class="mt-2 bg-green-500 text-white px-4 py-2 rounded">Add FAQ</button>
            </form>
        </section>

        <!-- Edit Team Members -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Edit Team Members</h2>
            <?php while ($member = $team_result->fetch_assoc()): ?>
                <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                    <input type="text" name="name" value="<?php echo $member['name']; ?>" class="w-full p-2 border rounded mb-2" required>
                    <input type="text" name="position" value="<?php echo $member['position']; ?>" class="w-full p-2 border rounded mb-2" required>
                    <input type="file" name="image" accept="image/*" class="mb-2">
                    <button type="submit" name="update_team_member" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Update</button>
                </form>
            <?php endwhile; ?>
            
            <form action="" method="POST" enctype="multipart/form-data" class="mt-4">
                <h3 class="font-semibold mb-2">Add New Team Member</h3>
                <input type="text" name="name" placeholder="Name" class="w-full p-2 border rounded mb-2" required>
                <input type="text" name="position" placeholder="Position" class="w-full p-2 border rounded mb-2" required>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit" name="add_team_member" class="mt-2 bg-green-500 text-white px-4 py-2 rounded">Add Team Member</button>
            </form>
        </section>
    </div>
</body>
</html>