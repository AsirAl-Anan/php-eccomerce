<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto px-4 py-8">
        <!-- Product details -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold text-gray-900 mb-6"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="product-images">
                    <?php
                    $images = explode(',', $product['images']);
                    foreach ($images as $image):
                        $imagePath = "../uploads/" . htmlspecialchars($image);
                    ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-auto object-cover mb-4 rounded-lg shadow-md">
                    <?php endforeach; ?>
                </div>
                <div class="product-details">
                    <p class="text-2xl font-bold text-gray-900 mb-4">$<?php echo number_format($product['price'], 2); ?></p>
                    <p class="text-lg text-gray-700 mb-6"><?php echo htmlspecialchars($product['description']); ?></p>
                    <div class="flex items-center mb-6">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-6 h-6 fill-current <?php echo $i <= $product['rating'] ? 'text-yellow-500' : 'text-gray-300'; ?>" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        <?php endfor; ?>
                        <span class="ml-2 text-gray-600"><?php echo $product['rating']; ?></span>
                    </div>
                    <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>

        <!-- Comments section -->
        <div class="mt-10">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Comments</h2>
            
            <!-- Comment form -->
            <?php if (isLoggedIn()): ?>
                <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-lg mb-6">
                    <textarea name="comment" rows="4" class="w-full p-4 border rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Write your comment here..."></textarea>
                    <input type="file" name="comment_image" accept="image/*" class="mb-4 text-sm">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300">Post Comment</button>
                </form>
            <?php else: ?>
                <p class="text-gray-600">Please <a href="login.php" class="text-blue-600 hover:underline">log in</a> to leave a comment.</p>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <p class="text-red-500 font-semibold"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <p class="text-green-500 font-semibold"><?php echo $success; ?></p>
            <?php endif; ?>

            <!-- Display comments -->
            <?php foreach ($comments as $comment): ?>
                <div class="bg-white p-6 rounded-lg shadow-md mb-6" id="comment-<?php echo $comment['id']; ?>">
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($comment['username']); ?></p>
                    <p class="comment-text text-gray-700 mt-2"><?php echo htmlspecialchars($comment['comment']); ?></p>
                    <?php if ($comment['image_file']): ?>
                        <img src="../uploads/comments/<?php echo htmlspecialchars($comment['image_file']); ?>" alt="Comment image" class="mt-4 rounded-lg shadow-md max-w-full h-auto">
                    <?php endif; ?>
                    <p class="text-sm text-gray-500 mt-4"><?php echo $comment['created_at']; ?></p>
                    <?php if (isLoggedIn() && $_SESSION['id'] == $comment['user_id']): ?>
                        <div class="flex mt-4">
                            <button onclick="editComment(<?php echo $comment['id']; ?>)" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg mr-2 shadow-md transition duration-300">Edit</button>
                            <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">Delete</button>
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
            textarea.classList.add('w-full', 'p-4', 'border', 'rounded-lg', 'mb-4', 'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-400');

            const saveButton = document.createElement('button');
            saveButton.innerText = 'Save';
            saveButton.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white', 'font-bold', 'py-2', 'px-4', 'rounded-lg', 'mr-2', 'shadow-md', 'transition', 'duration-300');

            const cancelButton = document.createElement('button');
            cancelButton.innerText = 'Cancel';
            cancelButton.classList.add('bg-gray-500', 'hover:bg-gray-600', 'text-white', 'font-bold', 'py-2', 'px-4', 'rounded-lg', 'shadow-md', 'transition', 'duration-300');

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
