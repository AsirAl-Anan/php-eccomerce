<?php
// Start the session at the beginning of your PHP file
session_start();

// Function to check if user is logged in
function isLoggedIn()
{
  return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

require_once '../config/config.php';
require_once '../admin/display_products.php';


$user_id =  $_SESSION['id'];




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
// Check if the user has purchased and received the product
$canRate = false;
if (isLoggedIn()) {
  $sql = "SELECT o.* FROM orders o
  INNER JOIN order_items oi ON o.id = oi.order_id
  WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $_SESSION['id'], $product_id);
$stmt->execute();
$result = $stmt->get_result();
$canRate = $result->num_rows > 0;
}

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rating'])) {
    if (!isLoggedIn()) {
        $error = "You must be logged in to rate products.";
    } elseif (!$canRate) {
        $error = "You can only rate products you've purchased and received.";
    } else {
        $rating = $_POST['rating'];
       
        
        $sql = "INSERT INTO product_ratings (user_id, product_id, rating, review) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE rating = ?, review = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiidds", $user_id, $product_id, $rating, $review, $rating, $review);
        
        if ($stmt->execute()) {
            $success = "Your rating has been submitted successfully.";
            // Update the product's average rating
            updateProductRating($product_id);
        } else {
            $error = "Error submitting your rating.";
        }
    }
}

// Function to update product's average rating
function updateProductRating($productId) {
    global $conn;
    $sql = "UPDATE products p
            SET rating = (
                SELECT AVG(rating) 
                FROM product_ratings 
                WHERE product_id = ?
            )
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $productId, $productId);
    $stmt->execute();
}

// Fetch the user's rating if it exists
$userRating = null;
if (isLoggedIn()) {
    $sql = "SELECT rating, review FROM product_ratings WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userRating = $result->fetch_assoc();
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
$sql = "SELECT pc.*, u.username, u.profile_picture 
        FROM product_comments pc 
        JOIN users u ON pc.user_id = u.id
        WHERE pc.product_id = ?
        ORDER BY pc.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);




?>



<!-- navigation bar ends -->




<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>E-commerce Site</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="css/index.css" />
  <script src="js/script.js"></script>


</head>

<body class="bg-white text-black">
  <!-- navigation bar -->
  <!-- navigation bar -->
  <div class="navbar text-black sticky top-0 z-50 !bg-white !text-black">
    <div class="navbar-start">
      <!-- responsive nav-bar -->
      <div class="dropdown">
        <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            title="menu">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
          </svg>
        </div>
        <ul tabindex="0"
          class="menu menu-sm dropdown-content bg-white text-black rounded-box z-[1] mt-3 w-52 p-2 shadow">
          <li><a class="hover:underline">Home</a></li>
          <li><a href="#featured" class="hover:underline">New & featured</a></li>
          <li>
            <a href="men.php" class="hover:underline">Men</a>
          
          </li>
          <li>
            <a class="hover:underline" href="women.php">Women</a>
           
          </li>
          <li><a class="hover:underline" href="kids.php">Kids</a></li>
        
          <li><a href="about.php" class="hover:underline">About & FAQ's</a></li>
        </ul>
      </div>
      <!-- responsive nav-bar ends -->
      <a class="btn btn-ghost text-xl hover:underline">Urban Store</a>
    </div>
    <div class="navbar-center hidden lg:flex">
      <ul class="menu menu-horizontal px-1 z-20">
        <li><a class="hover:underline">Home</a></li>
        <li><a href="#featured" class="hover:underline">New & featured</a></li>
        <li>
          <a href="men.php">Men</a>
        <li>
          <a href="women.php">Women</a>
        </li>
        <li><a class="hover:underline" href="kids.php">Kids</a></li>
       
        <li><a href="about.php" class="hover:underline" href="about.php">About & FAQ's</a></li>
      </ul>
    </div>
    <div class="navbar-end">

      <div class="dropdown">
        <div tabindex="0" role="button" class="btn !bg-gray-100 border-none !text-black m-1"><i
            class="fas fa-search"></i></div>
        <ul tabindex="0"
          class="dropdown-content menu !bg-white-500 rounded-box z-[1] w-70 p-2 shadow absolute left-1/2 transform -translate-x-1/2 mt-2">

          <form id="search-form" class="input input-bordered flex items-center gap-2">
            <input type="text" id="search-input" class="grow text-white" placeholder="Search" />
            <button type="submit" class="bg-transparent border-0 p-0 cursor-pointer">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                class="h-4 w-4 opacity-70">
                <path fill-rule="evenodd"
                  d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                  clip-rule="evenodd" />
              </svg>
            </button>
          </form>
          <div id="search-results"></div>
        </ul>
      </div>

      <!-- cart -->
      <button onclick="openCartDrawer()" class="relative m-5">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
          </path>
        </svg>
        <span id="cart-count"
          class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-white rounded-full px-1.5 py-0.5 text-xs leading-none">0</span>
      </button>

    </div>
    <div class="dropdown dropdown-end ">
      <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
        <div class="w-10 h-10 rounded-full overflow-hidden">
        <?php
$default_avatar = '../uploads/default_avatar.jpg'; // Use an absolute path
$profile_picture = isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) 
    ? $_SESSION['profile_picture'] 
    : $default_avatar;

if (!file_exists($profile_picture)) {
    $profile_picture = $default_avatar;
}

echo '<img src="' . htmlspecialchars($profile_picture) . '" alt="Profile Picture" class="w-full h-full object-cover" />';
?>
        </div>
      </div>
      <ul tabindex="0" class="menu menu-sm dropdown-content bg-gray-400 rounded-box z-[1] mt-3 w-52 p-2 shadow">
        <?php if (isLoggedIn()): ?>

          <li><a class="hover:underline" href="profile.php">Profile</a></li>
          <li><a class="hover:underline" href="../loginsystem/logout.php">Logout</a></li>
        <?php else: ?>
          <li><a class="hover:underline" href="../loginsystem/registration.php">Registration</a></li>
          <li><a class="hover:underline" href="../loginsystem/login.php">Login</a></li>
          <li><a href="../admin/admin_login.php" class="hover:underline">Admin Login</a></li>
        <?php endif; ?>
      </ul>
    </div>

  </div>

  <!-- navigation bar ends -->



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
            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
              class="w-full h-96 object-cover rounded-lg shadow-md transition-transform transform hover:scale-105">
          <?php endforeach; ?>
        </div>
        <div>
          <p class="text-3xl font-semibold text-blue-600 mb-4">$<?php echo number_format($product['price'], 2); ?></p>
          <p class="text-lg text-gray-600 mb-6"><?php echo htmlspecialchars($product['description']); ?></p>
          <div class="flex items-center mb-6">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <svg class="w-6 h-6 <?php echo $i <= $product['rating'] ? 'text-yellow-500' : 'text-gray-400'; ?>"
                viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
              </svg>
            <?php endfor; ?>
            <span class="ml-3 text-lg font-medium text-gray-500"><?php echo $product['rating']; ?>/5</span>
          </div>
          <button
            onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>); console.log('Add to Cart clicked');"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add to Cart
          </button>
          
          <a href="checkout.php?product_id=<?php echo $product['id']; ?>"
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Buy Now
          </a>
        </div>
      </div>
    </div>

    <!-- Add this where you want the rating form to appear -->
    <?php if ($canRate): ?>
    <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-semibold">Product Rating</h3>
            <button id="rateProductBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Rate the Product
            </button>
        </div>
        <form id="ratingForm" action="" method="POST" class="hidden">
            <div class="mb-4">
                <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">Your Rating:</label>
                <select name="rating" id="rating" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option class=" bg-white" value="">Select a rating</option>
                    <optio class=" bg-white"n value="5" <?php echo $userRating && $userRating['rating'] == 5 ? 'selected' : ''; ?>>5 Stars</option>
                    <option class=" bg-white" value="4" <?php echo $userRating && $userRating['rating'] == 4 ? 'selected' : ''; ?>>4 Stars</option>
                    <option class=" bg-white" value="3" <?php echo $userRating && $userRating['rating'] == 3 ? 'selected' : ''; ?>>3 Stars</option>
                    <option value="2" <?php echo $userRating && $userRating['rating'] == 2 ? 'selected' : ''; ?>>2 Stars</option>
                    <option value="1" <?php echo $userRating && $userRating['rating'] == 1 ? 'selected' : ''; ?>>1 Star</option>
                </select>
            </div>
            <button type="submit" name="submit_rating" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Submit Rating
            </button>
        </form>
    </div>
<?php endif; ?>
<div class="mt-4">
    <h3 class="text-xl font-semibold">Average Rating: <?php echo number_format($product['rating'], 1); ?> / 5</h3>
</div>
    <!-- Comments section -->
    <div class="bg-white p-8 rounded-lg shadow-md">
      <h2 class="text-3xl font-bold mb-6">Comments</h2>

      <!-- Comment form -->
      <?php if (isLoggedIn()): ?>
        <form action="" method="POST" enctype="multipart/form-data" class="mb-8">
          <textarea name="comment" rows="4"
            class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent mb-4 text-white"
            placeholder="Write your comment here..."></textarea>
          <input type="file" name="comment_image" accept="image/*" class="block mb-4">
          <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all transform hover:scale-105">
            Post Comment
          </button>
        </form>
      <?php else: ?>
        <p class="text-gray-500">Please <a href="../loginsystem/login.php"
            class="text-blue-600 font-medium hover:underline">log in</a> to leave a comment.</p>
      <?php endif; ?>

      <?php if (isset($error)): ?>
        <p class="text-red-500 mt-4"><?php echo $error; ?></p>
      <?php endif; ?>

      <?php if (isset($success)): ?>
        <p class="text-green-500 mt-4"><?php echo $success; ?></p>
      <?php endif; ?>

      <!-- Display comments -->
      <?php foreach ($comments as $comment): ?>
        <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-6 transition-transform transform hover:scale-105"
          id="comment-<?php echo $comment['id']; ?>">
          <div class="flex items-center mb-4">
            <?php
            $profilePicture = $comment['profile_picture'] ? "../uploads/" . htmlspecialchars($comment['profile_picture']) : '../uploads/default_avatar.jpg';
            ?>
            <img src="<?php echo $profilePicture; ?>" alt="Profile Picture"
              class="w-10 h-10 rounded-full mr-4 object-cover">
            <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($comment['username']); ?></p>
          </div>
          <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($comment['comment']); ?></p>
          <?php if ($comment['image_file']): ?>
            <img src="../uploads/comments/<?php echo htmlspecialchars($comment['image_file']); ?>" alt="Comment image"
              class="mt-4 rounded-lg max-w-full h-auto">
          <?php endif; ?>
          <p class="text-sm text-gray-400 mt-4"><?php echo $comment['created_at']; ?></p>
          <?php if (isLoggedIn() && $_SESSION['id'] == $comment['user_id']): ?>
            <div class="flex mt-4">
              <button onclick="editComment(<?php echo $comment['id']; ?>)"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded-lg shadow-sm mr-2 transition-all transform hover:scale-105">
                Edit
              </button>
              <button onclick="deleteComment(<?php echo $comment['id']; ?>)"
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-4 rounded-lg shadow-sm transition-all transform hover:scale-105">
                Delete
              </button>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>


  <!-- drawer -->
  <div id="cartDrawer"
    class="fixed inset-y-0 right-0 w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Your Cart</h2>
        <button onclick="closeCartDrawer()" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <div id="cartContents"></div>
    </div>
  </div>

  <!-- footer -->
  <?php include('footer.php'); ?>
  <!-- footer ends -->
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const rateProductBtn = document.getElementById('rateProductBtn');
    const ratingForm = document.getElementById('ratingForm');

    if (rateProductBtn && ratingForm) {
        rateProductBtn.addEventListener('click', function() {
            ratingForm.classList.toggle('hidden');
            rateProductBtn.textContent = ratingForm.classList.contains('hidden') ? 'Rate the Product' : 'Hide Rating Form';
        });
    }
});
    function addToCart(productId, productName, productPrice) {
      fetch('../admin/add_to_cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=1`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(`${productName} added to cart!`); //alert box
            updateCartCount(); // Update the cart count
            console.log('Product added to cart:', productId, productName, productPrice);
          } else {
            alert('Failed to add product to cart: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while adding the product to cart.');
        });
    }
    function editComment(commentId) {
      const commentDiv = document.getElementById(`comment-${commentId}`);
      const commentText = commentDiv.querySelector('p:nth-child(2)'); // Select the second paragraph, which contains the comment text
      const currentText = commentText.innerText;

      const textarea = document.createElement('textarea');
      textarea.value = currentText;
      textarea.classList.add('w-full', 'p-2', 'border', 'rounded', 'mb-2', 'text-white');

      const saveButton = document.createElement('button');
      saveButton.innerText = 'Save';
      saveButton.classList.add('bg-green-500', 'hover:bg-green-700', 'text-white', 'font-bold', 'py-1', 'px-2', 'rounded', 'mr-2');

      const cancelButton = document.createElement('button');
      cancelButton.innerText = 'Cancel';
      cancelButton.classList.add('bg-gray-500', 'hover:bg-gray-700', 'text-white', 'font-bold', 'py-1', 'px-2', 'rounded');

      commentText.replaceWith(textarea);
      commentDiv.appendChild(saveButton);
      commentDiv.appendChild(cancelButton);

      saveButton.onclick = function () {
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
              const newCommentText = document.createElement('p');
              newCommentText.innerText = newComment;
              newCommentText.classList.add('text-gray-600', 'mt-2');
              textarea.replaceWith(newCommentText);
              saveButton.remove();
              cancelButton.remove();
            });
        }
      };

      cancelButton.onclick = function () {
        const originalCommentText = document.createElement('p');
        originalCommentText.innerText = currentText;
        originalCommentText.classList.add('text-gray-600', 'mt-2');
        textarea.replaceWith(originalCommentText);
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

    document.getElementById('search-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const query = document.getElementById('search-input').value;
      performSearch(query);
    });

    function performSearch(query) {
      fetch(`search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => displayResults(data))
        .catch(error => console.error('Error:', error));
    }

    function displayResults(results) {
      const resultsContainer = document.getElementById('search-results');
      resultsContainer.innerHTML = '';

      if (results.length === 0) {
        resultsContainer.innerHTML = '<p class="text-white">No results found.</p>';
        return;
      }

      const ul = document.createElement('ul');
      ul.className = 'list-none p-0';
      results.forEach(product => {
        const li = document.createElement('li');
        li.className = 'mb-2';
        li.innerHTML = `<a href="product.php?id=${product.id}" class="text-white hover:underline">${product.name} - $${product.price}</a>`;
        ul.appendChild(li);
      });

      resultsContainer.appendChild(ul);

    }
    function openCartDrawer() {
      document.getElementById('cartDrawer').classList.remove('translate-x-full');
      loadCartContents();
    }

    function closeCartDrawer() {
      document.getElementById('cartDrawer').classList.add('translate-x-full');
    }

    function loadCartContents() {
      fetch('../admin/cart.php?action=get_cart_data')
        .then(response => response.json())
        .then(data => {
          const cartContents = document.getElementById('cartContents');
          if (data.cartItems.length === 0) {
            cartContents.innerHTML = '<p class="text-center py-4">Your cart is empty.</p>';
          } else {
            let html = '<div class="space-y-4">';
            data.cartItems.forEach(item => {
              html += `
                        <div class="flex items-center justify-between border-b pb-2">
                            <div class="flex items-center space-x-4">
                                <img src="../uploads/${item.image_file || 'default.jpg'}" alt="${item.name}" class="w-16 h-16 object-cover">
                                <div>
                                    <h3 class="font-semibold">${item.name}</h3>
                                    <p class="text-gray-600">$${parseFloat(item.price).toFixed(2)}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="updateQuantity(${item.id}, -1)" class="bg-gray-200 px-2 py-1 rounded">-</button>
                                <span>${item.quantity}</span>
                                <button onclick="updateQuantity(${item.id}, 1)" class="bg-gray-200 px-2 py-1 rounded">+</button>
                            </div>
                        </div>
                    `;
            });
            html += '</div>';
            html += `
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold">Subtotal:</span>
                            <span>$${parseFloat(data.total).toFixed(2)}</span>
                        </div>
                      
                        <a href="checkout.php" class=" btn btn-info bg-green-500 text-white py-2 px-4 rounded w-full  font-semibold">Proceed to Checkout</a>
                    </div>
                `;
            cartContents.innerHTML = html;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('cartContents').innerHTML = '<p class="text-center py-4">Error loading cart contents.</p>';
        });
    }

    function updateQuantity(itemId, change) {
      fetch('../admin/update_cart_quantity.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}&change=${change}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            loadCartContents(); // Reload cart contents
            updateCartCount(); // Update the cart count
          } else {
            alert('Failed to update quantity: ' + data.message);
          }
        })
        .catch(error => console.error('Error:', error));
    }
    function checkout() {
      // Implement checkout functionality
      alert('Checkout functionality to be implemented');
    }
    function updateCartCount() {
      fetch('../admin/get_cart_count.php')
        .then(response => response.text()) // Changed to text()
        .then(count => {
          document.getElementById('cart-count').textContent = count;
        })
        .catch(error => console.error('Error:', error));
    }

    // Call this function when the page loads and after adding/removing items from the cart
    document.addEventListener('DOMContentLoaded', updateCartCount);
    // Call this function when the page loads and after adding/removing items from the cart
    document.addEventListener('DOMContentLoaded', updateCartCount);

    // Load cart count on page load
    document.addEventListener('DOMContentLoaded', function () {
      fetch('../admin/get_cart_count.php')
        .then(response => response.text())
        .then(count => {
          document.getElementById('cart-count').textContent = count;
        })
        .catch(error => console.error('Error:', error));
    });
  </script>
</body>

</html>
