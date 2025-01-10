<?php
$host = '127.0.0.1';
$user = 'root';
$pass = 'password';
$database = 'OOP';

try {
    $conn = new PDO("mysql:host={$host};dbname={$database}", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
