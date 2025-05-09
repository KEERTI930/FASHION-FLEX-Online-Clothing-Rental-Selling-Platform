<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clothies_rental";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve product details from the form
$product_id    = isset($_POST['product_id']) ? $_POST['product_id'] : '';
$product_name  = isset($_POST['product_name']) ? $_POST['product_name'] : '';
$product_size  = isset($_POST['product_size']) ? $_POST['product_size'] : '';
$product_price = isset($_POST['product_price']) ? $_POST['product_price'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($product_name); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        .checkout-box {
            max-width: 400px;
            width: 100%;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f9f9f9;
        }
        .btn-payment {
            background-color: #28a745;
            color: white;
            font-size: 18px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }
        .btn-payment:hover {
            background-color: #218838;
        }
        .btn-back {
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            text-align: center;
            width: 100%;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function updateTotalPrice() {
            let quantity = document.getElementById('quantity').value;
            let price = <?php echo $product_price; ?>;
            document.getElementById('total_price').innerText = '$' + (quantity * price).toFixed(2);
        }
    </script>
</head>
<body>

<div class="container">
    <div class="checkout-box">
        <h3 class="text-center">Checkout</h3>
        <p><strong>Product:</strong> <?php echo htmlspecialchars($product_name); ?></p>
        <p><strong>Size:</strong> <?php echo htmlspecialchars($product_size); ?></p>
        <p><strong>Price per item:</strong> $<?php echo htmlspecialchars($product_price); ?></p>

        <form action="place_order.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>">
            <input type="hidden" name="product_size" value="<?php echo htmlspecialchars($product_size); ?>">
            <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product_price); ?>">
            
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" class="form-control mb-2" min="1" value="1" onchange="updateTotalPrice()">
            
            <p><strong>Total Price:</strong> <span id="total_price">$<?php echo htmlspecialchars($product_price); ?></span></p>

            <h4>Shipping Details</h4>
            <input type="text" name="user_name" class="form-control mb-2" placeholder="Full Name" required>
            <input type="email" name="user_email" class="form-control mb-2" placeholder="Email" required>
            <input type="text" name="user_phone" class="form-control mb-2" placeholder="Phone Number" required>
            <textarea name="user_address" class="form-control mb-2" placeholder="Full Address" required></textarea>

            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" class="form-select mb-3" required>
                <option value="COD">Cash on Delivery (COD)</option>
                <option value="Credit Card">Credit Card</option>
                <option value="PayPal">PayPal</option>
            </select>

            <button type="submit" class="btn btn-payment">Place Order</button>
        </form>
        <a href="buy_cloths.php" class="btn btn-back">Back to Shop</a>
    </div>
</div>

</body>
</html>
