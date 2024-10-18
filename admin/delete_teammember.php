<?php
session_start();
require_once '../config/config.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Get team member ID from URL
if (!isset($_GET['id'])) {
    header("Location: manage_teammembers.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch the team member's current image path for deletion
$team_query = $conn->prepare("SELECT image FROM team_members WHERE id = ?");
$team_query->bind_param("i", $id);
$team_query->execute();
$team_result = $team_query->get_result();
$team_member = $team_result->fetch_assoc();
$image_path = "../uploads/group-members/" . $team_member['image'];

// Delete the team member from the database
$stmt = $conn->prepare("DELETE FROM team_members WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Delete the image file from the server if it exists
if (file_exists($image_path)) {
    unlink($image_path);
}

// Redirect to manage team members page
header("Location: manage_teammembers.php");
exit;
?>
