<?php
session_start();
require 'db_config.php'; // Include the database connection

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: add_rent_cart.php"); 
    exit();
}

// Check if item_id is set in URL
if (!isset($_GET['item_id'])) {
    die("Error: Item ID is missing in the URL.");
}

$user_id = $_SESSION['user_id'];
$item_id = $_GET['item_id'];

// Fetch item details
$query = "SELECT * FROM items_for_rent WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Item not found!");
}

$item = $result->fetch_assoc();

// Calculate total price
$totalPrice = 0;
foreach ($_SESSION['cart'] as $cart_item) {
    $totalPrice += $cart_item['price'];
}

// Initialize date variables
$days = 0;
$issuedDate = '';
$returnDate = '';

// Calculate total rent price
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $issuedDate = $_POST['issuedDate'];
    $returnDate = $_POST['returnDate'];

    if (!empty($issuedDate) && !empty($returnDate)) {
        $start = strtotime($issuedDate);
        $end = strtotime($returnDate);
        $days = max(1, ($end - $start) / (60 * 60 * 24)); // Ensure at least 1 day rental
        $totalPrice *= $days;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            font-weight: bold;
            border-radius: 8px;
            padding: 10px;
        }
        .payment-method {
            margin-bottom: 15px;
        }
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
    </style>
</head>
<body> 

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
<hr>

<!-- Back Button -->
<div class="container text-start back-btn">
    <a href="add_rent_cart.php" class="btn btn-outline-primary">← Back</a>
</div>

<div class="payment-container">
    <h1 class="text-center mb-4">Payment</h1>

    <form action="" method="POST">
        <div class="mb-3">

        <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
        
            <label for="issuedDate" class="form-label">Issued Date</label>
            <input type="date" id="issuedDate" name="issuedDate" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="returnDate" class="form-label">Return Date</label>
            <input type="date" id="returnDate" name="returnDate" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Calculate Total</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($issuedDate) && !empty($returnDate)) : ?>
        <p class="text-center mt-3"><strong>Total Days:</strong> <?= $days ?> days</p>
        <p class="text-center"><strong>Total Amount:</strong> $<?= number_format($totalPrice, 2) ?></p>

        <form action="process_payment.php" method="POST">
            <input type="hidden" name="issuedDate" value="<?= $issuedDate ?>">
            <input type="hidden" name="returnDate" value="<?= $returnDate ?>">
            <input type="hidden" name="totalPrice" value="<?= $totalPrice ?>">
            <input type="hidden" name="days" value="<?= $days ?>">

            <!-- Payment Method Selection -->
            <div class="payment-method">
                <label><input type="radio" name="paymentMethod" value="creditCard" required> Credit Card</label>
            </div>
            <div class="payment-method">
                <label><input type="radio" name="paymentMethod" value="debitCard"> Debit Card</label>
            </div>
            <div class="payment-method">
                <label><input type="radio" name="paymentMethod" value="gpay"> GPay</label>
            </div>
            <div class="payment-method">
                <label><input type="radio" name="paymentMethod" value="cashOnDelivery"> Cash on Delivery</label>
            </div>

            <!-- Payment Details (Credit/Debit Card) -->
            <div id="cardDetails" class="mt-3">
                <div class="mb-3">
                    <label for="cardNumber" class="form-label">Card Number</label>
                    <input type="text" id="cardNumber" name="cardNumber" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="expiryDate" class="form-label">Expiry Date</label>
                    <input type="text" id="expiryDate" name="expiryDate" class="form-control" placeholder="MM/YY">
                </div>
                <div class="mb-3">
                    <label for="cvv" class="form-label">CVV</label>
                    <input type="text" id="cvv" name="cvv" class="form-control">
                </div>
            </div>

            <!-- GPay Details -->
            <div id="gpayDetails" class="mt-3" style="display: none;">
                <p>Please use your GPay app to complete the payment.</p>
                <p>Scan the QR code below or use the UPI ID: <strong>your-upi-id@example</strong></p>
            </div>

            <!-- Cash on Delivery Message -->
            <div id="cashOnDeliveryDetails" class="mt-3" style="display: none;">
                <p>You have selected Cash on Delivery. Payment will be collected at the time of delivery.</p>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-success btn-custom w-100">Proceed to Pay</button>
        </form>
    <?php endif; ?>
</div>

<script>
// Show/hide payment details based on selected payment method
const paymentMethods = document.querySelectorAll('input[name="paymentMethod"]');
        const cardDetails = document.getElementById('cardDetails');
        const gpayDetails = document.getElementById('gpayDetails');
        const cashOnDeliveryDetails = document.getElementById('cashOnDeliveryDetails');

        paymentMethods.forEach(method => {
            method.addEventListener('change', (e) => {
                const selectedMethod = e.target.value;

                // Hide all details initially
                cardDetails.style.display = 'none';
                gpayDetails.style.display = 'none';
                cashOnDeliveryDetails.style.display = 'none';

                // Show details based on selected method
                if (selectedMethod === 'creditCard' || selectedMethod === 'debitCard') {
                    cardDetails.style.display = 'block';
                } else if (selectedMethod === 'gpay') {
                    gpayDetails.style.display = 'block';
                } else if (selectedMethod === 'cashOnDelivery') {
                    cashOnDeliveryDetails.style.display = 'block';
                }
            });
        });
</script>
</body>
</html>
