<?php
session_start();
// Ensure the user is logged in as admin
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: admin_login.php");
//     exit();
// }

require_once('../config/config.php');

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $title = sanitize_input($_POST['title']);
        $subtitle = sanitize_input($_POST['subtitle']);
        $image_url = sanitize_input($_POST['image_url']);
        $link_url = sanitize_input($_POST['link_url']);
        $display_order = intval($_POST['display_order']);

        $sql = "INSERT INTO featured_sections (title, subtitle, image_url, link_url, display_order) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $title, $subtitle, $image_url, $link_url, $display_order);
        $stmt->execute();
    } elseif (isset($_POST['edit'])) {
        $id = intval($_POST['id']);
        $title = sanitize_input($_POST['title']);
        $subtitle = sanitize_input($_POST['subtitle']);
        $image_url = sanitize_input($_POST['image_url']);
        $link_url = sanitize_input($_POST['link_url']);
        $display_order = intval($_POST['display_order']);

        $sql = "UPDATE featured_sections SET title=?, subtitle=?, image_url=?, link_url=?, display_order=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $title, $subtitle, $image_url, $link_url, $display_order, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);

        $sql = "DELETE FROM featured_sections WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Fetch all featured sections
$sql = "SELECT * FROM featured_sections ORDER BY display_order";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Featured Sections</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Edit Featured Sections</h1>

    <h2>Add New Section</h2>
    <form method="post">
        <input type="text" name="title" required placeholder="Title"><br>
        <input type="text" name="subtitle" required placeholder="Subtitle"><br>
        <input type="text" name="image_url" required placeholder="Image URL"><br>
        <input type="text" name="link_url" required placeholder="Link URL"><br>
        <input type="number" name="display_order" required placeholder="Display Order"><br>
        <input type="submit" name="add" value="Add Section">
    </form>

    <h2>Existing Sections</h2>
    <table>
        <tr>
            <th>Title</th>
            <th>Subtitle</th>
            <th>Image URL</th>
            <th>Link URL</th>
            <th>Display Order</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <td><input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>"></td>
                <td><input type="text" name="subtitle" value="<?php echo htmlspecialchars($row['subtitle']); ?>"></td>
                <td><input type="text" name="image_url" value="<?php echo htmlspecialchars($row['image_url']); ?>"></td>
                <td><input type="text" name="link_url" value="<?php echo htmlspecialchars($row['link_url']); ?>"></td>
                <td><input type="number" name="display_order" value="<?php echo $row['display_order']; ?>"></td>
                <td>
                    <input type="submit" name="edit" value="Update">
                    <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you want to delete this section?');">
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>