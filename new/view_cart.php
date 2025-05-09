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

// Fetch cart items
$sql = "SELECT * FROM cart";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">FASHION FLEX</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
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
        <a href="buy_cloths.php" class="btn btn-outline-primary">
            ← Back 
        </a>
    </div>

<div class="container">
    <h2 class="my-4">Your Cart</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Size</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="cart-body">
            <?php
            $total = 0;
            while ($row = $result->fetch_assoc()) {
                $subtotal = $row['product_price'] * $row['quantity'];
                $total += $subtotal;
                echo "<tr id='product-{$row['id']}'>
                        <td>{$row['product_name']}</td>
                        <td>{$row['product_size']}</td>
                        <td>\${$row['product_price']}</td>
                        <td>
                            <button class='btn btn-sm btn-secondary' onclick='updateQuantity({$row['id']}, -1)'>-</button>
                            <span id='quantity-{$row['id']}'>{$row['quantity']}</span>
                            <button class='btn btn-sm btn-secondary' onclick='updateQuantity({$row['id']}, 1)'>+</button>
                        </td>
                        <td class='subtotal' id='subtotal-{$row['id']}'>\${$subtotal}</td>
                        <td>
                            <button class='btn btn-danger btn-sm' onclick='removeFromCart({$row['id']})'>Remove</button>
                        </td>
                      </tr>";
            }
            ?>
            <tr>
                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                <td id="cart-total"><strong>$<?php echo $total; ?></strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <a href="buy_cloths.php" class="btn btn-primary">Continue Shopping</a>
    <a href="checkout.php" class="btn btn-success">Checkout</a>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function updateQuantity(productId, change) {
    $.ajax({
        url: "update_quantity.php",
        type: "POST",
        data: { id: productId, change: change },
        dataType: "json",
        success: function(response) {
            if (response.status === "success") {
                $("#quantity-" + productId).text(response.new_quantity);
                $("#subtotal-" + productId).text("$" + response.new_subtotal.toFixed(2));
                $("#cart-total strong").text("$" + response.new_total.toFixed(2));
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function() {
            alert("An error occurred. Please try again.");
        }
    });
}

function removeFromCart(productId) {
    if (confirm("Are you sure you want to remove this product from the cart?")) {
        $.ajax({
            url: "remove_from_cart.php",
            type: "POST",
            data: { id: productId },
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    $("#product-" + productId).remove();
                    $("#cart-total strong").text("$" + response.new_total.toFixed(2));
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("An error occurred. Please try again.");
            }
        });
    }
}
</script>
</body>
</html>
<?php $conn->close(); ?>
