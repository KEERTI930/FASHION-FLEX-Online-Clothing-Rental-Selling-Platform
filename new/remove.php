<?php
session_start(); // Start the session

// Database connection
$host = 'localhost';
$dbname = 'clothies_rental';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to remove items from your cart.");
}

// Get the cart item ID from the form submission
if (!isset($_POST['cart_item_id'])) {
    die("Cart item ID is missing.");
}
$cart_item_id = $_POST['cart_item_id'];

// Delete the item from the cart
$sql = "DELETE FROM rent_cart WHERE id = :cart_item_id AND user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':cart_item_id', $cart_item_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

if ($stmt->execute()) {
    // Redirect back to the cart page
    header("Location: add_rent_cart.php");
    exit();
} else {
    die("Failed to remove item from cart.");
}