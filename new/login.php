<?php
// login.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = 'localhost'; // Replace with your database host
$dbname = 'clothies_rental'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input (basic validation)
    if (empty($email) || empty($password)) {
        die("Please fill in all fields.");
    }

    // Prepare SQL query to check if the user exists
    $sql = "SELECT id, name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("s", $email);

        // Execute the query
        $stmt->execute();

        // Store the result
        $stmt->store_result();

        // Check if a user with the email exists
        if ($stmt->num_rows > 0) {
            // Bind the result to variables
            $stmt->bind_result($id, $name, $hashedPassword);

            // Fetch the result
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                // Password is correct, start a session
                session_start();
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;

                // Redirect to the welcome page
                header("Location: welcome.php");
                exit();
            } else {
                // Password is incorrect
                header("Location: redirect.php");

               // die("Invalid email or password.");
            }
        } else {
            // User does not exist
            die("Invalid email or password.");
        }

        // Close the statement
        $stmt->close();
    } else {
        die("Error preparing the SQL statement: " . $conn->error);
    }
} else {
    // If the form is not submitted, redirect to the login page
    header("Location: index.html");
    exit();
}

// Close the database connection
$conn->close();

?>

