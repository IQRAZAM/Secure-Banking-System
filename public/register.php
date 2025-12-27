<?php
session_start();
require '../config/db.php';
require '../config/csrf.php';

$csrf_token = generateCSRFToken();

if(isset($_POST['register'])){
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    if($stmt->rowCount() > 0){
        $error = "Email already registered!";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
        if($stmt->execute([$name,$email,$password])){
            $user_id = $conn->lastInsertId();

            // Generate unique account number
            do {
                $account_number = rand(1000000000, 9999999999); // 10-digit number
                $stmt_check = $conn->prepare("SELECT * FROM accounts WHERE account_number=?");
                $stmt_check->execute([$account_number]);
            } while($stmt_check->rowCount() > 0);

            // Insert account for the user
            $initial_balance = 500; 
            $stmt_acc = $conn->prepare("INSERT INTO accounts (user_id, account_number, balance, status) VALUES (?,?,?,?)");
            $stmt_acc->execute([$user_id, $account_number, $initial_balance, 'active']);

            $success = "Registration successful! You can now login.";
        } else {
            $error = "Something went wrong!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4 text-center">Register</h3>

                    <!-- Error / Success Messages -->
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-primary">Register</button>
                        </div>

                        <p class="mt-3 text-center">
                            Already have an account? <a href="index.php">Login here</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
