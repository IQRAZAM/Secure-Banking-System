<?php
session_start();
require '../config/db.php';
require '../config/csrf.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$csrf_token = generateCSRFToken();

// Fetch sender account info
$stmt = $conn->prepare("SELECT * FROM accounts WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$account){
    echo "<p>No account found for this user. Please contact admin.</p>";
    exit;
}

// Handle form submission
if(isset($_POST['transfer'])){
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token. Request blocked.");
    }

    $target_account = htmlspecialchars($_POST['target_account']);
    $amount = floatval($_POST['amount']);

    if($amount <= 0){
        $error = "Enter a valid amount.";
    } elseif($target_account == $account['account_number']){
        $error = "Cannot transfer to your own account.";
    } elseif($amount > $account['balance']){
        $error = "Insufficient balance.";
    } else {
        $stmt2 = $conn->prepare("SELECT * FROM accounts WHERE account_number=? AND status='active'");
        $stmt2->execute([$target_account]);
        $target = $stmt2->fetch(PDO::FETCH_ASSOC);

        if(!$target){
            $error = "Target account not found or inactive.";
        } else {
            try {
                $conn->beginTransaction();

                $stmt3 = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
                $stmt3->execute([$amount, $account['id']]);

                $stmt4 = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
                $stmt4->execute([$amount, $target['id']]);

                $stmt5 = $conn->prepare("INSERT INTO transactions (account_id, type, amount, target_account) VALUES (?,?,?,?)");
                $stmt5->execute([$account['id'], 'transfer', $amount, $target_account]);

                $stmt6 = $conn->prepare("INSERT INTO transactions (account_id, type, amount, target_account) VALUES (?,?,?,?)");
                $stmt6->execute([$target['id'], 'deposit', $amount, $account['account_number']]);

                $conn->commit();
                $success = "Transfer successful!";
                $account['balance'] -= $amount;

            } catch(PDOException $e){
                $conn->rollBack();
                $error = "Transaction failed: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transfer Money</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f5ff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .card {
            max-width: 450px;
            margin: 50px auto;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4">Transfer Money</h3>

        <p><strong>Your Account Number:</strong> <?= htmlspecialchars($account['account_number']) ?></p>
        <p><strong>Balance:</strong> $<?= number_format($account['balance'], 2) ?></p>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="mb-3">
                <label for="target_account" class="form-label">Target Account Number</label>
                <input type="text" id="target_account" name="target_account" class="form-control" placeholder="Enter target account" required>
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" id="amount" name="amount" step="0.01" class="form-control" placeholder="Enter amount" required>
            </div>

            <div class="d-grid">
                <button type="submit" name="transfer" class="btn btn-primary">Transfer</button>
            </div>
        </form>

        <p class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-info me-2">Back to Dashboard</a>
            <form method="POST" action="logout.php" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
