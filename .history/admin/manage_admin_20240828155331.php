<?php
require_once '../config/config.js';

// Function to get all admin users
function getAdminUsers($conn) {
    $sql = "SELECT * FROM admin_users";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to delete an admin user
function deleteAdminUser($conn, $id) {
    $sql = "DELETE FROM admin_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    deleteAdminUser($conn, $_GET['id']);
}

// Get all admin users
$adminUsers = getAdminUsers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Users</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        img { max-width: 50px; max-height: 50px; }
    </style>
</head>
<body>
    <h1>Manage Admin Users</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Admin ID</th>
            <th>Profile Picture</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($adminUsers as $user): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['admin_id']); ?></td>
            <td>
                <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                <?php else: ?>
                    No Picture
                <?php endif; ?>
            </td>
            <td>
                <a href="edit_admin.php?id=<?php echo $user['id']; ?>">Edit</a>
                <a href="?action=delete&id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>