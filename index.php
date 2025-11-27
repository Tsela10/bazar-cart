<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Get featured products
$database = new Database();
$conn = $database->getConnection();

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1 
        ORDER BY p.created_at DESC 
        LIMIT 8";
$result = $conn->query($sql);

// Get categories for display
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BazarCart - Your Online Shopping Destination</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
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
                        <i class="fas fa-phone"></i> <?php echo getSetting('site_phone', '+977-98XXXXXXXX'); ?>
                    </div>
                    <div class="d-inline-block">
                        <i class="fas fa-envelope"></i> <?php echo getSetting('site_email', 'info@bazarcart.com'); ?>
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
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-shopping-bag"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="fade-in">
                <h1 class="display-4 fw-bold mb-3">Welcome to BazarCart</h1>
                <p class="lead mb-4">Your trusted online shopping destination with quality products and great prices</p>
                <div>
                    <a href="products.php" class="btn btn-primary btn-lg me-3 bounce-in">
                        <i class="fas fa-shopping-bag"></i> Shop Now
                    </a>
                    <a href="register.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Shop by Category</h2>
            <div class="row">
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <a href="products.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                            <div class="category-card">
                                <div class="category-icon">
                                    <i class="fas fa-<?php echo getCategoryIcon($category['name']); ?>"></i>
                                </div>
                                <h5 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars($category['description']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4 fw-bold">Featured Products</h2>
            <div class="row">
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card product-card h-100">
                            <?php if ($product['image_url']): ?>
                                <img src="uploads/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder.jpg" class="card-img-top" alt="Product Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="text-muted small"><?php echo substr(htmlspecialchars($product['description']), 0, 60) . '...'; ?></p>
                                <div class="product-price mb-3"><?php echo formatPrice($product['price']); ?></div>
                                
                                <!-- Quantity Selector -->
                                <div class="quantity-selector mb-3">
                                    <label class="form-label small">Quantity:</label>
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary decrease-qty" type="button" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center quantity-input" 
                                               data-product-id="<?php echo $product['id']; ?>" 
                                               value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" readonly>
                                        <button class="btn btn-outline-secondary increase-qty" type="button" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <button class="btn btn-primary btn-sm addToCart" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <button class="btn btn-secondary btn-sm buyNow" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-bolt"></i> Buy Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-th"></i> View All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-box">
                        <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                        <h4>Free Delivery</h4>
                        <p class="text-muted">Free delivery on orders above NPR <?php echo number_format(getSetting('free_shipping_threshold', 1000)); ?></p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-box">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h4>Secure Payment</h4>
                        <p class="text-muted">100% secure payment process</p>
                    </div>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-box">
                        <i class="fas fa-undo fa-3x text-warning mb-3"></i>
                        <h4>Easy Returns</h4>
                        <p class="text-muted">7 days return policy</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="footer-title">BazarCart</h5>
                    <p>Your trusted online shopping destination in Nepal. Quality products, great prices, and excellent service.</p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="terms.php">Terms & Conditions</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="footer-title">Customer Service</h5>
                    <ul class="footer-links">
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="returns.php">Returns</a></li>
                        <li><a href="support.php">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="footer-title">Contact Info</h5>
                    <p><i class="fas fa-map-marker-alt"></i> Kathmandu, Nepal</p>
                    <p><i class="fas fa-phone"></i> +977-98XXXXXXXX</p>
                    <p><i class="fas fa-envelope"></i> info@bazarcart.com</p>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center py-3">
                <p>&copy; 2024 BazarCart. All rights reserved. | BCA 4th Semester Project</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Quantity controls
        document.addEventListener('click', function(e) {
            // Decrease quantity
            if (e.target.closest('.decrease-qty')) {
                const button = e.target.closest('.decrease-qty');
                const productId = button.dataset.productId;
                const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                const currentValue = parseInt(input.value);
                const minValue = parseInt(input.min);
                
                if (currentValue > minValue) {
                    input.value = currentValue - 1;
                }
            }
            
            // Increase quantity
            if (e.target.closest('.increase-qty')) {
                const button = e.target.closest('.increase-qty');
                const productId = button.dataset.productId;
                const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                const currentValue = parseInt(input.value);
                const maxValue = parseInt(input.max);
                
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                }
            }
        });
        
        // Buy Now functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.buyNow')) {
                const button = e.target.closest('.buyNow');
                const productId = button.dataset.productId;
                const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                const quantity = parseInt(input.value);
                
                // Add to cart and redirect to checkout
                addToCart(productId, quantity, function() {
                    window.location.href = 'checkout.php';
                });
            }
        });
        
        // Updated Add to Cart to use quantity
        document.addEventListener('click', function(e) {
            if (e.target.closest('.addToCart')) {
                const button = e.target.closest('.addToCart');
                const productId = button.dataset.productId;
                const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                const quantity = parseInt(input.value);
                
                addToCart(productId, quantity);
            }
        });
        
        function addToCart(productId, quantity, callback) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart badge
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                    
                    // Show success message
                    showNotification('Product added to cart successfully!', 'success');
                    
                    // Execute callback if provided
                    if (callback) {
                        callback();
                    }
                } else {
                    showNotification(data.message || 'Failed to add product to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        }
        
        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
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
