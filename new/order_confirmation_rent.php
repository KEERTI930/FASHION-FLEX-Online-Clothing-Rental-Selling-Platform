<?php
session_start();
include("db_config.php"); // Ensure db.php exists in the same directory

// Retrieve order ID from GET or session
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
} elseif (isset($_SESSION['order_id'])) {
    $order_id = $_SESSION['order_id'];
} else {
    echo "<script>alert('Invalid order!'); window.location.href='index.php';</script>";
    exit();
}

// Prepare SQL to fetch the rental order details
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "<script>alert('Order not found!'); window.location.href='index.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rental Order Confirmation - Fashion Flex</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
      .container {
          margin-top: 50px;
          text-align: center;
      }
      .confirmation-box {
          border: 2px solid #28a745;
          padding: 20px;
          border-radius: 10px;
          display: inline-block;
          text-align: left;
          max-width: 600px;
          width: 100%;
      }
      .success-icon {
          color: #28a745;
          font-size: 50px;
      }
      .btn-home {
          background-color: #007bff;
          color: white;
          font-size: 18px;
          padding: 10px 20px;
          border-radius: 5px;
          text-decoration: none;
          margin-top: 20px;
          display: inline-block;
      }
      .btn-home:hover {
          background-color: #0056b3;
      }
  </style>
</head>
<body>
<div class="container">
    <div class="confirmation-box">
        <div class="text-center">
            <span class="success-icon">✅</span>
            <h2>Thank You for Your Rental Order!</h2>
        </div>
        <p>Your rental order has been placed successfully.</p>
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order["id"]); ?></p>
        <p><strong>Product:</strong> <?php echo htmlspecialchars($order["product_name"]); ?> (<?php echo htmlspecialchars($order["product_size"]); ?>)</p>
        <p><strong>Rental Duration (Days):</strong> <?php echo htmlspecialchars($order["quantity"]); ?></p>
        <p><strong>Total Price:</strong> $<?php echo number_format($order["total_price"], 2); ?></p>
        <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order["user_address"]); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order["payment_method"]); ?></p>
        <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order["order_status"]); ?></p>
        <a href="welcome.php" class="btn-home">Continue Shopping</a>
    </div>
</div>
</body>
</html>
<?php
unset($_SESSION['order_id']); // Clear order_id to prevent duplicate displays
$stmt->close();
$conn->close();
?>
