<?php
// Include your database connection
require_once '../config/config.php'; // Update the path if necessary

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Validate email format
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);

        // Execute the statement
        if ($stmt->execute()) {
            echo 'success';
        } else {
            // Handle error, such as if the email is already subscribed
            echo 'error';
        }

        $stmt->close();
    } else {
        echo 'invalid';
    }

    $conn->close();
}
?>
