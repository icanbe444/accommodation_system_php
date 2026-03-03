<?php
session_start();

// Set timezone to Nigeria (GMT+1)
date_default_timezone_set('Africa/Lagos');

require_once(__DIR__ . '/../includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $_SESSION['error_message'] = "Please enter both email and password.";
    } else {
        // Query user by email
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_email'] = $user['email'];
            
            header("Location: " . BASE_URL . "?page=admin");
            exit;
        } else {
            $_SESSION['error_message'] = "Invalid email or password.";
        }
    }
}

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "?page=admin");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TPAIS Accommodation</title>
</head>
<body>
    <div class="container" style="max-width: 400px; margin: 5rem auto;">
        <div class="card">
            <h2 style="text-align: center; color: #0099CC; margin-bottom: 2rem;">Admin Login</h2>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Login</button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem;">
                <a href="<?php echo BASE_URL; ?>" style="color: #0099CC; text-decoration: none;">Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>
