<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/functions.php';
require_once 'config/database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $full_name = sanitizeInput($_POST['full_name']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $message = "All fields are required";
        $message_type = "danger";
    } else {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            // Check if user already exists
            $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "Username or email already exists";
                $message_type = "warning";
            } else {
                // Create admin user
                $hashed_password = hashPassword($password);
                
                $insert_sql = "INSERT INTO users (username, email, password_hash, full_name, user_type) 
                              VALUES (?, ?, ?, ?, 'admin')";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);

                if ($stmt->execute()) {
                    $message = "Admin user created successfully! You can now login.";
                    $message_type = "success";
                    
                    // Clear form
                    $_POST = [];
                } else {
                    $message = "Failed to create admin user";
                    $message_type = "danger";
                }
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - BazarCart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-shield"></i> Create Admin Account
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="create_admin.php">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i> Username *
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="admin" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="admin@bazarcart.com" required>
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-id-card"></i> Full Name *
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="Administrator" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       value="admin123" required>
                                <small class="text-muted">Default password: admin123</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Admin User
                                </button>
                            </div>
                        </form>

                        <hr>

                        <div class="text-center">
                            <p class="mb-2">
                                <a href="login.php" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-in-alt"></i> Go to Login
                                </a>
                            </p>
                            <p class="mb-2">
                                <a href="test_db.php" class="btn btn-outline-info">
                                    <i class="fas fa-database"></i> Test Database
                                </a>
                            </p>
                            <p class="mb-2">
                                <a href="debug_login.php" class="btn btn-outline-warning">
                                    <i class="fas fa-bug"></i> Debug Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Instructions -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6>Quick Instructions:</h6>
                        <ol class="small">
                            <li>Click "Create Admin User" button above</li>
                            <li>Use default credentials: admin / admin123</li>
                            <li>Go to login page and test admin access</li>
                            <li>If successful, access admin panel at /admin/</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
