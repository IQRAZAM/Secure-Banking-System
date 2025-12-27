<?php
date_default_timezone_set('Asia/Karachi');
$servername = "localhost";
$username = "root";   // default XAMPP username
$password = "";       // default XAMPP password
$dbname = "banking_system"; // the database we created

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
