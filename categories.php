<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get categories with product counts
$sql = "SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1 
        GROUP BY c.id 
        ORDER BY c.name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - BazarCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="index.php" class="logo">
                        <img src="../bazar.jpg" alt="BazarCart Logo">
                        BazarCart
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-inline-block me-3">
                        <i class="fas fa-phone"></i> +977-98XXXXXXXX
                    </div>
                    <div class="d-inline-block">
                        <i class="fas fa-envelope"></i> info@bazarcart.com
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-shopping-bag"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="categories.php">
                            <i class="fas fa-th-large"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-phone"></i> Contact
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-circle"></i> Profile
                                </a></li>
                                <li><a class="dropdown-item" href="orders.php">
                                    <i class="fas fa-list"></i> My Orders
                                </a></li>
                                <?php if (isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/">
                                        <i class="fas fa-cog"></i> Admin Panel
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <span class="cart-badge"><?php echo getCartCount(); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="py-4 bg-light">
        <div class="container">
            <h1 class="mb-0">Shop by Category</h1>
            <p class="text-muted mb-0">Explore our wide range of product categories</p>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($category = $result->fetch_assoc()): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="category-card h-100">
                                <div class="text-center">
                                    <div class="category-icon mb-3">
                                        <i class="fas fa-<?php echo getCategoryIcon($category['name']); ?>"></i>
                                    </div>
                                    <?php if ($category['image_url']): ?>
                                        <img src="uploads/categories/<?php echo htmlspecialchars($category['image_url']); ?>" 
                                             class="img-fluid mb-3" style="max-height: 150px; object-fit: cover;" 
                                             alt="<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php endif; ?>
                                    <h5 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
                                    <div class="mb-3">
                                        <span class="badge bg-primary"><?php echo $category['product_count']; ?> Products</span>
                                    </div>
                                    <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-shopping-bag"></i> Shop Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-th-large fa-3x text-muted mb-3"></i>
                            <h4>No categories found</h4>
                            <p class="text-muted">Check back later for new categories</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Featured Categories -->
            <div class="row mt-5">
                <div class="col-12">
                    <h3 class="text-center mb-4">Popular Categories</h3>
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-laptop fa-3x text-primary mb-2"></i>
                                <h6>Electronics</h6>
                                <small class="text-muted">Latest gadgets & devices</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-tshirt fa-3x text-success mb-2"></i>
                                <h6>Fashion</h6>
                                <small class="text-muted">Trendy clothing & accessories</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-book fa-3x text-warning mb-2"></i>
                                <h6>Books</h6>
                                <small class="text-muted">Educational & entertainment</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-home fa-3x text-info mb-2"></i>
                                <h6>Home & Living</h6>
                                <small class="text-muted">Household essentials</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h3>Can't find what you're looking for?</h3>
                    <p class="text-muted mb-4">Browse our complete product collection or contact our support team</p>
                    <div>
                        <a href="products.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-th"></i> All Products
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-headset"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="text-center py-3">
                <p>&copy; 2024 BazarCart. All rights reserved. | BCA 4th Semester Project</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
// Helper function to get category icons
function getCategoryIcon($categoryName) {
    $icons = [
        'Electronics' => 'laptop',
        'Clothing' => 'tshirt',
        'Books' => 'book',
        'Home & Kitchen' => 'home',
        'Sports' => 'dumbbell'
    ];
    
    foreach ($icons as $name => $icon) {
        if (stripos($categoryName, $name) !== false) {
            return $icon;
        }
    }
    
    return 'box';
}
?>
