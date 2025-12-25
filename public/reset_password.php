<?php
require '../config/db.php';

$token = $_GET['token'] ?? '';
$message = "";

$stmt = $conn->prepare(
    "SELECT id FROM users WHERE reset_token=? AND token_expiry > NOW()"
);
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    die("Invalid or expired token.");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "UPDATE users SET password=?, reset_token=NULL, token_expiry=NULL WHERE id=?"
    );
    $stmt->execute([$newPassword, $user['id']]);

    $message = "Password reset successful. <a href='index.php'>Login</a>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>

<h2>Reset Password</h2>

<form method="POST">
    <input type="password" name="password" required placeholder="New password">
    <br><br>
    <button type="submit">Reset Password</button>
</form>

<p><?= $message ?></p>

</body>
</html>
