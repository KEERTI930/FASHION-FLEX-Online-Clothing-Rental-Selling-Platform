<?php
// signup.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = 'localhost';
$dbname = 'clothies_rental';
$username = 'root';
$password = '';

try {
    // Create a connection to the database
    $conn = mysqli_connect($host, $username, $password, $dbname);

    // Check if the connection was successful
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validate input
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("Please fill in all fields.");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            throw new Exception("Email already exists. Please use a different email or <a href='index.html'>login</a>.");
        }
        mysqli_stmt_close($check_stmt);

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert data into the database
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // Bind parameters
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);

            // Execute the query
            if (mysqli_stmt_execute($stmt)) {
                // Success message
                echo "Signup successful! You can now <a href='index.html'>login</a>.";
            } else {
                throw new Exception("Error: " . mysqli_error($conn));
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("Error preparing the SQL statement: " . mysqli_error($conn));
        }
    } else {
        // If the form is not submitted, redirect to the signup page
        header("Location: signup.html");
        exit();
    }
} catch (Exception $e) {
    // Display user-friendly error message
    echo "Error: " . $e->getMessage();
} finally {
    // Close the database connection if it exists
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>