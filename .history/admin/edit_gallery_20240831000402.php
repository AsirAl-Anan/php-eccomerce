<?php
session_start();
// // Ensure the user is logged in as admin
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: admin_login.php");
//     exit();
// }

require_once('../config/config.php');

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to handle file upload
function handle_file_upload($file) {
    $target_dir = "../uploads/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }

    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }

    // Allow certain file formats
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif" ) {
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return false;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $title = sanitize_input($_POST['title']);
        $subtitle = sanitize_input($_POST['subtitle']);
        $link_url = sanitize_input($_POST['link_url']);
        $display_order = intval($_POST['display_order']);

        $image_path = handle_file_upload($_FILES["image"]);
        if ($image_path) {
            $sql = "INSERT INTO featured_sections (title, subtitle, image_url, link_url, display_order) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $subtitle, $image_path, $link_url, $display_order);
            $stmt->execute();
        } else {
            $error = "Failed to upload image.";
        }
    } elseif (isset($_POST['edit'])) {
        $id = intval($_POST['id']);
        $title = sanitize_input($_POST['title']);
        $subtitle = sanitize_input($_POST['subtitle']);
        $link_url = sanitize_input($_POST['link_url']);
        $display_order = intval($_POST['display_order']);

        if ($_FILES['image']['size'] > 0) {
            $image_path = handle_file_upload($_FILES["image"]);
            if (!$image_path) {
                $error = "Failed to upload new image.";
            }
        } else {
            $image_path = $_POST['current_image'];
        }

        $sql = "UPDATE featured_sections SET title=?, subtitle=?, image_url=?, link_url=?, display_order=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $title, $subtitle, $image_path, $link_url, $display_order, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);

        // Fetch the image path before deleting the record
        $sql = "SELECT image_url FROM featured_sections WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $image_to_delete = $row['image_url'];

        $sql = "DELETE FROM featured_sections WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // Delete the image file
            if (file_exists($image_to_delete)) {
                unlink($image_to_delete);
            }
        }
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
        img { max-width: 100px; max-height: 100px; }
    </style>
</head>
<body>
    <h1>Edit Featured Sections</h1>

    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>

    <h2>Add New Section</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" required placeholder="Title"><br>
        <input type="text" name="subtitle" required placeholder="Subtitle"><br>
        <input type="file" name="image" required accept="image/*"><br>
        <input type="text" name="link_url" required placeholder="Link URL"><br>
        <input type="number" name="display_order" required placeholder="Display Order"><br>
        <input type="submit" name="add" value="Add Section">
    </form>

    <h2>Existing Sections</h2>
    <table>
        <tr>
            <th>Title</th>
            <th>Subtitle</th>
            <th>Image</th>
            <th>Link URL</th>
            <th>Display Order</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="current_image" value="<?php echo $row['image_url']; ?>">
                <td><input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>"></td>
                <td><input type="text" name="subtitle" value="<?php echo htmlspecialchars($row['subtitle']); ?>"></td>
                <td>
                    <img src="<?php echo $row['image_url']; ?>" alt="Section Image"><br>
                    <input type="file" name="image" accept="image/*">
                </td>
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