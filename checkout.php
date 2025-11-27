<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=checkout.php');
}

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get cart items
$sql = "SELECT c.*, p.name, p.price, p.stock_quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.is_active = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Check if cart is empty
if ($cart_items->num_rows === 0) {
    redirect('cart.php');
}

// Get user information
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Calculate totals
$subtotal = 0;
while ($item = $cart_items->fetch_assoc()) {
    $item_total = $item['quantity'] * $item['price'];
    $subtotal += $item_total;
}

// Get shipping settings
$shipping_cost = getSetting('shipping_cost', 50);
$free_shipping_threshold = getSetting('free_shipping_threshold', 1000);
$shipping = $subtotal >= $free_shipping_threshold ? 0 : $shipping_cost;
$total = $subtotal + $shipping;

// Reset result pointer
$cart_items->data_seek(0);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $shipping_address = sanitizeInput($_POST['shipping_address']);
    $phone_number = sanitizeInput($_POST['phone_number']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    $notes = sanitizeInput($_POST['notes']);

    // Validation
    if (empty($shipping_address)) {
        $errors[] = "Shipping address is required";
    }

    if (empty($phone_number)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^[0-9]{10}$/', preg_replace('/[^0-9]/', '', $phone_number))) {
        $errors[] = "Please enter a valid 10-digit phone number";
    }

    if (empty($payment_method)) {
        $errors[] = "Please select a payment method";
    }

    // Check stock availability
    $cart_items->data_seek(0);
    while ($item = $cart_items->fetch_assoc()) {
        if ($item['stock_quantity'] < $item['quantity']) {
            $errors[] = "Insufficient stock for " . htmlspecialchars($item['name']);
        }
    }

    // Process order if no errors
    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Generate order number
            $order_number = generateOrderNumber();
            
            // Create order
            $order_sql = "INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, phone_number, payment_method, notes) 
                         VALUES (?, ?, ?, 'pending', ?, ?, ?, ?)";
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param("isdssss", $user_id, $order_number, $total, $shipping_address, $phone_number, $payment_method, $notes);
            $order_stmt->execute();
            $order_id = $conn->insert_id;

            // Insert order items
            $cart_items->data_seek(0);
            while ($item = $cart_items->fetch_assoc()) {
                $item_total = $item['quantity'] * $item['price'];
                $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price_per_unit, total_price) 
                                 VALUES (?, ?, ?, ?, ?)";
                $order_item_stmt = $conn->prepare($order_item_sql);
                $order_item_stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $item_total);
                $order_item_stmt->execute();

                // Update product stock
                $new_stock = $item['stock_quantity'] - $item['quantity'];
                $stock_sql = "UPDATE products SET stock_quantity = ? WHERE id = ?";
                $stock_stmt = $conn->prepare($stock_sql);
                $stock_stmt->bind_param("ii", $new_stock, $item['product_id']);
                $stock_stmt->execute();
            }

            // Clear cart
            $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
            $clear_cart_stmt = $conn->prepare($clear_cart_sql);
            $clear_cart_stmt->bind_param("i", $user_id);
            $clear_cart_stmt->execute();

            $conn->commit();
            
            // Redirect to order confirmation
            redirect('order-confirmation.php?order_id=' . $order_id);
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Order processing failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - BazarCart</title>
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
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <span class="cart-badge"><?php echo getCartCount(); ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Checkout Progress -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0">
                            <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
                            <li class="breadcrumb-item active">Checkout</li>
                            <li class="breadcrumb-item active">Confirmation</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Form -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Billing Information -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-truck"></i> Shipping Information
                            </h5>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="checkout.php" onsubmit="return validateForm('checkoutForm')">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i> Shipping Address *
                                    </label>
                                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : htmlspecialchars($user['address']); ?></textarea>
                                    <div class="invalid-feedback">Please enter your shipping address</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone_number" class="form-label">
                                                <i class="fas fa-phone"></i> Phone Number *
                                            </label>
                                            <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                                   value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : htmlspecialchars($user['phone']); ?>"
                                                   placeholder="98XXXXXXXX" required>
                                            <div class="invalid-feedback">Please enter a valid phone number</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_method" class="form-label">
                                                <i class="fas fa-credit-card"></i> Payment Method *
                                            </label>
                                            <select class="form-select" id="payment_method" name="payment_method" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="cash_on_delivery" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cash_on_delivery') ? 'selected' : ''; ?>>
                                                    Cash on Delivery
                                                </option>
                                                <option value="khalti" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'khalti') ? 'selected' : ''; ?>>
                                                    Khalti
                                                </option>
                                                <option value="esewa" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'esewa') ? 'selected' : ''; ?>>
                                                    eSewa
                                                </option>
                                                <option value="bank_transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>
                                                    Bank Transfer
                                                </option>
                                            </select>
                                            <div class="invalid-feedback">Please select a payment method</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note"></i> Order Notes (Optional)
                                    </label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Any special instructions for your order..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="cart.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Cart
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-check"></i> Place Order
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-receipt"></i> Order Summary
                            </h5>
                            
                            <!-- Order Items -->
                            <div class="order-items mb-3">
                                <?php $cart_items->data_seek(0); ?>
                                <?php while ($item = $cart_items->fetch_assoc()): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <small><?php echo htmlspecialchars($item['name']); ?></small>
                                            <br>
                                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                        </div>
                                        <small><?php echo formatPrice($item['quantity'] * $item['price']); ?></small>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?php echo formatPrice($subtotal); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span><?php echo formatPrice($shipping); ?></span>
                            </div>
                            <?php if ($shipping === 0): ?>
                                <div class="alert alert-success small">
                                    <i class="fas fa-truck"></i> Free shipping applied!
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <h5>Total:</h5>
                                <h5><?php echo formatPrice($total); ?></h5>
                            </div>

                            <!-- Security Badge -->
                            <div class="text-center">
                                <div class="badge bg-success mb-2">
                                    <i class="fas fa-shield-alt"></i> Secure Checkout
                                </div>
                                <p class="text-muted small">
                                    Your payment information is encrypted and secure
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Info -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-info-circle"></i> Delivery Information
                            </h6>
                            <ul class="small">
                                <li>Standard delivery: 2-3 business days</li>
                                <li>Express delivery: 1-2 business days</li>
                                <li>Free shipping on orders above NPR <?php echo number_format($free_shipping_threshold); ?></li>
                                <li>Cash on delivery available</li>
                            </ul>
                        </div>
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
    
    <script>
        // Show payment method details
        document.getElementById('payment_method').addEventListener('change', function() {
            const paymentMethod = this.value;
            const paymentInfo = document.getElementById('paymentInfo');
            
            if (paymentInfo) {
                paymentInfo.remove();
            }
            
            if (paymentMethod === 'khalti' || paymentMethod === 'esewa') {
                const infoDiv = document.createElement('div');
                infoDiv.id = 'paymentInfo';
                infoDiv.className = 'alert alert-info mt-3';
                infoDiv.innerHTML = `
                    <i class="fas fa-info-circle"></i> 
                    You will be redirected to ${paymentMethod === 'khalti' ? 'Khalti' : 'eSewa'} payment gateway after placing the order.
                `;
                this.parentNode.appendChild(infoDiv);
            }
        });

        // Form validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            let isValid = true;
            const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
                
                // Phone validation
                if (input.name === 'phone_number' && input.value) {
                    const phoneRegex = /^[0-9]{10}$/;
                    if (!phoneRegex.test(input.value.replace(/[^0-9]/g, ''))) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            });
            
            if (isValid) {
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
            }
            
            return isValid;
        }
    </script>
</body>
</html>
