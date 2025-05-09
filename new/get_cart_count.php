<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["count" => 0]));
}

$conn = new mysqli("localhost", "root", "", "clothies_rental");
if ($conn->connect_error) {
    die(json_encode(["count" => 0]));
}

$query = "SELECT SUM(quantity) as count FROM cart_rent WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    "count" => $result['count'] ?? 0
]);

$conn->close();
?>