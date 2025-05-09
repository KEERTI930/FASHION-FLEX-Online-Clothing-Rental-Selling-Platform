<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'clothies_rental';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Pagination logic
$itemsPerPage = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Search and filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$size = $_GET['size'] ?? '';

// Fetch total number of items
$totalItemsQuery = $conn->prepare("SELECT COUNT(*) FROM items_for_rent WHERE item_name LIKE :search AND (:category = '' OR category = :category) AND (:size = '' OR size = :size)");
$totalItemsQuery->execute([':search' => "%$search%", ':category' => $category, ':size' => $size]);
$totalItems = (int)$totalItemsQuery->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch items for current page
$query = $conn->prepare("SELECT id, item_name, description, price, image_path, size, category, owner_name FROM items_for_rent WHERE item_name LIKE :search AND (:category = '' OR category = :category) AND (:size = '' OR size = :size) ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$query->bindValue(':search', "%$search%");
$query->bindValue(':category', $category);
$query->bindValue(':size', $size);
$query->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$items = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Clothes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function rentItem(itemId) {
        window.location.href = `rent_payment.php?id=${itemId}`;
    }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">FASHION FLEX</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="welcome.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="buy_for_rent.php">Rent Clothes</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <?php if (empty($items)): ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-info">No clothes available for rent.</div>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['item_name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['item_name']) ?></h5>
                                <p class="text-muted">Size: <?= htmlspecialchars($item['size']) ?></p>
                                <p class="card-text">$<?= htmlspecialchars($item['price']) ?>/day</p>
                                <button class="btn btn-primary w-100" onclick="rentItem(<?= $item['id'] ?>)">Rent Now</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
