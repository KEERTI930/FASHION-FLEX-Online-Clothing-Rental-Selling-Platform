<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clothies_rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "User not logged in."]));
}

$user_id = $_SESSION['user_id'];
$paymentMethod = $_POST['paymentMethod'];

$sql = "INSERT INTO orders (user_id, payment_method, order_date) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $paymentMethod);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

$sql = "DELETE FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

$conn->close();
echo json_encode(["status" => "success"]);
?>
