<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "Please login first"]));
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clothies_rental");
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

$action = $_POST['action'] ?? '';
$itemId = (int)($_POST['id'] ?? 0);

if ($itemId <= 0) {
    die(json_encode(["status" => "error", "message" => "Invalid item"]));
}

switch ($action) {
    case 'update':
        $quantity = (int)($_POST['quantity'] ?? 1);
        if ($quantity < 1) $quantity = 1;
        
        $stmt = $conn->prepare("UPDATE cart_rent SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $itemId, $_SESSION['user_id']);
        break;
        
    case 'remove':
        $stmt = $conn->prepare("DELETE FROM cart_rent WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $itemId, $_SESSION['user_id']);
        break;
        
    default:
        die(json_encode(["status" => "error", "message" => "Invalid action"]));
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Cart updated"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update cart"]);
}

$conn->close();
?>