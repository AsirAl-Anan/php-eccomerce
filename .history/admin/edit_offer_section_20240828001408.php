<?php
require_once('../config/config.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_offer'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $image = $_FILES['image'];
        
        $target_dir = "uploads/";
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