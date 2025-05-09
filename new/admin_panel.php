<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clothies_rental";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
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
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">FASHION FLEX</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="welcome.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="help.html">Help</a></li>
                <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
                <li class="nav-item"><a class="nav-link" href="order_history.php">History</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sidebar and Content -->
<div class="wrapper">
    <div class="sidebar">
        <h4 class="text-center">Admin Panel</h4>
        <a href="?view=buy">Buy Orders</a>
        <a href="?view=rent">Rent Orders</a>
    </div>

    <div class="content">
        <h2>Order Management</h2>
        <?php
        if (isset($_GET['view']) && $_GET['view'] == 'buy') {
            echo "<h3>Buy Orders</h3>";
            $sql = "SELECT o.order_id, oi.product_id, oi.quantity, oi.price FROM orders o INNER JOIN order_items oi ON o.order_id = oi.order_id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo "<table class='table table-bordered'>";
                echo "<tr><th>Order ID</th><th>Product ID</th><th>Quantity</th><th>Price</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>{$row['order_id']}</td><td>{$row['product_id']}</td><td>{$row['quantity']}</td><td>\${$row['price']}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No buy orders found.</p>";
            }
        } elseif (isset($_GET['view']) && $_GET['view'] == 'rent') {
            echo "<h3>Rent Orders</h3>";
            // Corrected query to match existing schema (check column names in rental_order_items)
            $sql = "SELECT ro.order_id, ri.product_name, ri.category, ri.size, ri.quantity, ri.price FROM rental_orders ro INNER JOIN rental_order_items ri ON ro.order_id = ri.order_id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo "<table class='table table-bordered'>";
                echo "<tr><th>Order ID</th><th>Product Name</th><th>Category</th><th>Size</th><th>Quantity</th><th>Price</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>{$row['order_id']}</td><td>{$row['product_name']}</td><td>{$row['category']}</td><td>{$row['size']}</td><td>{$row['quantity']}</td><td>\${$row['price']}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No rent orders found.</p>";
            }
        } else {
            echo "<p>Please select an option from the sidebar.</p>";
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
