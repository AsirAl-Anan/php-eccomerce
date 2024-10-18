<?php
session_start();
require_once '../config/config.php';

// Debug: Check session status


// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
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

/// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (!isLoggedIn()) {
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
                $stmt->bind_param("iiss", $product_id, $_SESSION['id'], $comment, $image_file);
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_comment'])) {
    if (!isLoggedIn()) {
        $error = "You must be logged in to edit a comment.";
    } else {
        $comment_id = $_POST['comment_id'];
        $new_comment = trim($_POST['new_comment']);
        if (!empty($new_comment)) {
            $sql = "UPDATE product_comments SET comment = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $new_comment, $comment_id, $_SESSION['id']);
            if ($stmt->execute()) {
                $success = "Comment updated successfully.";
            } else {
                $error = "Error updating comment.";
            }
        } else {
            $error = "Comment cannot be empty.";
        }
    }
}

// Handle comment delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_comment'])) {
    if (!isLoggedIn()) {
        $error = "You must be logged in to delete a comment.";
    } else {
        $comment_id = $_POST['comment_id'];
        $sql = "DELETE FROM product_comments WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $comment_id, $_SESSION['id']);
        if ($stmt->execute()) {
            $success = "Comment deleted successfully.";
        } else {
            $error = "Error deleting comment.";
        }
    }
}

// ... (rest of the previous code)

// Modify the comment fetching query to include the user_id
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
<body class="bg-gray-50 text-gray-900">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Product details -->
        <div class="mb-12">
            <h1 class="text-4xl font-extrabold text-gray-800 mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="space-y-4">
                    <?php
                    $images = explode(',', $product['images']);
                    foreach ($images as $image):
                        $imagePath = "../uploads/" . htmlspecialchars($image);
                    ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-96 object-cover rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <?php endforeach; ?>
                </div>
                <div>
                    <p class="text-3xl font-semibold text-blue-600 mb-4">$<?php echo number_format($product['price'], 2); ?></p>
                    <p class="text-lg text-gray-600 mb-6"><?php echo htmlspecialchars($product['description']); ?></p>
                    <div class="flex items-center mb-6">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-6 h-6 <?php echo $i <= $product['rating'] ? 'text-yellow-500' : 'text-gray-400'; ?>" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        <?php endfor; ?>
                        <span class="ml-3 text-lg font-medium text-gray-500"><?php echo $product['rating']; ?>/5</span>
                    </div>
                    <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all transform hover:scale-105">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>

        <!-- Comments section -->
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-3xl font-bold mb-6">Comments</h2>
            
            <!-- Comment form -->
            <?php if (isLoggedIn()): ?>
                <form action="" method="POST" enctype="multipart/form-data" class="mb-8">
                    <textarea name="comment" rows="4" class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent mb-4" placeholder="Write your comment here..."></textarea>
                    <input type="file" name="comment_image" accept="image/*" class="block mb-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all transform hover:scale-105">
                        Post Comment
                    </button>
                </form>
            <?php else: ?>
                <p class="text-gray-500">Please <a href="login.php" class="text-blue-600 font-medium hover:underline">log in</a> to leave a comment.</p>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <p class="text-red-500 mt-4"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <p class="text-green-500 mt-4"><?php echo $success; ?></p>
            <?php endif; ?>

            <!-- Display comments -->
            <?php foreach ($comments as $comment): ?>
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-6 transition-transform transform hover:scale-105" id="comment-<?php echo $comment['id']; ?>">
                    <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($comment['username']); ?></p>
                    <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($comment['comment']); ?></p>
                    <?php if ($comment['image_file']): ?>
                        <img src="../uploads/comments/<?php echo htmlspecialchars($comment['image_file']); ?>" alt="Comment image" class="mt-4 rounded-lg max-w-full h-auto">
                    <?php endif; ?>
                    <p class="text-sm text-gray-400 mt-4"><?php echo $comment['created_at']; ?></p>
                    <?php if (isLoggedIn() && $_SESSION['id'] == $comment['user_id']): ?>
                        <div class="flex mt-4">
                            <button onclick="editComment(<?php echo $comment['id']; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded-lg shadow-sm mr-2 transition-all transform hover:scale-105">
                                Edit
                            </button>
                            <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-4 rounded-lg shadow-sm transition-all transform hover:scale-105">
                                Delete
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
    function editComment(commentId) {
        const commentDiv = document.getElementById(`comment-${commentId}`);
        const commentText = commentDiv.querySelector('.comment-text');
        const currentText = commentText.innerText;

        const textarea = document.createElement('textarea');
        textarea.value = currentText;
        textarea.classList.add('w-full', 'p-2', 'border', 'rounded', 'mb-2');

        const saveButton = document.createElement('button');
        saveButton.innerText = 'Save';
        saveButton.classList.add('bg-green-500', 'hover:bg-green-700', 'text-white', 'font-bold', 'py-1', 'px-2', 'rounded', 'mr-2');

        const cancelButton = document.createElement('button');
        cancelButton.innerText = 'Cancel';
        cancelButton.classList.add('bg-gray-500', 'hover:bg-gray-700', 'text-white', 'font-bold', 'py-1', 'px-2', 'rounded');

        commentText.replaceWith(textarea);
        commentDiv.appendChild(saveButton);
        commentDiv.appendChild(cancelButton);

        saveButton.onclick = function() {
            const newComment = textarea.value;
            if (newComment.trim() !== '') {
                const formData = new FormData();
                formData.append('edit_comment', '1');
                formData.append('comment_id', commentId);
                formData.append('new_comment', newComment);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(() => {
                    commentText.innerText = newComment;
                    textarea.replaceWith(commentText);
                    saveButton.remove();
                    cancelButton.remove();
                });
            }
        };

        cancelButton.onclick = function() {
            textarea.replaceWith(commentText);
            saveButton.remove();
            cancelButton.remove();
        };
    }

    function deleteComment(commentId) {
        if (confirm('Are you sure you want to delete this comment?')) {
            const formData = new FormData();
            formData.append('delete_comment', '1');
            formData.append('comment_id', commentId);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                const commentDiv = document.getElementById(`comment-${commentId}`);
                commentDiv.remove();
            });
        }
    }
    </script>
</body>
</html>
