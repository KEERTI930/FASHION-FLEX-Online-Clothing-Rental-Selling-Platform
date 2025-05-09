<?php
session_start();
include 'db_config.php'; // Ensure this file connects to your database

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please login to view your order history.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Determine which type of orders to display
$view = isset($_GET['view']) ? $_GET['view'] : 'rent';

if ($view == 'rent') {
    $sql = "SELECT * FROM rental_orders WHERE user_id = ? ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = ?) ORDER BY order_id DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .wrapper {
            display: flex;
            margin-top: 56px; /* Adjust based on navbar height */
        }
        .sidebar {
            width: 250px;
            height: calc(100vh - 56px);
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="welcome.php">FASHION FLEX</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
                <li class="nav-item">
                    <a class="nav-link" href="welcome.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.html">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="help.html">Help</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="feedback.php">Feedback</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="order_history.php">History</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sidebar and Content -->
<div class="wrapper">
    <div class="sidebar">
        <h4 class="text-center">Order History</h4>
        <a href="order_history.php?view=buy" class="<?= $view == 'buy' ? 'active' : '' ?>">Buy Orders</a>
        <a href="order_history.php?view=rent" class="<?= $view == 'rent' ? 'active' : '' ?>">Rent Orders</a>
    </div>

    <div class="content">
        <h2>Your <?= $view == 'rent' ? 'Rent' : 'Buy' ?> Order History</h2>

        <?php if ($result->num_rows > 0) { ?>
            <table>
                <tr>
                    <?php if ($view == 'rent') { ?>
                        <th>Order ID</th>
                        <th>Total Amount</th>
                        <th>Rental Days</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Date</th>
                    <?php } else { ?>
                        <th>Order ID</th>
                        <th>Product ID</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    <?php } ?>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <?php if ($view == 'rent') { ?>
                            <td><?= $row['id']; ?></td>
                            <td>$<?= $row['total_amount']; ?></td>
                            <td><?= $row['rental_days']; ?></td>
                            <td><?= ucfirst($row['payment_method']); ?></td>
                            <td><?= ucfirst($row['payment_status']); ?></td>
                            <td><?= $row['created_at']; ?></td>
                        <?php } else { ?>
                            <td><?= $row['order_id']; ?></td>
                            <td><?= $row['product_id']; ?></td>
                            <td><?= $row['quantity']; ?></td>
                            <td>$<?= $row['price']; ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </table>
        <?php } else {
            echo "<p>No orders found.</p>";
        } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
