<?php
session_start();
include("db_config.php"); // This file must be in the same directory

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure the user is logged in
    if (!isset($_SESSION["user_id"])) {
        echo "<script>alert('You need to log in to place an order.'); window.location.href='login.php';</script>";
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $user_name = $_POST["user_name"];
    $user_email = $_POST["user_email"];
    $user_phone = $_POST["user_phone"];
    $user_address = $_POST["user_address"];
    $payment_method = $_POST["payment_method"];
    $order_status = "Pending";
    
    // We'll process each cart item as a separate order record.
    // (In a real-world system you might combine items into one order with order_items)
    $last_order_id = 0;

    // Retrieve cart items for the logged-in user
    $cart_query = $conn->query("SELECT * FROM cart WHERE user_id = '$user_id'");
    
    if ($cart_query->num_rows > 0) {
        while ($row = $cart_query->fetch_assoc()) {
            $product_id    = $row["product_id"];
            $product_name  = $row["product_name"];
            $product_size  = $row["product_size"];
            $quantity      = $row["quantity"];
            $product_price = $row["product_price"];
            $subtotal      = $quantity * $product_price;
            
            // Insert order details into the orders table
            $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, product_name, product_size, quantity, product_price, total_price, user_name, user_email, user_phone, user_address, payment_method, order_status) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // Parameter types:
            // i = integer, s = string, d = double
            $stmt->bind_param("iissidddsssss", 
                $user_id, 
                $product_id, 
                $product_name, 
                $product_size, 
                $quantity, 
                $product_price, 
                $subtotal, 
                $user_name, 
                $user_email, 
                $user_phone, 
                $user_address, 
                $payment_method, 
                $order_status
            );

            if (!$stmt->execute()) {
                echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='cart.php';</script>";
                exit();
            }
            $last_order_id = $stmt->insert_id;
            $stmt->close();
        }

        // Clear the user's cart after order placement
        $conn->query("DELETE FROM cart WHERE user_id = '$user_id'");

        // Store the last order ID in the session and pass it as a GET parameter
        $_SESSION['order_id'] = $last_order_id;
        header("Location: confirm_order.php?order_id=" . $last_order_id);
        exit();
    } else {
        echo "<script>alert('Invalid order! Your cart is empty.'); window.location.href='cart.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='index.php';</script>";
}
?>
