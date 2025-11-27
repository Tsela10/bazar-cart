<?php
require_once 'includes/functions.php';
require_once 'config/database.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // Validation
    if (empty($username) || empty($password)) {
        $errors = "Username and password are required";
    } else {
        // Check user credentials
        $database = new Database();
        $conn = $database->getConnection();

        $sql = "SELECT id, username, email, password_hash, full_name, user_type FROM users 
                WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (verifyPassword($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];

                // Remember me functionality
                if ($remember) {
                    // Set cookie for 30 days
                    setcookie('remember_user', $user['username'], time() + (30 * 24 * 60 * 60), '/');
                }

                // Redirect based on user type
                if ($user['user_type'] == 'admin') {
                    redirect('admin/');
                } else {
                    redirect('index.php');
                }
            } else {
                $errors = "Invalid username or password";
            }
        } else {
            $errors = "Invalid username or password";
        }
    }
}

// Check for remember me cookie
if (isset($_COOKIE['remember_user']) && !isLoggedIn()) {
    $remembered_username = $_COOKIE['remember_user'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BazarCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <a href="index.php" class="logo">
                        <img src="../bazar.jpg" alt="BazarCart Logo">
                        BazarCart
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Login Form -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-sign-in-alt text-primary"></i> Login to Your Account
                        </h3>
                        
                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $errors; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php" onsubmit="return validateForm('loginForm')">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i> Username or Email *
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($remembered_username) ? htmlspecialchars($remembered_username) : ''; ?>"
                                       required autofocus>
                                <div class="invalid-feedback">Please enter your username or email</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter your password</div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">
                                <a href="forgot-password.php" class="text-decoration-none">
                                    <i class="fas fa-key"></i> Forgot Password?
                                </a>
                            </p>
                            <p class="mb-0">Don't have an account? 
                                <a href="register.php" class="text-decoration-none">
                                    <i class="fas fa-user-plus"></i> Register here
                                </a>
                            </p>
                        </div>

                        <!-- Demo Accounts -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="text-center mb-3">Demo Accounts</h6>
                            <div class="small">
                                <p class="mb-1"><strong>Customer:</strong> customer / customer123</p>
                                <p class="mb-0"><strong>Admin:</strong> admin / admin123</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="text-center py-3">
                <p>&copy; 2024 BazarCart. All rights reserved. | BCA 4th Semester Project</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Auto-fill demo account
        function fillDemo(type) {
            if (type === 'customer') {
                document.getElementById('username').value = 'customer';
                document.getElementById('password').value = 'customer123';
            } else if (type === 'admin') {
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'admin123';
            }
        }

        // Add click handlers for demo accounts
        document.addEventListener('DOMContentLoaded', function() {
            const demoText = document.querySelector('.bg-light');
            if (demoText) {
                demoText.style.cursor = 'pointer';
                demoText.addEventListener('click', function(e) {
                    if (e.target.tagName === 'STRONG') {
                        const accountType = e.target.textContent.toLowerCase();
                        if (accountType.includes('customer')) {
                            fillDemo('customer');
                        } else if (accountType.includes('admin')) {
                            fillDemo('admin');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
