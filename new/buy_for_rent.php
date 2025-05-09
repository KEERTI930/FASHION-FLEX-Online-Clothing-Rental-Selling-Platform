<?php
session_start();
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
$totalItemsQuery->execute([
    ':search' => "%$search%",
    ':category' => $category,
    ':size' => $size
]);
$totalItems = (int)$totalItemsQuery->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch items for current page
$query = $conn->prepare("SELECT id, item_name, description, price, image_path, size, category, subcategory, owner_name, contact_info
                          FROM items_for_rent
                          WHERE item_name LIKE :search AND (:category = '' OR category = :category) AND (:size = '' OR size = :size)
                          ORDER BY created_at DESC
                          LIMIT :limit OFFSET :offset");
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rent Clothes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    #cartCounter { font-size: 0.75rem; vertical-align: top; }
    .alert { position: fixed; top: 20px; right: 20px; z-index: 1000; min-width: 300px; }
    .card { transition: transform 0.3s; margin-bottom: 20px; }
    .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .card-img-top { height: 500px; object-fit: cover; }
    .alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 1000; min-width: 300px; }
    .cart-counter { font-size: 0.75rem; vertical-align: top; }
    .btn-custom {
            width: 100%;
            font-weight: bold;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
        }

  </style>
</head>
<body>
  <!-- Navbar -->
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
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link position-relative" href="rent_cart.php">
              <i class="bi bi-cart3"></i> Rental Cart
              <span id="cartCounter" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
            </a>
          </li>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container text-start back-btn">
        <a href="rent.php" class="btn btn-outline-primary">
            ← Back 
        </a>
    </div>

  <!-- Alert Container -->
  <div id="alertContainer" class="alert-fixed"></div>

  <div class="container py-5">
    <div class="row mb-4">
      <div class="col">
        <h1 class="text-center">Rent Clothes</h1>
        <p class="text-center text-muted">Find the perfect outfit for your occasion</p>
      </div>
    </div>

    <!-- Search and Filter Form -->
    <form method="GET" class="mb-5">
      <div class="row g-3">
        <div class="col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search by name" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
          <select name="category" class="form-select">
            <option value="">All Categories</option>
            <option value="Men" <?= $category === 'Men' ? 'selected' : '' ?>>Men</option>
            <option value="Women" <?= $category === 'Women' ? 'selected' : '' ?>>Women</option>
            <option value="Kids" <?= $category === 'Kids' ? 'selected' : '' ?>>Kids</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="size" class="form-select">
            <option value="">All Sizes</option>
            <option value="S" <?= $size === 'S' ? 'selected' : '' ?>>Small (S)</option>
            <option value="M" <?= $size === 'M' ? 'selected' : '' ?>>Medium (M)</option>
            <option value="L" <?= $size === 'L' ? 'selected' : '' ?>>Large (L)</option>
            <option value="XL" <?= $size === 'XL' ? 'selected' : '' ?>>Extra Large (XL)</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
      </div>
    </form>

    <!-- Items Grid -->
    <div class="row">
      <?php if (empty($items)): ?>
        <div class="col-12 text-center py-5">
          <div class="alert alert-info">No clothes available for rent matching your criteria.</div>
          <a href="buy_for_rent.php" class="btn btn-outline-primary">Reset Filters</a>
        </div>
      <?php else: ?>
        <?php foreach ($items as $item): ?>
          <div class="col-lg-4 col-md-6">
            <div class="card h-100">
              <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['item_name']) ?>">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($item['item_name']) ?></h5>
                <div class="d-flex justify-content-between mb-2">
                  <span class="badge bg-primary"><?= htmlspecialchars($item['category']) ?></span>
                  <span class="badge bg-secondary">Size: <?= htmlspecialchars($item['size']) ?></span>
                </div>
                <p class="card-text text-muted"><?= htmlspecialchars($item['description']) ?></p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="h5">$<?= htmlspecialchars($item['price']) ?>/day</span>
                  <small class="text-muted">Owner: <?= htmlspecialchars($item['owner_name']) ?></small>
                </div>
                <div class="d-grid gap-2">
                  <!-- When Rent Now is clicked, send product details via a hidden form -->
                  <form action="rent_checkout.php" method="post">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']) ?>">
                    <input type="hidden" name="product_name" value="<?= htmlspecialchars($item['item_name']) ?>">
                    <input type="hidden" name="product_size" value="<?= htmlspecialchars($item['size']) ?>">
                    <input type="hidden" name="product_price" value="<?= htmlspecialchars($item['price']) ?>">
                    <input type="hidden" name="product_image" value="<?= htmlspecialchars($item['image_path']) ?>">
                    <button type="submit" class="btn btn-primary">Rent Now</button>
                  </form>
                  <!-- Add to Rental Cart button remains unchanged -->
                  <button class="btn btn-outline-success" onclick="addToCart(<?= $item['id'] ?>, this)">Add to Rental Cart</button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav aria-label="Page navigation" class="mt-5">
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&size=<?= urlencode($size) ?>">Previous</a>
            </li>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&size=<?= urlencode($size) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&size=<?= urlencode($size) ?>">Next</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Function to update cart counter
    function updateCartCounter(count) {
      const counter = document.getElementById('cartCounter');
      if (counter) {
        counter.textContent = count;
        counter.classList.remove('d-none');
        counter.classList.add('animate__animated', 'animate__bounceIn');
        setTimeout(() => {
          counter.classList.remove('animate__animated', 'animate__bounceIn');
        }, 1000);
      }
    }

    // Function to show alert messages
    function showAlert(type, message) {
      const alertContainer = document.getElementById('alertContainer') || document.body;
      const alertId = 'alert-' + Date.now();
      const alertDiv = document.createElement('div');
      alertDiv.id = alertId;
      alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
      alertDiv.style.position = 'fixed';
      alertDiv.style.top = '20px';
      alertDiv.style.right = '20px';
      alertDiv.style.zIndex = '1000';
      alertDiv.innerHTML = `${message} <button type="button" class="btn-close" onclick="document.getElementById('${alertId}').remove()"></button>`;
      alertContainer.appendChild(alertDiv);
      setTimeout(() => { if (document.getElementById(alertId)) { alertDiv.remove(); } }, 5000);
    }

    // Add to cart function
    async function addToCart(itemId, buttonElement) {
      const button = buttonElement;
      const originalText = button.innerHTML;
      try {
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
        button.disabled = true;
        const response = await fetch(`add_rent_cart.php?id=${itemId}`);
        const data = await response.json();
        if (!response.ok) {
          throw new Error(data.message || 'Failed to add to cart');
        }
        if (data.status === 'success') {
          updateCartCounter(data.cart_count);
          showAlert('success', 'Item added to rental cart!');
        } else {
          throw new Error(data.message || 'Unknown error occurred');
        }
      } catch (error) {
        console.error('Add to cart error:', error);
        showAlert('danger', error.message);
      } finally {
        button.innerHTML = originalText;
        button.disabled = false;
      }
    }

    // Load initial cart count when page loads
    document.addEventListener('DOMContentLoaded', function() {
      fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => { if (data.count > 0) { updateCartCounter(data.count); } })
        .catch(error => console.error('Error loading cart count:', error));
    });
  </script>
</body>
</html>
