<?php
session_start();
require '../config/db.php';
require '../config/csrf.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Fetch user role and name safely
$role = $_SESSION['role'] ?? 'user';
$user_name = $_SESSION['name'] ?? 'User';

// Generate CSRF token for forms
$csrf_token = generateCSRFToken();

// Fetch user account info
$stmt = $conn->prepare("SELECT * FROM accounts WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    echo "<p>No account found for this user. Please contact admin.</p>";
    exit;
}

// Fetch last 5 transactions
$stmt2 = $conn->prepare(
    "SELECT * FROM transactions WHERE account_id=? ORDER BY created_at DESC LIMIT 5"
);
$stmt2->execute([$account['id']]);
$transactions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9f5ff; /* Light blue background */
            margin: 0;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
        }
        .admin-link {
            margin-bottom: 15px;
        }
        table th {
            background-color: #cfe2ff; /* Light blue header */
        }
        table td, table th {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="mb-4 text-center">Welcome, <?= htmlspecialchars($user_name) ?>!</h2>

            <?php if ($role === 'admin'): ?>
                <div class="text-center">
                    <a href="admin_dashboard.php" class="btn btn-primary admin-link">Open Admin Dashboard</a>
                </div>
            <?php endif; ?>

            <!-- Account Info Card -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title">Account Details</h5>
                    <p><strong>Account Number:</strong> <?= htmlspecialchars($account['account_number']) ?></p>
                    <p><strong>Balance:</strong> $<?= number_format($account['balance'], 2) ?></p>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Last 5 Transactions</h5>
                    <?php if (count($transactions) > 0): ?>
                        <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Target Account</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['type']) ?></td>
                                        <td>$<?= number_format($t['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($t['target_account'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($t['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php else: ?>
                        <p>No transactions yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="text-center mb-4">
                <a href="transfer.php" class="btn btn-info me-2">Transfer Money</a>
                <a href="history.php" class="btn btn-info me-2">Transaction History</a>
                <form method="POST" action="logout.php" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
