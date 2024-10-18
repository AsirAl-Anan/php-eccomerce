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
