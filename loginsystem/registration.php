<?php
require_once '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);
    $age = intval($_POST["age"]);
    $gender = $_POST["gender"];
    $payment_info = trim($_POST["payment_info"]);
    
    // Validate input
    if (empty($username) || empty($password) || empty($email) || empty($address) || empty($age) || empty($gender) || empty($payment_info)) {
        $error = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($age < 18 || $age > 120) {
        $error = "Invalid age. Must be between 18 and 120.";
    } elseif (!in_array($gender, ['Male', 'Female', 'Other'])) {
        $error = "Invalid gender selection.";
    } else {
        // Check if username or email already exists
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Insert new user
            $sql = "INSERT INTO users (username, password, email, address, age, gender, payment_info) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssssiss", $username, $hashed_password, $email, $address, $age, $gender, $payment_info);
            
            if ($stmt->execute()) {
                header("location: login.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="w-full max-w-lg">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h2 class="text-3xl font-bold text-gray-700 text-center mb-6">Register</h2>
            <?php if (isset($error)) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Username:</label>
                    <input type="text" name="username" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Password:</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Email:</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Address:</label>
                    <textarea name="address" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Age:</label>
                    <input type="number" name="age" min="18" max="120" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Gender:</label>
                    <select name="gender" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                        <option value="" disabled selected>Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Payment Information:</label>
                    <input type="text" name="payment_info" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div>
                    <input type="submit" value="Register" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
