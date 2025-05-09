<?php
// Database connection
$host = 'localhost';
$dbname = 'clothies_rental'; // Ensure this matches your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'];
    $size = $_POST['size'];
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $owner_name = $_POST['owner_name'];
    $contact_info = $_POST['contact_info'];

    // Handle file upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/'; // Directory to store uploaded files
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
        }
        $file_name = basename($_FILES['product_image']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $file_path)) {
            // File uploaded successfully
        } else {
            die("Failed to upload file.");
        }
    } else {
        die("No file uploaded or file upload error.");
    }

   // Prepare SQL query
$stmt = $conn->prepare("INSERT INTO items_for_rent (item_name, size, category, subcategory, description, price, owner_name, contact_info, image_path) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Check if the statement was prepared successfully
if (!$stmt) {
die("Error preparing statement: " . implode(" ", $conn->errorInfo()));
}

// Execute the query with form data
$success = $stmt->execute([$item_name, $size, $category, $subcategory, $description, $price, $owner_name, $contact_info, $file_path]);

if ($success) {
echo "<p style='color: green; font-weight: bold;'>Item added for rent successfully!</p>";
} else {
echo "<p style='color: red;'>Failed to add item: " . implode(" ", $stmt->errorInfo()) . "</p>";
}

    
    echo "<p>Item added for rent successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell for Rent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(203, 250, 137);
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            justify-content: center;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .rent-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
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
        <a href="rent.php" class="btn btn-outline-primary">
            ← Back to Rent Page
        </a>
    </div>


    <div class="rent-container">


        <h2 class="mb-4">Sell for Rent</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <!-- Product Name -->
            <input type="text" name="item_name" class="form-control mb-3" placeholder="Product Name" required>

            <!-- Size Dropdown -->
            <select name="size" class="form-select mb-3" required>
                <option value="" disabled selected>Select Size</option>
                <option value="S">Small (S)</option>
                <option value="M">Medium (M)</option>
                <option value="L">Large (L)</option>
                <option value="XL">Extra Large (XL)</option>
            </select>

            <!-- Category Dropdown -->
            <select name="category" class="form-select mb-3" required>
                <option value="" disabled selected>Select Category</option>
                <option value="Men">Men</option>
                <option value="Women">Women</option>
                <option value="Kids">Kids</option>
            </select>

            <!-- Subcategory Dropdown -->
            <select name="subcategory" class="form-select mb-3" required>
                <option value="" disabled selected>Select Subcategory</option>
                <option value="Casual">Casual</option>
                <option value="Formal">Formal</option>
                <option value="Party Wear">Party Wear</option>
                <option value="Sports">Sports</option>
            </select>

            <!-- Description -->
            <textarea name="description" class="form-control mb-3" placeholder="Description" required></textarea>

            <!-- Price -->
            <input type="number" name="price" class="form-control mb-3" placeholder="Price" step="0.01" required>

            <!-- Owner Name -->
            <input type="text" name="owner_name" class="form-control mb-3" placeholder="Your Name" required>

             <!-- Contact Info -->
             <input type="text" name="contact_info" class="form-control mb-3" placeholder="Contact Info" required>


            <!-- Product Image Upload -->
            <input type="file" name="product_image" class="form-control mb-3" accept="image/*" required>

           
            <!-- Submit Button -->
            <button type="submit" name="sell_for_rent" class="btn btn-primary btn-custom">Submit</button>
        </form>
    </div>
</body>
</html>