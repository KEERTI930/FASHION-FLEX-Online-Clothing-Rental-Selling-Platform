<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "clothies_rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin credentials
$username = "admin"; // Set your desired username
$password = "password"; // Set your desired password

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert admin into the database
$sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ss", $username, $hashedPassword);

if ($stmt->execute()) {
    echo "Admin user created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>