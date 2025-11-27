<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get filters from URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : 100000;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 12;
$offset = ($page - 1) * $records_per_page;

// Build query
$where_conditions = [];
$params = [];
$types = '';

// Base query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1";

// Add category filter
if (!empty($category_id)) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

// Add search filter
if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Add price filter
$where_conditions[] = "p.price BETWEEN ? AND ?";
$params[] = $price_min;
$params[] = $price_max;
$types .= 'dd';

// Combine where conditions
if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
}

// Add sorting
switch ($sort) {
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

// Add pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $records_per_page;
$params[] = $offset;
$types .= 'ii';

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get total records for pagination
$count_sql = "SELECT COUNT(*) as total 
              FROM products p 
              WHERE p.is_active = 1";

$count_conditions = [];
$count_params = [];
$count_types = '';

if (!empty($category_id)) {
    $count_conditions[] = "p.category_id = ?";
    $count_params[] = $category_id;
    $count_types .= 'i';
}

if (!empty($search)) {
    $count_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = '%' . $search . '%';
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_types .= 'ss';
}

$count_conditions[] = "p.price BETWEEN ? AND ?";
$count_params[] = $price_min;
$count_params[] = $price_max;
$count_types .= 'dd';

if (!empty($count_conditions)) {
    $count_sql .= " AND " . implode(" AND ", $count_conditions);
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get categories for filter
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - BazarCart</title>
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
                        <a class="nav-link active" href="products.php">
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

    <!-- Page Header -->
    <section class="py-4 bg-light">
        <div class="container">
            <h1 class="mb-0">Our Products</h1>
            <p class="text-muted mb-0">Browse our wide range of quality products</p>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-filter"></i> Filters
                            </h5>
                            
                            <!-- Search -->
                            <form method="GET" action="products.php">
                                <div class="mb-3">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Search products...">
                                </div>

                                <!-- Category Filter -->
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">All Categories</option>
                                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <!-- Price Range -->
                                <div class="mb-3">
                                    <label class="form-label">Price Range</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="number" class="form-control" name="price_min" 
                                                   value="<?php echo $price_min; ?>" placeholder="Min" min="0">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control" name="price_max" 
                                                   value="<?php echo $price_max; ?>" placeholder="Max" min="0">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sort -->
                                <div class="mb-3">
                                    <label for="sort" class="form-label">Sort By</label>
                                    <select class="form-select" id="sort" name="sort">
                                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>
                                            Newest First
                                        </option>
                                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>
                                            Name (A-Z)
                                        </option>
                                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>
                                            Name (Z-A)
                                        </option>
                                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>
                                            Price (Low to High)
                                        </option>
                                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>
                                            Price (High to Low)
                                        </option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5>
                            Showing <?php echo $result->num_rows; ?> of <?php echo $total_records; ?> products
                        </h5>
                    </div>

                    <div class="row" id="productsContainer">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($product = $result->fetch_assoc()): ?>
                                <div class="col-md-4 col-sm-6 mb-4">
                                    <div class="product-card" data-category="<?php echo $product['category_id']; ?>">
                                        <?php if ($product['image_url']): ?>
                                            <img src="uploads/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                 class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <?php else: ?>
                                            <img src="assets/images/placeholder.jpg" class="product-image" alt="Product Image">
                                        <?php endif; ?>
                                        <div class="product-body">
                                            <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                            <p class="product-description">
                                                <?php echo substr(htmlspecialchars($product['description']), 0, 80) . '...'; ?>
                                            </p>
                                            <div class="text-muted small mb-2">
                                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                                            </div>
                                            <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                                            
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
                                                <button class="btn btn-success btn-sm buyNow" data-product-id="<?php echo $product['id']; ?>">
                                                    <i class="fas fa-bolt"></i> Buy Now
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h4>No products found</h4>
                                    <p class="text-muted">Try adjusting your filters or search terms</p>
                                    <a href="products.php" class="btn btn-primary">
                                        <i class="fas fa-redo"></i> Reset Filters
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
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
