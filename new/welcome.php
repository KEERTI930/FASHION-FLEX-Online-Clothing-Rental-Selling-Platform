<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | E-Commerce</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-image: url('images/store.jpg');
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
        .btn-custom {
            width: 100%;
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
        }
        .navbar {
            width: 100%;
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
                    <li class="nav-item"><a class="nav-link" href="help.html">Help</a></li>
                    <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contactuss.html">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="order_history.php">History</a></li>

                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Welcome to FASHION FLEX</h1>
        <p>Hello, <strong>Guest</strong>!</p> <!-- Generic greeting -->
        <button class="btn btn-primary btn-custom" onclick="selectOption('buy')">Buy Clothes</button>
        <button class="btn btn-success btn-custom" onclick="selectOption('rent')">Rent Clothes</button>
        <button class="btn btn-primary btn-custom" onclick="selectOption('admin')">Admin</button>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function selectOption(option) {
            if (option === 'buy') {
                window.location.href = 'buy_cloths.php';
            } else if(option === 'rent'){
                window.location.href = 'rent.php';
            }else{
                window.location.href = 'admin_login.php';   
            }
        }

        function logout() {
            window.location.href = 'logout.php';
        }
    </script>

</body>
</html>