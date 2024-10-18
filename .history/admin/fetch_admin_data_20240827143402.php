<?php
session_start();
require_once '../config/config.php';  // Adjust this path as needed

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

$admin_id = $_SESSION["admin_id"];
$admin_data = null;

// Fetch current admin data
$sql = "SELECT * FROM admin_users WHERE id = ?";
if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("i", $admin_id);
    
    if($stmt->execute()){
        $result = $stmt->get_result();
        
        if($result->num_rows == 1){
            $admin_data = $result->fetch_assoc();
        } else {
            // Handle error - admin data not found
            error_log("Error: Admin data not found for ID: $admin_id");
        }
    } else {
        // Handle error - SQL execution failed
        error_log("Error executing SQL: " . $stmt->error);
    }

    $stmt->close();
} else {
    // Handle error - SQL preparation failed
    error_log("Error preparing SQL: " . $conn->error);
}

// Close the database connection if it's not needed anymore
 $conn->close();
?>