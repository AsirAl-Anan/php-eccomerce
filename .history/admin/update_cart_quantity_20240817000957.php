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

$stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ? AND user_id = ? AND quantity + ? > 0");
$stmt->bind_param("iiii", $change, $item_id, $user_id, $change);
$result = $stmt->execute();

if ($result) {
    // If quantity becomes 0, remove the item from the cart
    if ($change < 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ? AND quantity <= 0");
        $stmt->bind_param("ii", $item_id, $user_id);
        $stmt->execute();
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
}