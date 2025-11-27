<?php
require_once 'includes/functions.php';
require_once 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - BazarCart</title>
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
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-th-large"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">
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
            <h1 class="mb-0">About BazarCart</h1>
            <p class="text-muted mb-0">Your trusted online shopping destination</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h2 class="mb-4">Our Story</h2>
                    <p class="lead">Welcome to BazarCart, Nepal's premier online shopping destination designed to bring quality products right to your doorstep.</p>
                    <p>Founded with a vision to revolutionize the e-commerce landscape in Nepal, BazarCart combines cutting-edge technology with exceptional customer service to deliver an unparalleled shopping experience.</p>
                    <p>Our platform is carefully crafted to meet the diverse needs of Nepali consumers, offering everything from the latest electronics to traditional household items, all at competitive prices.</p>
                    <p>As a BCA 4th semester project, BazarCart demonstrates advanced web development capabilities while solving real-world shopping challenges faced by modern consumers.</p>
                </div>
                <div class="col-lg-6 mb-4">
                    <img src="assets/images/about-image.jpg" class="img-fluid rounded shadow" alt="About BazarCart" style="height: 400px; object-fit: cover;">
                </div>
            </div>

            <!-- Mission & Vision -->
            <div class="row mt-5">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="card-title">
                                <i class="fas fa-bullseye text-primary"></i> Our Mission
                            </h4>
                            <p>To provide Nepali consumers with a seamless, secure, and enjoyable online shopping experience by offering quality products, competitive prices, and exceptional customer service.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Quality products at affordable prices</li>
                                <li><i class="fas fa-check text-success"></i> Fast and reliable delivery</li>
                                <li><i class="fas fa-check text-success"></i> Excellent customer support</li>
                                <li><i class="fas fa-check text-success"></i> Secure payment options</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h4 class="card-title">
                                <i class="fas fa-eye text-primary"></i> Our Vision
                            </h4>
                            <p>To become Nepal's most trusted and preferred e-commerce platform, setting new standards for online shopping excellence and customer satisfaction.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-star text-warning"></i> Leading e-commerce platform in Nepal</li>
                                <li><i class="fas fa-star text-warning"></i> Innovation in online retail</li>
                                <li><i class="fas fa-star text-warning"></i> Customer-centric approach</li>
                                <li><i class="fas fa-star text-warning"></i> Sustainable business practices</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="row mt-5">
                <div class="col-12">
                    <h3 class="text-center mb-4">Why Choose BazarCart?</h3>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-success"></i>
                        </div>
                        <h5>Secure Shopping</h5>
                        <p class="text-muted">Advanced security measures to protect your data and transactions</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-truck fa-3x text-primary"></i>
                        </div>
                        <h5>Fast Delivery</h5>
                        <p class="text-muted">Quick and reliable delivery across Nepal within 2-3 business days</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-undo fa-3x text-warning"></i>
                        </div>
                        <h5>Easy Returns</h5>
                        <p class="text-muted">Hassle-free return policy within 7 days of purchase</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-headset fa-3x text-info"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted">Dedicated customer support team ready to assist you anytime</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-4">Our Development Team</h3>
            <p class="text-center text-muted mb-5">Meet the talented BCA students behind BazarCart</p>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-4x text-primary"></i>
                            </div>
                            <h5>Lead Developer</h5>
                            <p class="text-muted">Full-stack development & database design</p>
                            <div class="d-flex justify-content-center">
                                <a href="#" class="text-primary me-3"><i class="fab fa-github"></i></a>
                                <a href="#" class="text-info me-3"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="text-primary"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-4x text-success"></i>
                            </div>
                            <h5>UI/UX Designer</h5>
                            <p class="text-muted">Frontend design & user experience</p>
                            <div class="d-flex justify-content-center">
                                <a href="#" class="text-primary me-3"><i class="fab fa-github"></i></a>
                                <a href="#" class="text-info me-3"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="text-primary"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-4x text-warning"></i>
                            </div>
                            <h5>Database Administrator</h5>
                            <p class="text-muted">Database architecture & optimization</p>
                            <div class="d-flex justify-content-center">
                                <a href="#" class="text-primary me-3"><i class="fab fa-github"></i></a>
                                <a href="#" class="text-info me-3"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="text-primary"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technology Stack -->
    <section class="py-5">
        <div class="container">
            <h3 class="text-center mb-4">Technology Stack</h3>
            <p class="text-center text-muted mb-5">Built with modern web technologies</p>
            
            <div class="row">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                    <div class="text-center">
                        <i class="fab fa-html5 fa-3x text-danger mb-2"></i>
                        <h6>HTML5</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                    <div class="text-center">
                        <i class="fab fa-css3-alt fa-3x text-primary mb-2"></i>
                        <h6>CSS3</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                    <div class="text-center">
                        <i class="fab fa-bootstrap fa-3x text-info mb-2"></i>
                        <h6>Bootstrap</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                    <div class="text-center">
                        <i class="fab fa-php fa-3x text-primary mb-2"></i>
                        <h6>PHP</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                    <div class="text-center">
                        <i class="fas fa-database fa-3x text-success mb-2"></i>
                        <h6>MySQL</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                    <div class="text-center">
                        <i class="fab fa-js fa-3x text-warning mb-2"></i>
                        <h6>JavaScript</h6>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h3>Ready to start shopping?</h3>
                    <p class="mb-4">Join thousands of satisfied customers who trust BazarCart for their online shopping needs</p>
                    <div>
                        <a href="products.php" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-shopping-bag"></i> Start Shopping
                        </a>
                        <a href="register.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus"></i> Create Account
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
