<?php
session_start();
include("db_config.php"); // Ensure db.php exists and creates a valid $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION["user_id"])) {
        echo "<script>alert('Please log in to place a rental order.'); window.location.href='login.php';</script>";
        exit();
    }
    
    $user_id = $_SESSION["user_id"];
    $user_name = $_POST["user_name"];
    $user_email = $_POST["user_email"];
    $user_phone = $_POST["user_phone"];
    $user_address = $_POST["user_address"];
    $payment_method = $_POST["payment_method"];
    $order_status = "Pending";
    
    // Retrieve product details from POST
    $product_id = $_POST["product_id"];
    $product_name = $_POST["product_name"];
    $product_size = $_POST["product_size"];
    $product_price = $_POST["product_price"];
    $quantity = $_POST["quantity"];  // Here, quantity represents the number of rental days
    $total_price = $product_price * $quantity;
    
    // Insert rental order into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, product_name, product_size, quantity, product_price, total_price, user_name, user_email, user_phone, user_address, payment_method, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // Parameter types: i (user_id), i (product_id), s (product_name), s (product_size), i (quantity), d (product_price), d (total_price), then s for each string field.
    $stmt->bind_param("iissidddsssss",
        $user_id,
        $product_id,
        $product_name,
        $product_size,
        $quantity,
        $product_price,
        $total_price,
        $user_name,
        $user_email,
        $user_phone,
        $user_address,
        $payment_method,
        $order_status
    );
    
    if ($stmt->execute()) {
        $last_order_id = $stmt->insert_id;
        // Optionally, if you store rental orders separately or clear a temporary rental cart, do that here.
        
        // Set order ID in session and redirect to order confirmation page
        $_SESSION['order_id'] = $last_order_id;
        header("Location: order_confirmation_rent.php?order_id=" . $last_order_id);
        exit();
    } else {
        echo "<script>alert('Error placing rental order: " . $stmt->error . "'); window.location.href='rent_checkout.php';</script>";
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request.'); window.location.href='index.php';</script>";
}
?>
