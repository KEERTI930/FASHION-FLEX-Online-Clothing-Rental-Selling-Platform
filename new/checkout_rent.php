<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clothies_rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get rental days from URL
$rental_days = isset($_GET['days']) ? max(1, (int)$_GET['days']) : 1;

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get cart items
$cart_query = "SELECT cr.*, i.description, i.owner_name, i.contact_info
               FROM cart_rent cr
               JOIN items_for_rent i ON cr.product_id = i.id
               WHERE cr.user_id = ?";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $_SESSION['user_id']);
$cart_stmt->execute();
$cart_items = $cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.1 * $rental_days; // 10% tax for rental period
$total = ($subtotal * $rental_days) + $tax;

// Process payment if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    
    // Validate based on payment method
    if ($payment_method === 'credit_card') {
        $card_number = str_replace(' ', '', $_POST['card_number']);
        $expiry = $_POST['expiry'];
        $cvv = $_POST['cvv'];
        
        if (empty($card_number) || empty($expiry) || empty($cvv)) {
            $error = "Please fill in all payment details";
        } elseif (!preg_match('/^\d{16}$/', $card_number)) {
            $error = "Invalid card number";
        } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
            $error = "Invalid CVV";
        }
    }
    
    // If no errors, process the order
    if (!isset($error)) {
        // Create rental order
        $order_query = "INSERT INTO rental_orders 
                        (user_id, total_amount, rental_days, payment_method, payment_status, created_at)
                        VALUES (?, ?, ?, ?, 'completed', NOW())";
        $order_stmt = $conn->prepare($order_query);
        $order_stmt->bind_param("idss", $_SESSION['user_id'], $total, $rental_days, $payment_method);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        
        // Add order items
        foreach ($cart_items as $item) {
            $order_item_query = "INSERT INTO rental_order_items
                                (order_id, product_id, product_name, price, quantity, 
                                 size, category, image_path)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $order_item_stmt = $conn->prepare($order_item_query);
            $order_item_stmt->bind_param(
                "iisdssss",
                $order_id,
                $item['product_id'],
                $item['product_name'],
                $item['price'],
                $item['quantity'],
                $item['size'],
                $item['category'],
                $item['image_path']
            );
            $order_item_stmt->execute();
            
            // Mark items as rented (update status)
            $update_query = "UPDATE items_for_rent SET status = 'rented' WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $item['product_id']);
            $update_stmt->execute();
        }
        
        // Clear cart
        $clear_cart = "DELETE FROM cart_rent WHERE user_id = ?";
        $clear_stmt = $conn->prepare($clear_cart);
        $clear_stmt->bind_param("i", $_SESSION['user_id']);
        $clear_stmt->execute();
        
        // Redirect to confirmation
        header("Location: rental_confirmation.php?order_id=$order_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img {
            width: 80px;
            height: auto;
        }
        .summary-card {
            position: sticky;
            top: 20px;
        }
        .payment-card {
            max-width: 600px;
            margin: 0 auto;
        }
        .payment-method {
            cursor: pointer;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #0d6efd;
        }
        .payment-method.active {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .payment-details {
            display: none;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-top: 10px;
        }
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
        <a href="rent_cart.php" class="btn btn-outline-primary">
            ← Back
        </a>
    </div>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="mb-4">Checkout</h1>
                
                <!-- Display errors -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <!-- Rental Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Rental Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                         class="card-img rounded" 
                                         alt="<?= htmlspecialchars($item['product_name']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <h6><?= htmlspecialchars($item['product_name']) ?></h6>
                                    <small class="text-muted">
                                        Size: <?= htmlspecialchars($item['size']) ?> | 
                                        Qty: <?= $item['quantity'] ?>
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <strong>$<?= number_format($item['price'] * $rental_days * $item['quantity'], 2) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Payment Form -->
                <div class="card payment-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="payment-form">
                            <!-- Payment Method Selection -->
                            <div class="mb-4">
                                <div class="form-check payment-method active" onclick="selectPaymentMethod('credit_card')">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="credit-card" value="credit_card" checked>
                                    <label class="form-check-label fw-bold" for="credit-card">
                                        Credit/Debit Card
                                    </label>
                                    <div class="payment-details" id="credit-card-details">
                                        <div class="mb-3 mt-3">
                                            <label for="card-number" class="form-label">Card Number</label>
                                            <input type="text" class="form-control" id="card-number" 
                                                   name="card_number" placeholder="1234 5678 9012 3456">
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="expiry" class="form-label">Expiry Date</label>
                                                <input type="text" class="form-control" id="expiry" 
                                                       name="expiry" placeholder="MM/YY">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="cvv" class="form-label">CVV</label>
                                                <input type="text" class="form-control" id="cvv" 
                                                       name="cvv" placeholder="123">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name on Card</label>
                                            <input type="text" class="form-control" id="name" 
                                                   name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check payment-method" onclick="selectPaymentMethod('cod')">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cod" value="cod">
                                    <label class="form-check-label fw-bold" for="cod">
                                        Cash on Delivery
                                    </label>
                                    <div class="payment-details" id="cod-details">
                                        <div class="alert alert-info mt-3">
                                            <i class="bi bi-info-circle"></i> Pay when you receive the items. 
                                            An additional $5 delivery charge may apply.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Complete Rental
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card summary-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Rental Period:</span>
                            <span><?= $rental_days ?> day<?= $rental_days > 1 ? 's' : '' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?= number_format($subtotal * $rental_days, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <span>$<?= number_format($tax, 2) ?></span>
                        </div>
                        <div id="delivery-charge" class="d-flex justify-content-between mb-2" style="display: none;">
                            <span>Delivery Charge:</span>
                            <span>$5.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span id="total-amount">$<?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Rental Policy -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Rental Policy</h5>
                    </div>
                    <div class="card-body">
                        <ul class="small text-muted">
                            <li>Rental period starts from delivery date</li>
                            <li>Late returns incur additional charges</li>
                            <li>Damage to items may result in fees</li>
                            <li>Full refund if cancelled before delivery</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select payment method
        function selectPaymentMethod(method) {
            // Update UI
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Update radio button
            document.querySelector(`input[value="${method}"]`).checked = true;
            
            // Show/hide details
            document.querySelectorAll('.payment-details').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelector(`#${method}-details`).style.display = 'block';
            
            // Update total for COD
            if (method === 'cod') {
                document.getElementById('delivery-charge').style.display = 'flex';
                const currentTotal = <?= $total ?>;
                document.getElementById('total-amount').textContent = '$' + (currentTotal + 5).toFixed(2);
            } else {
                document.getElementById('delivery-charge').style.display = 'none';
                document.getElementById('total-amount').textContent = '$<?= number_format($total, 2) ?>';
            }
        }
        
        // Initialize payment method display
        document.querySelector('#credit-card-details').style.display = 'block';
        
        // Format card number input
        document.getElementById('card-number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            e.target.value = value;
        });

        // Format expiry date input
        document.getElementById('expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        // Prevent form submission if invalid
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (paymentMethod === 'credit_card') {
                const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
                const expiry = document.getElementById('expiry').value;
                const cvv = document.getElementById('cvv').value;
                
                if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                    alert('Please enter a valid 16-digit card number');
                    e.preventDefault();
                    return;
                }
                
                if (!/^\d{2}\/\d{2}$/.test(expiry)) {
                    alert('Please enter expiry date in MM/YY format');
                    e.preventDefault();
                    return;
                }
                
                if (cvv.length < 3 || cvv.length > 4 || !/^\d+$/.test(cvv)) {
                    alert('Please enter a valid CVV (3-4 digits)');
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>
</body>
</html>