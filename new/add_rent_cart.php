<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clothies_rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "Please login first"]));
}

// Get product ID
$product_id = (int)$_GET['id'];
if ($product_id <= 0) {
    die(json_encode(["status" => "error", "message" => "Invalid product"]));
}

// Get product details (REMOVED status check)
$product_query = "SELECT * FROM items_for_rent WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die(json_encode(["status" => "error", "message" => "Product not found"]));
}

// Check if item already in cart_rent
$check_query = "SELECT * FROM cart_rent WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
$stmt->execute();
$existing_item = $stmt->get_result()->fetch_assoc();

if ($existing_item) {
    // Update quantity
    $update_query = "UPDATE cart_rent SET quantity = quantity + 1 WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $existing_item['id']);
} else {
    // Insert new item
    $insert_query = "INSERT INTO cart_rent 
                    (user_id, product_id, product_name, price, image_path, size, category, quantity)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param(
        "iisdsss",
        $_SESSION['user_id'],
        $product_id,
        $product['item_name'],
        $product['price'],
        $product['image_path'],
        $product['size'],
        $product['category']
    );
}

if ($stmt->execute()) {
    // Get updated cart count
    $count_query = "SELECT SUM(quantity) as count FROM cart_rent WHERE user_id = ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $count_result = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        "status" => "success", 
        "message" => "Added to rental cart",
        "cart_count" => $count_result['count'] ?? 0
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update cart: " . $conn->error]);
}

$conn->close();
?>