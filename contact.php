<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    // Process contact form if no errors
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For demo purposes, we'll just show success message
        
        $success = "Thank you for contacting us! We'll get back to you within 24 hours.";
        
        // Clear form
        $_POST = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BazarCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
    <style>
        .map-container {
            position: relative;
            overflow: hidden;
        }
        .google-maps-placeholder {
            height: 400px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .google-maps-placeholder::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect fill="rgba(255,255,255,0.1)" x="0" y="0" width="20" height="20"/><rect fill="rgba(255,255,255,0.05)" x="20" y="20" width="20" height="20"/><rect fill="rgba(255,255,255,0.1)" x="40" y="0" width="20" height="20"/><rect fill="rgba(255,255,255,0.05)" x="60" y="20" width="20" height="20"/><rect fill="rgba(255,255,255,0.1)" x="80" y="0" width="20" height="20"/><rect fill="rgba(255,255,255,0.05)" x="0" y="40" width="20" height="20"/><rect fill="rgba(255,255,255,0.1)" x="20" y="60" width="20" height="20"/><rect fill="rgba(255,255,255,0.05)" x="40" y="40" width="20" height="20"/><rect fill="rgba(255,255,255,0.1)" x="60" y="60" width="20" height="20"/><rect fill="rgba(255,255,255,0.05)" x="80" y="40" width="20" height="20"/><rect fill="rgba(255,255,255,0.1)" x="0" y="80" width="20" height="20"/><rect fill="rgba(255,255,255,0.05)" x="20" y="100" width="20" height="20"/><rect fill="rgba(255,255,255,0.1)" x="40" y="80" width="20" height="20"/><rect fill="rgba(255,255,255,0.05)" x="60" y="100" width="20" height="20"/><rect fill="rgba(255,255,255,0.1)" x="80" y="80" width="20" height="20"/></svg>');
            opacity: 0.3;
        }
        .maps-content {
            text-align: center;
            color: white;
            z-index: 2;
            position: relative;
            padding: 2rem;
        }
        .maps-content i {
            opacity: 0.9;
            animation: pulse 2s infinite;
        }
        .maps-content h4 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .maps-content p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .google-maps-placeholder:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }
        .contact-form {
            background: linear-gradient(135deg, rgba(69, 113, 189, 0.05) 0%, rgba(245, 118, 21, 0.05) 100%);
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
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
                        <a class="nav-link active" href="contact.php">
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
            <h1 class="mb-0">Contact Us</h1>
            <p class="text-muted mb-0">Get in touch with our team</p>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-8 mb-4">
                    <div class="card contact-form">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <i class="fas fa-envelope"></i> Send us a Message
                            </h4>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                </div>
                            <?php endif; ?>

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

                            <form method="POST" action="contact.php" onsubmit="return validateForm('contactForm')">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">
                                                <i class="fas fa-user"></i> Your Name *
                                            </label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                                   required>
                                            <div class="invalid-feedback">Please enter your name</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope"></i> Email Address *
                                            </label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                                   required>
                                            <div class="invalid-feedback">Please enter a valid email address</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">
                                        <i class="fas fa-tag"></i> Subject *
                                    </label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="order_inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'order_inquiry') ? 'selected' : ''; ?>>
                                            Order Inquiry
                                        </option>
                                        <option value="product_question" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'product_question') ? 'selected' : ''; ?>>
                                            Product Question
                                        </option>
                                        <option value="technical_support" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'technical_support') ? 'selected' : ''; ?>>
                                            Technical Support
                                        </option>
                                        <option value="payment_issue" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'payment_issue') ? 'selected' : ''; ?>>
                                            Payment Issue
                                        </option>
                                        <option value="general_feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'general_feedback') ? 'selected' : ''; ?>>
                                            General Feedback
                                        </option>
                                        <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'other') ? 'selected' : ''; ?>>
                                            Other
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">Please select a subject</div>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">
                                        <i class="fas fa-comment"></i> Message *
                                    </label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                    <div class="invalid-feedback">Please enter your message</div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane"></i> Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-12 mb-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-info-circle"></i> Contact Information
                            </h5>
                            
                            <div class="mb-3">
                                <h6><i class="fas fa-map-marker-alt text-primary"></i> Address</h6>
                                <p class="text-muted">
                                    <?php echo getSetting('site_name', 'BazarCart'); ?> Headquarters<br>
                                    <?php echo getSetting('site_address', 'Boudha, Kathmandu'); ?><br>
                                    Nepal 44600
                                </p>
                            </div>

                            <div class="mb-3">
                                <h6><i class="fas fa-phone text-primary"></i> Phone</h6>
                                <p class="text-muted">
                                    <?php echo getSetting('site_phone', '+977-98XXXXXXXX'); ?><br>
                                    +977-01XXXXXXXX
                                </p>
                            </div>

                            <div class="mb-3">
                                <h6><i class="fas fa-envelope text-primary"></i> Email</h6>
                                <p class="text-muted">
                                    <?php echo getSetting('site_email', 'info@bazarcart.com'); ?><br>
                                    support@bazarcart.com
                                </p>
                            </div>

                            <div class="mb-3">
                                <h6><i class="fas fa-clock text-primary"></i> Business Hours</h6>
                                <p class="text-muted">
                                    Monday - Friday: 9:00 AM - 6:00 PM<br>
                                    Saturday: 10:00 AM - 4:00 PM<br>
                                    Sunday: Closed
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-link"></i> Quick Links
                            </h5>
                            
                            <div class="list-group list-group-flush">
                                <a href="faq.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-question-circle"></i> Frequently Asked Questions
                                </a>
                                <a href="shipping.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-truck"></i> Shipping Information
                                </a>
                                <a href="returns.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-undo"></i> Return Policy
                                </a>
                                <a href="terms.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-contract"></i> Terms & Conditions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-4">Find Us</h3>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <!-- Placeholder for map -->
                            <div class="bg-light text-center py-5" style="height: 400px;">
                                <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                                <h4>Interactive Map</h4>
                                <p class="text-muted">Our location in Boudha, Kathmandu</p>
                                <small class="text-muted">Map integration would be implemented here in production</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Media -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="text-center">
                        <h3 class="mb-4">Connect With Us</h3>
                        <p class="text-muted mb-4">Follow us on social media for updates and special offers</p>
                        
                        <div class="d-flex justify-content-center">
                            <a href="#" class="btn btn-primary btn-lg me-3">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-info btn-lg me-3">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-danger btn-lg me-3">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-danger btn-lg">
                                <i class="fab fa-youtube"></i>
                            </a>
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
                
                // Email validation
                if (input.type === 'email' && input.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value)) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            });
            
            return isValid;
        }

        // Auto-remove success message after 5 seconds
        setTimeout(function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.remove();
            }
        }, 5000);
    </script>
</body>
</html>
