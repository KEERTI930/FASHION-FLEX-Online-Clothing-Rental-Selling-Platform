<?php
session_start(); // Start the session to access the cart and payment details

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: add_rent_cart.php"); // Redirect to the cart page if empty
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the selected payment method
    $paymentMethod = $_POST['paymentMethod'] ?? '';

    // Process payment based on the selected method
    switch ($paymentMethod) {
        case 'creditCard':
        case 'debitCard':
            // Process card payment
            $cardNumber = $_POST['cardNumber'] ?? '';
            $expiryDate = $_POST['expiryDate'] ?? '';
            $cvv = $_POST['cvv'] ?? '';

            // Validate card details (basic validation)
            if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
                die("Invalid card details. Please go back and try again.");
            }

            // Simulate a successful card payment
            // In a real application, integrate with a payment gateway (e.g., Stripe, PayPal)
            $paymentSuccess = true; // Simulate payment success

            if ($paymentSuccess) {
                // Payment successful
                $message = "<h1 class='success-message'>Payment Successful!</h1>
                            <p>Your card payment has been processed successfully.</p>";
                // Clear the cart after successful payment
                unset($_SESSION['cart']);
            } else {
                // Payment failed
                $message = "<h1 class='error-message'>Payment Failed</h1>
                            <p>There was an issue processing your card payment. Please try again.</p>";
            }
            break;

        case 'gpay':
            // Process GPay payment
            // Simulate a successful GPay payment
            $paymentSuccess = true; // Simulate payment success

            if ($paymentSuccess) {
                // Payment successful
                $message = "<h1 class='success-message'>Payment Successful!</h1>
                            <p>Your GPay payment has been processed successfully.</p>";
                // Clear the cart after successful payment
                unset($_SESSION['cart']);
            } else {
                // Payment failed
                $message = "<h1 class='error-message'>Payment Failed</h1>
                            <p>There was an issue processing your GPay payment. Please try again.</p>";
            }
            break;

        case 'cashOnDelivery':
            // Handle Cash on Delivery
            $message = "<h1 class='success-message'>Order Confirmed!</h1>
                        <p>Your order has been placed successfully. Payment will be collected at the time of delivery.</p>";
            // Clear the cart after confirming the order
            unset($_SESSION['cart']);
            break;

        default:
            // Invalid payment method
            die("Invalid payment method. Please go back and try again.");
    }
} else {
    // Redirect to the payment page if the form was not submitted
    header("Location: payment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .success-message {
            color: #28a745;
            font-weight: bold;
            font-size: 2rem;
            margin-bottom: 20px;
            animation: bounce 1s ease-in-out;
        }
        .error-message {
            color: #dc3545;
            font-weight: bold;
            font-size: 2rem;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            50% { transform: translateX(10px); }
            75% { transform: translateX(-10px); }
            100% { transform: translateX(0); }
        }
        p {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .btn-primary:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Payment status message will be displayed here -->
        <?= $message ?? '' ?>
        <a href="buy_for_rent.php" class="btn btn-primary mt-3">Continue Shopping</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>