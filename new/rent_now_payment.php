<?php
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request.");
}

$item_id = $_GET['id'];

// Database connection
$host = 'localhost';
$dbname = 'clothies_rental';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch item details
$query = $conn->prepare("SELECT * FROM items_for_rent WHERE id = :id");
$query->execute([':id' => $item_id]);
$item = $query->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center">Complete Your Rental Payment</h2>
        <div class="card mx-auto mt-4" style="max-width: 500px;">
            <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['item_name']) ?>">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($item['item_name']) ?></h5>
                <p class="text-muted">Category: <?= htmlspecialchars($item['category']) ?> | Size: <?= htmlspecialchars($item['size']) ?></p>
                <h4 class="text-primary">$<?= htmlspecialchars($item['price']) ?>/day</h4>
            </div>
        </div>

        <form action="process_payment.php" method="POST" class="mt-4">
            <input type="hidden" name="item_id" value="<?= $item_id ?>">
            
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Rental Duration (Days)</label>
                <input type="number" name="rental_days" class="form-control" min="1" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select" required>
                    <option value="paypal">PayPal</option>
                    <option value="stripe">Credit/Debit Card (Stripe)</option>
                    <option value="cod">Cash on Delivery</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success w-100">Proceed to Payment</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
