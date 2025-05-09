<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clothies_rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = "SELECT * FROM rental_orders WHERE id = ? AND user_id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<p>Order not found.</p>";
    exit();
}

// Fetch rented items
$items_query = "SELECT * FROM rental_order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .order-details {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
        }
        .item-card {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #ffffff;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
            transition: transform 0.3s;
        }
        .item-card:hover {
            transform: scale(1.02);
        }
        .item-card img {
            width: 80px;
            height: auto;
            border-radius: 5px;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-container">
            <h2 class="text-center text-success">Order Confirmed!</h2>
            <p class="text-center text-muted">Thank you for renting with us. Your order details are below:</p>
            <div class="order-details">
                <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
                <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
                <p><strong>Rental Days:</strong> <?= htmlspecialchars($order['rental_days']) ?></p>
                <p><strong>Payment Method:</strong> <?= htmlspecialchars(ucfirst($order['payment_method'])) ?></p>
                <p><strong>Status:</strong> <span class="badge bg-success">Completed</span></p>
            </div>
            <h4 class="mt-4">Rented Items</h4>
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                    <div>
                        <h6><?= htmlspecialchars($item['product_name']) ?></h6>
                        <p class="text-muted">Category: <?= htmlspecialchars($item['category']) ?> | Size: <?= htmlspecialchars($item['size']) ?></p>
                        <p><strong>Quantity:</strong> <?= htmlspecialchars($item['quantity']) ?> | <strong>Price:</strong> $<?= number_format($item['price'], 2) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="text-center mt-4">
                <a href="welcome.php" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>