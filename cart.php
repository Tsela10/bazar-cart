<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=cart.php');
}

$database = new Database();
$conn = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get cart items
$sql = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.is_active = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Calculate totals
$subtotal = 0;
$shipping = 50; // Fixed shipping cost
$cart_total = 0;

while ($item = $cart_items->fetch_assoc()) {
    $item_total = $item['quantity'] * $item['price'];
    $subtotal += $item_total;
}
$cart_total = $subtotal + $shipping;

// Reset result pointer for display
$cart_items->data_seek(0);

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $update_sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("iii", $quantity, $user_id, $product_id);
                $update_stmt->execute();
            }
        }
        redirect('cart.php');
    } elseif (isset($_POST['remove_item'])) {
        $product_id = (int)$_POST['product_id'];
        $remove_sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $remove_stmt = $conn->prepare($remove_sql);
        $remove_stmt->bind_param("ii", $user_id, $product_id);
        $remove_stmt->execute();
        redirect('cart.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - BazarCart</title>
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
                        <a class="nav-link active" href="cart.php">
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
            <h1 class="mb-0">Shopping Cart</h1>
            <p class="text-muted mb-0">Review and manage your selected items</p>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <?php if ($cart_items->num_rows > 0): ?>
                        <form method="POST" action="cart.php">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">
                                        <i class="fas fa-shopping-cart"></i> Cart Items (<?php echo $cart_items->num_rows; ?>)
                                    </h5>
                                    
                                    <div id="cartItems">
                                        <?php while ($item = $cart_items->fetch_assoc()): ?>
                                            <div class="cart-item" id="cart-item-<?php echo $item['product_id']; ?>">
                                                <div class="row align-items-center">
                                                    <div class="col-md-2">
                                                        <?php if ($item['image_url']): ?>
                                                            <img src="uploads/products/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                                 class="cart-item-image" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                        <?php else: ?>
                                                            <img src="assets/images/placeholder.jpg" class="cart-item-image" alt="Product Image">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                        <p class="text-muted mb-0 small">
                                                            <?php echo formatPrice($item['price']); ?> each
                                                        </p>
                                                        <?php if ($item['stock_quantity'] < 10): ?>
                                                            <p class="text-warning small mb-0">
                                                                <i class="fas fa-exclamation-triangle"></i> 
                                                                Only <?php echo $item['stock_quantity']; ?> left in stock
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="quantity-control">
                                                            <button type="button" class="quantity-btn" 
                                                                    data-action="decrease" 
                                                                    data-product-id="<?php echo $item['product_id']; ?>">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                            <input type="number" 
                                                                   name="quantity[<?php echo $item['product_id']; ?>]" 
                                                                   id="quantity-<?php echo $item['product_id']; ?>"
                                                                   class="form-control text-center" 
                                                                   value="<?php echo $item['quantity']; ?>" 
                                                                   min="1" 
                                                                   max="<?php echo $item['stock_quantity']; ?>"
                                                                   style="width: 60px;">
                                                            <button type="button" class="quantity-btn" 
                                                                    data-action="increase" 
                                                                    data-product-id="<?php echo $item['product_id']; ?>">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 text-end">
                                                        <h6 class="mb-2"><?php echo formatPrice($item['quantity'] * $item['price']); ?></h6>
                                                        <form method="POST" action="cart.php" style="display: inline;">
                                                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                            <input type="hidden" name="remove_item" value="1">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="return confirm('Remove this item from cart?')">
                                                                <i class="fas fa-trash"></i> Remove
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="my-3">
                                        <?php endwhile; ?>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="products.php" class="btn btn-outline-primary">
                                            <i class="fas fa-arrow-left"></i> Continue Shopping
                                        </a>
                                        <button type="submit" name="update_cart" class="btn btn-secondary">
                                            <i class="fas fa-sync"></i> Update Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <h4>Your cart is empty</h4>
                                <p class="text-muted mb-4">Looks like you haven't added any products to your cart yet</p>
                                <a href="products.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-bag"></i> Start Shopping
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Order Summary -->
                <?php if ($cart_items->num_rows > 0): ?>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="fas fa-receipt"></i> Order Summary
                                </h5>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span id="cartSubtotal"><?php echo formatPrice($subtotal); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span id="cartShipping"><?php echo formatPrice($shipping); ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Total:</h5>
                                    <h5 id="cartTotal"><?php echo formatPrice($cart_total); ?></h5>
                                </div>

                                <div class="alert alert-info small">
                                    <i class="fas fa-truck"></i> 
                                    Free shipping on orders above NPR 1000
                                </div>

                                <a href="checkout.php" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                                </a>

                                <div class="mt-3">
                                    <h6 class="mb-3">Payment Methods</h6>
                                    <div class="d-flex justify-content-around">
                                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                        <i class="fas fa-credit-card fa-2x text-primary"></i>
                                        <i class="fab fa-paypal fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Promo Code -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-tag"></i> Promo Code
                                </h6>
                                <form class="d-flex">
                                    <input type="text" class="form-control me-2" placeholder="Enter promo code">
                                    <button type="submit" class="btn btn-outline-primary">Apply</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
        // Auto-submit form on quantity change
        document.querySelectorAll('input[name^="quantity"]').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelector('form[method="POST"]').submit();
            });
        });

        // Update totals dynamically
        function updateCartTotals() {
            let subtotal = 0;
            const shipping = 50;
            
            document.querySelectorAll('.cart-item').forEach(item => {
                const price = parseFloat(item.querySelector('.text-muted').textContent.replace(/[^\d.]/g, ''));
                const quantity = parseInt(item.querySelector('input[name^="quantity"]').value);
                subtotal += price * quantity;
            });
            
            const total = subtotal + shipping;
            
            document.getElementById('cartSubtotal').textContent = formatPrice(subtotal);
            document.getElementById('cartTotal').textContent = formatPrice(total);
            
            // Update shipping based on subtotal
            if (subtotal >= 1000) {
                document.getElementById('cartShipping').textContent = formatPrice(0);
                document.getElementById('cartTotal').textContent = formatPrice(subtotal);
            } else {
                document.getElementById('cartShipping').textContent = formatPrice(shipping);
                document.getElementById('cartTotal').textContent = formatPrice(total);
            }
        }

        // Initialize quantity controls
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                updateQuantity(this);
            });
        });
    </script>
</body>
</html>
