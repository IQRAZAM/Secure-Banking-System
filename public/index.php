<?php
session_start();
require '../config/db.php';
require '../config/csrf.php';
$csrf_token = generateCSRFToken();

$error = "";

if(isset($_POST['login'])){

    // 🔐 CSRF CHECK — MUST BE FIRST
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token. Request blocked.");
    }

    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // 1️⃣ Check failed attempts in last 15 minutes
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS fail_count
        FROM login_attempts
        WHERE email = ?
        AND success = 0
        AND attempt_time > (NOW() - INTERVAL 15 MINUTE)
    ");
    $stmt->execute([$email]);
    $fail = $stmt->fetch(PDO::FETCH_ASSOC);

    if($fail['fail_count'] >= 5){
        $error = "Too many failed login attempts. Try again after 15 minutes.";
    } else {

        // 2️⃣ Fetch user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $success = 0; // default = failed

        if($user && password_verify($password, $user['password'])){

            // ✅ Successful login
            $success = 1;

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // Log login attempt (success)
            $stmt = $conn->prepare("
                INSERT INTO login_attempts
                (user_id, email, ip_address, attempt_time, success)
                VALUES (?, ?, ?, NOW(), ?)
            ");
            $stmt->execute([
                $user['id'],
                $email,
                $ip,
                $success
            ]);

            header("Location: dashboard.php");
            exit;
        }

        // ❌ Failed login → log it
        $stmt = $conn->prepare("
            INSERT INTO login_attempts
            (user_id, email, ip_address, attempt_time, success)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([
            $user['id'] ?? NULL,
            $email,
            $ip,
            $success
        ]);

        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f5ff; /* Light blue background */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .login-card {
            max-width: 400px;
            margin: 80px auto;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow login-card">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Login</h3>

            <?php if(!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>


            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                </div>

                <p class="text-center mb-1">
                    <a href="forgot_password.php">Forgot Password?</a>
                </p>
                <p class="text-center">
                    Don't have an account? <a href="register.php">Register</a>
                </p>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
