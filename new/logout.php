<?php
session_start(); // Start the session

// Destroy the session to log the user out
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }
        .message {
            text-align: center;
            font-size: 24px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="message">
        <p>Thank you! Visit us again.</p>
    </div>

    <script>
        // Redirect to index.html after 3 seconds
        setTimeout(function() {
            window.location.href = "index.html";
        }, 3000); // 3000 milliseconds = 3 seconds
    </script>
</body>
</html>