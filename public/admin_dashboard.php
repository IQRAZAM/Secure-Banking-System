<?php
session_start();
require '../config/db.php';
require '../config/csrf.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

if($_SESSION['role'] !== 'admin'){
    echo "Access denied. Admins only.";
    exit;
}

$csrf_token = generateCSRFToken();

$stmt = $conn->prepare("
    SELECT users.id, users.name, users.email, users.role,
           accounts.account_number, accounts.balance, accounts.status
    FROM users
    LEFT JOIN accounts ON users.id = accounts.user_id
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f5ff; /* Light blue background */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .table-container {
            margin-top: 20px;
        }
        .btn-action {
            margin-right: 5px;
        }
        .card {
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Admin Dashboard</h2>

    <div class="table-container card shadow p-3">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Account #</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['role']) ?></td>
                            <td><?= htmlspecialchars($u['account_number'] ?? '-') ?></td>
                            <td>$<?= number_format($u['balance'] ?? 0, 2) ?></td>
                            <td><?= htmlspecialchars($u['status'] ?? '-') ?></td>
                            <td>
                                <?php if($u['status'] === 'active'): ?>
                                    <a href="toggle_account.php?id=<?= $u['id'] ?>&action=block&csrf_token=<?= $csrf_token ?>" class="btn btn-sm btn-danger btn-action">Block</a>
                                <?php elseif($u['status'] === 'blocked'): ?>
                                    <a href="toggle_account.php?id=<?= $u['id'] ?>&action=unblock&csrf_token=<?= $csrf_token ?>" class="btn btn-sm btn-success btn-action">Unblock</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 text-center">
        <a href="dashboard.php" class="btn btn-info me-2">User Dashboard</a>
        <form method="POST" action="logout.php" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
