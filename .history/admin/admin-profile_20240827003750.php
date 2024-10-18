<?php
require_once '../config/config.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = '';

// Fetch current admin data
$stmt = $conn->prepare("SELECT * FROM admin_users WHERE admin_id = ?");
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];

    // Update profile information
    $sql = "UPDATE admin_users SET fullname = ?, email = ?, username = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fullname, $email, $username, $admin_id);
    
    if ($stmt->execute()) {
        $message .= "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>Profile updated successfully.</div>";
    } else {
        $message .= "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>Error updating profile: " . $conn->error . "</div>";
    }
    $stmt->close();

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["profile_picture"]["name"];
        $filetype = $_FILES["profile_picture"]["type"];
        $filesize = $_FILES["profile_picture"]["size"];
    
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $message .= "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>Error: Please select a valid file format.</div>";
        }
    
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $message .= "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>Error: File size is larger than the allowed limit.</div>";
        }
    
        // Verify MYME type of the file
        if (in_array($filetype, $allowed)) {
            // Check whether file exists before uploading it
            $target_dir = "../uploads/";
            $target_file = $target_dir . "pfp-" . $admin_id . "." . $ext;
            
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $sql = "UPDATE admin_users SET profile_picture = ? WHERE admin_id = ?";
                $stmt = $conn->prepare($sql);
                $profile_picture = "pfp-" . $admin_id . "." . $ext;
                $stmt->bind_param("ss", $profile_picture, $admin_id);
                
                if ($stmt->execute()) {
                    $message .= "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>Profile picture updated successfully.</div>";
                } else {
                    $message .= "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>Error updating profile picture in database: " . $conn->error . "</div>";
                }
                $stmt->close();
            } else {
                $message .= "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>Error uploading file.</div>";
            }
        } else {
            $message .= "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>Error: There was a problem uploading your file. Please try again.</div>";
        }
    }

    // Refresh admin data after update
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE admin_id = ?");
    $stmt->bind_param("s", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_data = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Admin Profile</h2>
            
            <?php echo $message; ?>

            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
                <div class="mb-4 text-center">
                    <img src="<?php echo isset($admin_data['profile_picture']) ? '../uploads/' . $admin_data['profile_picture'] : '../uploads/default_avatar.jpg'; ?>" alt="Profile Picture" class="w-32 h-32 rounded-full mx-auto mb-4">
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="mb-4">
                    <label for="fullname" class="block text-gray-700 text-sm font-bold mb-2">Full Name:</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo $admin_data['fullname']; ?>" required 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $admin_data['email']; ?>" required 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo $admin_data['username']; ?>" required 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label for="admin_id" class="block text-gray-700 text-sm font-bold mb-2">Admin ID:</label>
                    <input type="text" id="admin_id" value="<?php echo $admin_data['admin_id']; ?>" readonly 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100">
                </div>

                <div class="flex items-center justify-center">
                    <input type="submit" value="Update Profile" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline cursor-pointer">
                </div>
            </form>
        </div>
    </div>
</body>
</html>