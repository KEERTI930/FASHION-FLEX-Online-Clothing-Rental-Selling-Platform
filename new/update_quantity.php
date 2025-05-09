<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clothies_rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

if (isset($_POST['id']) && isset($_POST['change'])) {
    $id = intval($_POST['id']);
    $change = intval($_POST['change']);

    // Fetch current quantity
    $sql = "SELECT quantity, product_price FROM cart WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_quantity = max(1, $row['quantity'] + $change);
        $new_subtotal = $row['product_price'] * $new_quantity;

        // Update quantity in the database
        $update_sql = "UPDATE cart SET quantity = $new_quantity WHERE id = $id";
        if ($conn->query($update_sql) === TRUE) {
            // Recalculate total cart value
            $total_sql = "SELECT SUM(product_price * quantity) AS total FROM cart";
            $total_result = $conn->query($total_sql);
            $total_row = $total_result->fetch_assoc();
            $new_total = $total_row['total'];

            echo json_encode(["status" => "success", "new_quantity" => $new_quantity, "new_subtotal" => $new_subtotal, "new_total" => $new_total]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update quantity"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Item not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

$conn->close();
?>
