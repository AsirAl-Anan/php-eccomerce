<?php
session_start();
require_once '../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$sql = "SELECT p.*, GROUP_CONCAT(pi.image_file) as images 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id 
        WHERE p.id = ?
        GROUP BY p.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: index.php');
    exit();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = "You must be logged in to comment.";
    } else {
        $comment = trim($_POST['comment']);
        if (!empty($comment)) {
            $image_file = null;
            if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                $filename = $_FILES['comment_image']['name'];
                $filetype = $_FILES['comment_image']['type'];
                $filesize = $_FILES['comment_image']['size'];

                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!array_key_exists($ext, $allowed)) {
                    $error = "Error: Please select a valid file format.";
                }

                // Verify file size - 5MB maximum
                $maxsize = 5 * 1024 * 1024;
                if ($filesize > $maxsize) {
                    $error = "Error: File size is larger than the allowed limit.";
                }

                // Verify MYME type of the file
                if (in_array($filetype, $allowed)) {
                    // Check whether file exists before uploading it
                    $image_file = uniqid() . "." . $ext;
                    move_uploaded_file($_FILES['comment_image']['tmp_name'], "../uploads/comments/" . $image_file);
                } else {
                    $error = "Error: There was a problem uploading your file. Please try again.";
                }
            }

            if (!isset($error)) {
                $sql = "INSERT INTO product_comments (product_id, user_id, comment, image_file) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiss", $product_id, $_SESSION['user_id'], $comment, $image_file);
                if ($stmt->execute()) {
                    $success = "Comment posted successfully.";
                } else {
                    $error = "Error posting comment.";
                }
            }
        } else {
            $error = "Comment cannot be empty.";
        }
    }
}

// Fetch comments
$sql = "SELECT pc.*, u.username 
        FROM product_comments pc 
        JOIN users u ON pc.user_id = u.id
        WHERE pc.product_id = ?
        ORDER BY pc.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <!-- Product details here (as in the previous example) -->

        <!-- Comments section -->
        <div class="mt-8">
            <h2 class="text-2xl font-bold mb-4">Comments</h2>
            
            <!-- Comment form -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
                    <textarea name="comment" rows="4" class="w-full p-2 border rounded mb-2" placeholder="Write your comment here..."></textarea>
                    <input type="file" name="comment_image" accept="image/*" class="mb-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Post Comment</button>
                </form>
            <?php else: ?>
                <p>Please <a href="../login.php" class="text-blue-500">log in</a> to leave a comment.</p>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <p class="text-red-500"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <p class="text-green-500"><?php echo $success; ?></p>
            <?php endif; ?>

            <!-- Display comments -->
            <?php foreach ($comments as $comment): ?>
                <div class="bg-gray-100 p-4 rounded mb-4">
                    <p class="font-bold"><?php echo htmlspecialchars($comment['username']); ?></p>
                    <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                    <?php if ($comment['image_file']): ?>
                        <img src="uploads/comments/<?php echo htmlspecialchars($comment['image_file']); ?>" alt="Comment image" class="mt-2 max-w-full h-auto">
                    <?php endif; ?>
                    <p class="text-sm text-gray-500 mt-2"><?php echo $comment['created_at']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>