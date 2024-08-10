<?php
require_once '../config/config.php';

$username = "admin"; // Change this to your desired admin username
$password = "admin"; // Change this to your desired admin password

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO admin_users (username, password) VALUES (?, ?)";

if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("ss", $username, $hashed_password);
    
    if($stmt->execute()){
        echo "Admin account created successfully.";
    } else{
        echo "Error creating admin account.";
    }

    $stmt->close();
}

$conn->close();
?>