<?php
session_start();
require '../config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token. Request blocked.");
    }

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit;

} else {
    header("Location: dashboard.php");
    exit;
}
?>
