<?php
require_once('../config/config.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_offer'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $image = $_FILES['image'];
        
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($image["name"]);
        
        if (move_uploaded_file($image["tmp_name"], $target_file)) {
            $sql = "INSERT INTO offers (title, image_path) VALUES ('$title', '$target_file')";
            $conn->query($sql);
        }
    } elseif (isset($_POST['update_timer'])) {
        $end_time = $conn->real_escape_string($_POST['end_time']);
        $sql = "UPDATE offer_timer SET end_time = '$end_time'";
        $conn->query($sql);
    }
}

// Fetch existing offers
$sql = "SELECT * FROM offers ORDER BY id DESC";
$offers = $conn->query($sql);

// Fetch current timer
$sql = "SELECT end_time FROM offer_timer LIMIT 1";
$result = $conn->query($sql);
$timer = $result->fetch_assoc();
$sql = "CREATE TABLE IF NOT EXISTS offer_slides (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    button_text VARCHAR(100),
    button_link VARCHAR(255),
    image_file VARCHAR(255),
    slide_order INT(6)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table offer_slides created successfully or already exists";
} else {
    echo "Error creating table: " . $conn->error;
}

// Add slide
if (isset($_POST['add_slide'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $slide_order = $_POST['slide_order'];

    // Handle file upload
    $target_dir = "../uploads/offers/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["image_file"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is actual image or fake image
    $check = getimagesize($_FILES["image_file"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["image_file"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // If everything is ok, try to upload file
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
            $image_file = basename($_FILES["image_file"]["name"]);
            $sql = "INSERT INTO offer_slides (title, description, button_text, button_link, image_file, slide_order) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $title, $description, $button_text, $button_link, $image_file, $slide_order);

            if ($stmt->execute()) {
                echo "New offer slide added successfully.";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Fetch all slides
function getAllSlides($conn) {
    $sql = "SELECT * FROM offer_slides ORDER BY slide_order";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$slides = getAllSlides($conn);

// Delete slide
if (isset($_POST['delete_slide'])) {
    $slide_id = $_POST['slide_id'];
    $sql = "DELETE FROM offer_slides WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $slide_id);
    if ($stmt->execute()) {
        echo "Slide deleted successfully.";
    } else {
        echo "Error deleting slide: " . $conn->error;
    }
}

// Edit slide
if (isset($_POST['edit_slide'])) {
    $slide_id = $_POST['slide_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $slide_order = $_POST['slide_order'];

    $sql = "UPDATE offer_slides SET title = ?, description = ?, button_text = ?, button_link = ?, slide_order = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $title, $description, $button_text, $button_link, $slide_order, $slide_id);

    if ($stmt->execute()) {
        // Check if a new image was uploaded
        if ($_FILES['image_file']['size'] > 0) {
            $target_dir = "../uploads/offers/";
            $target_file = $target_dir . basename($_FILES["image_file"]["name"]);
            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                $image_file = basename($_FILES["image_file"]["name"]);
                $sql = "UPDATE offer_slides SET image_file = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $image_file, $slide_id);
                $stmt->execute();
            }
        }
        echo "Slide updated successfully.";
    } else {
        echo "Error updating slide: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offer Section Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-8">Offer Section Admin</h1>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Add New Offer</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <input type="text" name="title" placeholder="Offer Title" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <input type="file" name="image" required class="w-full p-2 border rounded">
                </div>
                <button type="submit" name="add_offer" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Offer</button>
            </form>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Current Offers</h2>
            <ul class="space-y-4">
            <?php while ($offer = $offers->fetch_assoc()): ?>
                <li class="flex items-center space-x-4">
                    <img src="<?php echo $offer['image_path']; ?>" alt="<?php echo $offer['title']; ?>" class="w-24 h-24 object-cover rounded">
                    <span class="font-medium"><?php echo $offer['title']; ?></span>
                </li>
            <?php endwhile; ?>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-semibold mb-4">Update Timer</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <input type="datetime-local" name="end_time" value="<?php echo date('Y-m-d\TH:i', strtotime($timer['end_time'])); ?>" required class="w-full p-2 border rounded">
                </div>
                <button type="submit" name="update_timer" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Update Timer</button>
            </form>
        </section>
    </div>
</body>
</html>