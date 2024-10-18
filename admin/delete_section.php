<?php
session_start();
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete the section
    $sql = "DELETE FROM about_page_content WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header('Location: edit_about_page.php');
exit;
