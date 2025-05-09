<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clothies_rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get rental cart items
$cart_query = "SELECT cr.*, i.description, i.owner_name, i.contact_info
               FROM cart_rent cr
               JOIN items_for_rent i ON cr.product_id = i.id
               WHERE cr.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.1; // Example 10% tax
$total = $subtotal + $tax;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rental Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cart-item-image {
            width: 100px;
            height: auto;
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
        <a href="buy_for_rent.php" class="btn btn-outline-primary">
            ← Back 
        </a>
    </div>


    <div class="container mt-5">
        <h1 class="mb-4">Your Rental Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">Your rental cart is empty</div>
            <a href="rent.php" class="btn btn-primary">Browse Rentals</a>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-dark">
                        <tr>
                            <th>Item</th>
                            <th>Price/Day</th>
                            <th>Quantity</th>
                            <th>Days</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                     class="cart-item-image me-3" 
                                     alt="<?= htmlspecialchars($item['product_name']) ?>">
                                <?= htmlspecialchars($item['product_name']) ?>
                                <div class="text-muted small">
                                    Size: <?= htmlspecialchars($item['size']) ?>,
                                    Owner: <?= htmlspecialchars($item['owner_name']) ?>
                                </div>
                            </td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input type="number" min="1" value="<?= $item['quantity'] ?>" 
                                       class="form-control quantity-input" 
                                       data-item-id="<?= $item['id'] ?>"
                                       style="width: 70px;">
                            </td>
                            <td>
                                <input type="number" min="1" value="1" 
                                       class="form-control days-input" 
                                       style="width: 70px;">
                            </td>
                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            <td>
                                <button class="btn btn-danger btn-sm remove-item" 
                                        data-item-id="<?= $item['id'] ?>">
                                    Remove
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Order Summary</h5>
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span>$<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tax (10%):</span>
                                <span>$<?= number_format($tax, 2) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span>$<?= number_format($total, 2) ?></span>
                            </div>
                            <button class="btn btn-primary w-100 mt-3" id="checkout-btn">
                                Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update quantity
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const itemId = this.dataset.itemId;
                const newQuantity = this.value;
                
                fetch('update_rent_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update&id=${itemId}&quantity=${newQuantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            });
        });

        // Remove item
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Remove this item from your rental cart?')) {
                    const itemId = this.dataset.itemId;
                    
                    fetch('update_rent_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=remove&id=${itemId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }
            });
        });

        // Checkout
        document.getElementById('checkout-btn').addEventListener('click', function() {
            // Calculate total days
            let totalDays = 1;
            const daysInputs = document.querySelectorAll('.days-input');
            if (daysInputs.length > 0) {
                totalDays = Math.max(...Array.from(daysInputs).map(input => parseInt(input.value)));
            }
            
            window.location.href = `checkout_rent.php?days=${totalDays}`;
        });
    </script>
</body>
</html>