<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clothies_rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$showForm = true; // Variable to control form visibility
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback = trim($_POST["feedback"]);
    if (!empty($feedback)) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, feedback) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION["user_id"], $feedback);
        if ($stmt->execute()) {
            // Feedback submitted successfully
            $message = "Thank you for your feedback!Visit again";
            $showForm = false; // Hide the form after submission
            // Redirect to welcome.php after 3 seconds
            header("Refresh: 3; url=welcome.php");
        } else {
            $message = "Error submitting feedback. Please try again.";
        }
        $stmt->close();
    } else {
        $message = "Feedback cannot be empty.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/clothes.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin-top: 100px;
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
                    <li class="nav-item"><a class="nav-link" href="welcome.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                    <li class="nav-item"><a class="nav-link active" href="feedback.php">Feedback</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Feedback Form -->
    <div class="container">
        <h2>Submit Your Feedback</h2>
        <?php if (isset($message)) { echo "<p class='text-success'>$message</p>"; } ?>
        
        <?php if ($showForm) { ?>
            <form method="POST" action="feedback.php">
                <div class="mb-3">
                    <textarea name="feedback" class="form-control" rows="5" placeholder="Enter your feedback here..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
        <?php } ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>