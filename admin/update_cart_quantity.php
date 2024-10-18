<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$change = isset($_POST['change']) ? intval($_POST['change']) : 0;

if (!$user_id || !$item_id || !$change) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// First, get the current quantity
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_quantity = $result->fetch_assoc()['quantity'];

// Calculate new quantity
$new_quantity = $current_quantity + $change;

if ($new_quantity <= 0) {
    // Remove the item if new quantity is 0 or less
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $item_id, $user_id);
} else {
    // Update the quantity
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $new_quantity, $item_id, $user_id);
}

$result = $stmt->execute();

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
}