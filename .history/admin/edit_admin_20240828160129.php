<?php
require_once '../config/config.php';

// Function to get admin user by ID
function getAdminUser($conn, $id) {
    $sql = "SELECT * FROM admin_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to update admin user
function updateAdminUser($conn, $id, $username, $fullname, $email, $admin_id, $profile_picture) {
    $sql = "UPDATE admin_users SET username = ?, fullname = ?, email = ?, admin_id = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $username, $fullname, $email, $admin_id, $profile_picture, $id);
    return $stmt->execute();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $admin_id = $_POST['admin_id'];
    
    $profile_picture = $_POST['current_profile_picture'];
    
    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . "." . $filetype;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], "../uploads/".$new_filename)) {
                $profile_picture = $new_filename;
            }
        }
    }

    if (updateAdminUser($conn, $id, $username, $fullname, $email, $admin_id, $profile_picture)) {
        $message = "Admin user updated successfully.";
    } else {
        $message = "Error updating admin user.";
    }
}

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$admin_user = getAdminUser($conn, $id);

// if (!$admin_user) {
//     die("Admin user not found.");
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Admin User</h1>
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <input type="hidden" name="id" value="<?php echo $admin_user['id']; ?>">
            <input type="hidden" name="current_profile_picture" value="<?php echo $admin_user['profile_picture']; ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    Username
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" type="text" name="username" value="<?php echo htmlspecialchars($admin_user['username']); ?>" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fullname">
                    Full Name
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="fullname" type="text" name="fullname" value="<?php echo htmlspecialchars($admin_user['fullname']); ?>" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" type="email" name="email" value="<?php echo htmlspecialchars($admin_user['email']); ?>" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="admin_id">
                    Admin ID
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="admin_id" type="text" name="admin_id" value="<?php echo htmlspecialchars($admin_user['admin_id']); ?>" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="profile_picture">
                    Profile Picture
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="profile_picture" type="file" name="profile_picture">
                <?php if ($admin_user['profile_picture']): ?>
                    <p class="text-sm text-gray-500 mt-1">Current: <?php echo htmlspecialchars($admin_user['profile_picture']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Update Admin User
                </button>
                <a href="manage_admin.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Back to Admin List
                </a>
            </div>
        </form>
    </div>
</body>
</html>