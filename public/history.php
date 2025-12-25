<?php
session_start();
require '../config/db.php';
require '../config/csrf.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$csrf_token = generateCSRFToken();

// Get user's account
$stmt = $conn->prepare("SELECT * FROM accounts WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$account){
    echo "No account found.";
    exit;
}

// Fetch all transactions
$stmt2 = $conn->prepare("SELECT * FROM transactions WHERE account_id=? ORDER BY created_at DESC");
$stmt2->execute([$account['id']]);
$transactions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f5ff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
        }
        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Transaction History</h2>

    <div class="card shadow p-3 mb-4">
        <p><strong>Account Number:</strong> <?= htmlspecialchars($account['account_number']) ?></p>
        <p><strong>Current Balance:</strong> $<?= number_format($account['balance'], 2) ?></p>
    </div>

    <div class="card shadow p-3 table-container">
        <?php if(count($transactions) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Target Account</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transactions as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars(ucfirst($t['type'])) ?></td>
                            <td>$<?= number_format($t['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($t['target_account'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($t['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p>No transactions found.</p>
        <?php endif; ?>
    </div>

    <div class="text-center mt-3">
        <a href="dashboard.php" class="btn btn-info me-2">Back to Dashboard</a>
        <a href="transfer.php" class="btn btn-primary me-2">Transfer Money</a>
        <form method="POST" action="logout.php" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
