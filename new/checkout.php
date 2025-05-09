<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "clothies_rental"; // Use your existing database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id']; // Get user ID from session

// Fetch cart items for the logged-in user
$sql = "SELECT * FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total
$total = 0;
$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['product_price'] * $row['quantity'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-option { display: none; } /* Hide payment input fields by default */
        .btn-custom {
            width: 100%;
            font-weight: bold;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
        }

    </style>
</head>
<body>
<div class="container">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">FASHION FLEX</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="welcome.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container text-start back-btn">
        <a href="view_cart.php" class="btn btn-outline-primary">
            ← Back
        </a>
    </div>

    <h2 class="my-4">Checkout</h2>

    <!-- Cart Items -->
    <div class="card p-4">
        <h4>Total Amount: $<?php echo number_format($total, 2); ?></h4>

        <!-- Payment Options -->
        <div class="mb-3">
            <label><strong>Select Payment Method:</strong></label>
            <select id="paymentMethod" class="form-control">
                <option value="">Select an option</option>
                <option value="debit">Debit Card</option>
                <option value="credit">Credit Card</option>
                <option value="gpay">GPay</option>
                <option value="cod">Cash on Delivery</option>
            </select>
        </div>

        <!-- Payment Details Fields -->
        <div id="paymentDetails"></div>

        <!-- Place Order Button -->
        <button id="placeOrder" class="btn btn-success w-100" disabled>Place Order</button>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#paymentMethod").change(function() {
        const method = $(this).val();
        let html = "";
        
        if (method === "debit" || method === "credit") {
            html = `<div class="mb-3">
                        <label>Card Number</label>
                        <input type="text" class="form-control" id="cardNumber" placeholder="Enter card number">
                    </div>
                    <div class="mb-3">
                        <label>Expiry Date</label>
                        <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY">
                    </div>
                    <div class="mb-3">
                        <label>CVV</label>
                        <input type="text" class="form-control" id="cvv" placeholder="Enter CVV">
                    </div>`;
        } else if (method === "gpay") {
            html = `<div class="mb-3">
                        <label>GPay UPI ID</label>
                        <input type="text" class="form-control" id="upiId" placeholder="Enter UPI ID">
                    </div>`;
        } else if (method === "cod") {
            html = `<p>No payment required now. Pay upon delivery.</p>`;
        }

        $("#paymentDetails").html(html);
        $("#placeOrder").prop("disabled", !method);
    });

    $("#placeOrder").click(function() {
        $.post("process_order.php", { user_id: "<?php echo $user_id; ?>", paymentMethod: $("#paymentMethod").val() }, function(response) {
            if (response.status === "success") {
                window.location.href = "order_confirmation.php";
            } else {
                alert("Error: " + response.message);
            }
        }, "json");
    });
});
</script>
</body>
</html>

<?php $conn->close(); ?>
