<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "clothies_rental"; // Use your existing database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Get product ID from the request
$productId = $_POST['id'];

// Delete the product from the cart
$sql = "DELETE FROM cart WHERE id = $productId";
if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Product removed from cart"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error removing product: " . $conn->error]);
}

$conn->close();
?>