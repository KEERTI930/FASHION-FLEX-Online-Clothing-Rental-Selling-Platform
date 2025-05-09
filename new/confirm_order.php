<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clothies_rental";

// Connect to Database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    echo "<script>alert('Invalid order!'); window.location.href='welcome.php';</script>";
    exit();
}

$order_id = $_GET['order_id'];

// Fetch Order Details
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "<script>alert('Order not found!'); window.location.href='welcome.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .container { margin-top: 50px; text-align: center; }
        .confirmation-box { border: 2px solid #28a745; padding: 20px; border-radius: 10px; }
        .success-icon { color: #28a745; font-size: 50px; }
        .btn-home { background-color: #007bff; color: white; font-size: 18px; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
        .btn-home:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <div class="confirmation-box">
        <h1 class="success-icon">✅</h1>
        <h2>Thank You for Your Order!</h2>
        <p>Your order has been placed successfully.</p>
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
        <p><strong>Product:</strong> <?php echo htmlspecialchars($order['product_name']); ?> (<?php echo htmlspecialchars($order['product_size']); ?>)</p>
        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
        <p><strong>Total Price:</strong> $<?php echo htmlspecialchars($order['total_price']); ?></p>
        <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['user_address']); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
        <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>

        <a href="welcome.php" class="btn-home">Back to Home</a>
    </div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
