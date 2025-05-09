<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clothies_rental";

// Create connection using mysqli
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve product details from POST
$product_id    = isset($_POST['product_id']) ? $_POST['product_id'] : '';
$product_name  = isset($_POST['product_name']) ? $_POST['product_name'] : '';
$product_size  = isset($_POST['product_size']) ? $_POST['product_size'] : '';
$product_price = isset($_POST['product_price']) ? $_POST['product_price'] : 0;
$product_image = isset($_POST['product_image']) ? $_POST['product_image'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rent Checkout - <?php echo htmlspecialchars($product_name); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .container { margin-top: 50px; }
    .product-image { max-width: 100%; border-radius: 10px; }
    .quantity-input { width: 80px; text-align: center; }
    .btn-payment { 
        background-color: #28a745; 
        color: white; 
        font-size: 18px; 
        padding: 10px 20px; 
        border: none; 
        border-radius: 5px; 
        display: inline-block; 
    }
    .btn-payment:hover { background-color: #218838; }
    .total-price { font-size: 20px; font-weight: bold; color: #d9534f; }
    .btn-custom {
            width: 100%;
            font-weight: bold;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
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
<div class="container text-start back-btn">
        <a href="buy_for_rent.php" class="btn btn-outline-primary">
            ← Back 
        </a>
    </div>
<div class="container">
  <h2 class="text-center">Rent Checkout</h2>
  <div class="row">
    <!-- Product Image -->
    <div class="col-md-6 text-center">
      <?php 
      // Display image based on whether it is a URL or local file
      if (!empty($product_image)) {
          if (filter_var($product_image, FILTER_VALIDATE_URL)) {
              echo '<img src="' . htmlspecialchars($product_image) . '" class="product-image" alt="' . htmlspecialchars($product_name) . '">';
          } else {
              if (file_exists($product_image)) {
                  echo '<img src="' . htmlspecialchars($product_image) . '" class="product-image" alt="' . htmlspecialchars($product_name) . '">';
              } else {
                  echo '<img src="default.jpg" class="product-image" alt="No image available">';
              }
          }
      } else {
          echo '<img src="default.jpg" class="product-image" alt="No image available">';
      }
      ?>
    </div>
    <!-- Product Details & Order Form -->
    <div class="col-md-6">
      <h3><?php echo htmlspecialchars($product_name); ?></h3>
      <p><strong>Size:</strong> <?php echo htmlspecialchars($product_size); ?></p>
      <p><strong>Price per day:</strong> $<?php echo htmlspecialchars($product_price); ?></p>
      
      <form action="place_rent_order.php" method="post">
          <!-- Hidden product details -->
          <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
          <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>">
          <input type="hidden" name="product_size" value="<?php echo htmlspecialchars($product_size); ?>">
          <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($product_price); ?>">
          
          <!-- Rental Duration (Quantity as number of days) -->
          <label for="quantity">Number of Days:</label>
          <input type="number" name="quantity" id="quantity" class="form-control mb-2 quantity-input" min="1" value="1" onchange="updateTotalPrice()">
          
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
          
          <button type="submit" class="btn btn-payment w-100">Place Rent Order</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
