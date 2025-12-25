<?php
require '../config/db.php';
require '../config/csrf.php';
$csrf_token = generateCSRFToken();

$message = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $email = htmlspecialchars($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user){
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $stmt = $conn->prepare(
            "UPDATE users SET reset_token=?, token_expiry=? WHERE id=?"
        );
        $stmt->execute([$token, $expiry, $user['id']]);

        $resetLink = "http://localhost/banking-system/public/reset_password.php?token=$token";

        $message = "Password reset link (simulation): <br>
                    <a href='$resetLink'>$resetLink</a>";
    } else {
        $message = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f5ff; /* Light blue background */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .card {
            max-width: 400px;
            margin: 80px auto;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow p-4">
        <h3 class="card-title text-center mb-4">Forgot Password</h3>

        <!-- Message -->
        <?php if(!empty($message)): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </div>

            <p class="text-center">
                <a href="index.php">Back to Login</a>
            </p>
        </form>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
