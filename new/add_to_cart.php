<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "clothies_rental"; // Use your existing database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Get product details from the form
$product_id = $_POST['product_id'];
$product_name = $conn->real_escape_string($_POST['product_name']);
$product_size = $conn->real_escape_string($_POST['product_size']);
$product_price = $_POST['product_price'];
$product_description = $conn->real_escape_string($_POST['product_description']);
$product_image = $conn->real_escape_string($_POST['product_image']);

// Check if the product is already in the cart for the same user
$sql = "SELECT * FROM cart WHERE product_id = ? AND product_size = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $product_id, $product_size, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity if the product is already in the cart
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'] + 1;
    
    $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_quantity, $row['id']);
    $update_stmt->execute();
} else {
    // Insert new product into the cart
    $insert_sql = "INSERT INTO cart (product_id, product_name, product_size, product_price, product_description, product_image, quantity, user_id)
                   VALUES (?, ?, ?, ?, ?, ?, 1, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("issdssi", $product_id, $product_name, $product_size, $product_price, $product_description, $product_image, $user_id);
    $insert_stmt->execute();
}

// Return success response
echo json_encode(["status" => "success", "message" => "Product added to cart"]);
$conn->close();
?>
