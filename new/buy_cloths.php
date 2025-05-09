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

// Get filters from URL
$category = $_GET['category'] ?? null;
$subcategory = $_GET['subcategory'] ?? "";
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 1000;

$sizes = isset($_GET['sizes']) && is_string($_GET['sizes']) ? explode(',', $_GET['sizes']) : [];

// Fetch products based on filters
$products = [];
if ($category) {
    $sql = "SELECT clothes.* FROM clothes
            JOIN subcategories ON clothes.subcategory_id = subcategories.id
            JOIN categories ON subcategories.category_id = categories.id
            WHERE categories.name = '$category'";

    // Apply Subcategory Filter
    if (!empty($subcategory)) {
        $sql .= " AND subcategories.name = '$subcategory'";
    }

    // Apply Price Filter
    $sql .= " AND clothes.price BETWEEN $min_price AND $max_price";

    // Apply Size Filter
    if (!empty($sizes)) {
        $size_conditions = array_map(function ($size) {
            return "clothes.size = '$size'";
        }, $sizes);
        $sql .= " AND (" . implode(" OR ", $size_conditions) . ")";
    }

    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch subcategories for the selected category
$subcategories = [];
if ($category) {
    $sql = "SELECT subcategories.name FROM subcategories
            JOIN categories ON subcategories.category_id = categories.id
            WHERE categories.name = '$category'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row['name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Clothes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            background-image: url('https://via.placeholder.com/1920x1080.png?text=Shopping+Background');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        h2.text-center {
            margin: 25%;
            background-position: center;
            text-align: center;
            background-size: auto;
            font-weight: bold;
            width: 50%;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: rgb(206, 209, 212);
            padding: 10px;
            border-radius: 10px;
        }

        h5 {
            margin-left: 25%;
            background-position: center;
            text-align: center;
            background-size: auto;
            font-weight: bold;
            width: 45%;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: rgb(110, 177, 244);
            padding: 10px;
            border-radius: 10px;
        }

        h4.text-dark {
            font-weight: 600;
            color: #333;
            background-color: rgb(226, 238, 250);
            padding: 10px;
            border-radius: 10px;
        }

        .border-bottom {
            border-bottom: 3px solid rgb(191, 194, 197); /* Blue underline */
        }

        .navbar {
            width: 100%;
        }

        /* Category Cards */
        .category-card img {
            height: 250px; /* Medium-sized images */
            object-fit: cover; /* Ensures the image covers the area without distortion */
            border-radius: 10px 10px 0 0; /* Rounded corners at the top */
        }

        .category-card {
            cursor: pointer;
            transition: transform 0.3s;
            margin-bottom: 20px; /* Adds space between cards */
            border: 1px solid #ddd; /* Adds a border to the card */
            border-radius: 10px; /* Rounded corners for the card */
            overflow: hidden; /* Ensures the image doesn't overflow the card */
        }

        .category-card:hover {
            transform: scale(1.05); /* Slight zoom effect on hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Adds shadow on hover */
        }

        .category-card .card-body {
            padding: 15px; /* Adds padding inside the card */
            text-align: center; /* Centers the text */
        }

        .category-card .card-title {
            font-size: 1.25rem; /* Adjusts the title font size */
            font-weight: bold; /* Makes the title bold */
            margin: 0; /* Removes default margin */
        }

        /* Product Cards */
        .product-card img {
            height: 500px; /* Fixed height for product images */
            object-fit: cover; /* Ensures the image covers the area without distortion */
            border-radius: 10px 10px 0 0; /* Rounded corners at the top */
        }

        .product-card {
            cursor: pointer;
            transition: transform 0.3s;
            margin-bottom: 20px; /* Adds space between cards */
            border: 1px solid #ddd; /* Adds a border to the card */
            border-radius: 10px; /* Rounded corners for the card */
            overflow: hidden; /* Ensures the image doesn't overflow the card */
        }

        .product-card:hover {
            transform: scale(1.05); /* Slight zoom effect on hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Adds shadow on hover */
        }

        .product-card .card-body {
            padding: 15px; /* Adds padding inside the card */
            text-align: center; /* Centers the text */
        }

        .product-card .card-title {
            font-size: 1.25rem; /* Adjusts the title font size */
            font-weight: bold; /* Makes the title bold */
            margin: 0; /* Removes default margin */
        }

        /* Filter Section */
        .filter-section .form-control {
            margin-bottom: 10px; /* Adds space between form elements */
        }

        .filter-section .btn {
            width: 100%; /* Full-width button */
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .category-card img, .product-card img {
                height: 200px; /* Smaller images for mobile devices */
            }

            h2.text-center, h5 {
                margin: 10px; /* Adjust margins for smaller screens */
                width: 90%; /* Full width for smaller screens */
            }
        }
    </style>
</head>
<body>
<div class="container">
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
                    <li class="nav-item"><a class="nav-link" href="view_cart.php">View Cart</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <h2 class="text-center my-4">Buy Clothes</h2>

    <?php if (!$category) { ?>
        <!-- Category Section -->
        <div class="text-center mb-4">
            <h4 class="text-dark border-bottom pb-2 d-inline-block">Select a Category</h4>
        </div>
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card category-card" onclick="redirectToCategory('Ladies')">
                    <img src="images/ladies.jpg" class="card-img-top" alt="Ladies">
                    <div class="card-body">
                        <h4 class="card-title">Ladies</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card category-card" onclick="redirectToCategory('Men')">
                    <img src="images/mens.png" class="card-img-top" alt="Men">
                    <div class="card-body">
                        <h4 class="card-title">Men</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card category-card" onclick="redirectToCategory('Kids')">
                    <img src="images/kids.jpg" class="card-img-top" alt="Kids">
                    <div class="card-body">
                        <h4 class="card-title">Kids</h4>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <!-- Filter Section -->
        <div class="card mb-3 p-3">
            <form action="buy_cloths.php" method="GET" class="row">
                <input type="hidden" name="category" value="<?php echo $category; ?>">

                <!-- Subcategory Filter -->
                <div class="col-md-3">
                    <label><strong>Subcategory:</strong></label>
                    <select name="subcategory" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($subcategories as $sub) { ?>
                            <option value="<?php echo $sub; ?>" <?php echo ($subcategory == $sub) ? 'selected' : ''; ?>><?php echo $sub; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Price Range Filter -->
                <div class="col-md-3">
                    <label><strong>Price Range:</strong></label>
                    <div class="d-flex">
                        <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo $min_price; ?>">
                        <input type="number" name="max_price" class="form-control ms-2" placeholder="Max" value="<?php echo $max_price; ?>">
                    </div>
                </div>

                <!-- Size Filter -->
                <div class="col-md-3">
                    <label><strong>Size:</strong></label><br>
                    <input type="checkbox" name="sizes[]" value="S"> S
                    <input type="checkbox" name="sizes[]" value="M"> M
                    <input type="checkbox" name="sizes[]" value="L"> L
                    <input type="checkbox" name="sizes[]" value="XL"> XL
                </div>

                <!-- Filter Button -->
                <div class="col-md-3 mt-4">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Product Display Section -->
        <div class="text-center mb-3">
            <h5>All Products in <?php echo $category; ?></h5>
            <button class="btn btn-secondary" onclick="window.location.href='buy_cloths.php'">Back to Categories</button>
        </div>
        <div class="row">
            <?php if (count($products) > 0) {
                foreach ($products as $product) { ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card">
                          <!--  <img src="https://via.placeholder.com/400x300.png?text=Product+Image" class="card-img-top" alt="Product Image">-->
                            <img src="images/<?php echo $product['image']; ?>" class="card-img-top">






                            <div class="card-body">
                                <h4 class="card-title"><?php echo $product['name']; ?></h4>
                                <p class="card-text"><strong>Size:</strong> <?php echo $product['size']; ?></p>
                                <p class="card-text"><strong>Price:</strong> $<?php echo $product['price']; ?></p>
                                <p class="card-text"><?php echo $product['description']; ?></p>
                                <form action="buy_now.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="product_name" value="<?php echo $product['name']; ?>">
                                    <input type="hidden" name="product_size" value="<?php echo $product['size']; ?>">
                                    <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                                    <input type="hidden" name="product_description" value="<?php echo $product['description']; ?>">
                                    <input type="hidden" name="product_image" value="<?php echo $product['image']; ?>">
                                    <button type="submit" class="btn btn-primary">Buy Now</button>
                                </form>
                                <form action="add_to_cart.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="product_name" value="<?php echo $product['name']; ?>">
                                    <input type="hidden" name="product_size" value="<?php echo $product['size']; ?>">
                                    <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                                    <input type="hidden" name="product_description" value="<?php echo $product['description']; ?>">
                                    <input type="hidden" name="product_image" value="<?php echo $product['image']; ?>">
                                   <button type="submit" class="btn btn-success">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php }
            } else {
                echo "<p class='text-center'><h4>No products available with selected filters.<h4></p>";
            } ?>
        </div>
    <?php } ?>
</div>

<script>
function redirectToCategory(category) {
    window.location.href = `buy_cloths.php?category=${category}`;
}
</script>

<!-- Add this script to the end of your <body> tag -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Handle "Add to Cart" form submission
    $("form[action='add_to_cart.php']").on("submit", function(e) {
        e.preventDefault(); // Prevent default form submission

        var form = $(this);
        var button = form.find("button[type='submit']");

        // Disable the button to prevent multiple submissions
        button.prop("disabled", true);

        // Send AJAX request
        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    // Update button text and disable it
                    button.text("Product Added");
                    button.removeClass("btn-success").addClass("btn-secondary");
                } else {
                    alert("Error: " + response.message);
                    button.prop("disabled", false); // Re-enable the button on error
                }
            },
            error: function() {
                alert("An error occurred. Please try again.");
                button.prop("disabled", false); // Re-enable the button on error
            }
        });
    });
});
</script>
</body>

</html>

<?php $conn->close(); ?>