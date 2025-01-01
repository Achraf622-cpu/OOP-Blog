<?php
require 'connect.php'; 
require 'Admin.php'; 
require 'User.php'; 
session_start();

class Login {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function authenticate($email, $password) {
        $stmt = $this->conn->prepare("SELECT u.id, u.password, u.username, r.name FROM users u JOIN roles r ON u.id_role = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['name'];
            $_SESSION['username'] = $user['username'];

            // Instantiate the appropriate object based on the role
            if ($user['name'] === 'admin') {
                $admin = new Admin($user['id'], $user['username'], $email, $this->conn);
                $_SESSION['user_obj'] = $admin; // Store admin object in session
                header("Location: ../admin/admin.php");
            } else {
                $normalUser = new User($user['id'], $user['username'], $email, $user['name'], $this->conn);
                $_SESSION['user_obj'] = $normalUser; // Store user object in session
                header("Location: ../Profile/profile.php");
            }
            exit;
        } else {
            return "Invalid email or password.";
        }
    }
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password)) {
        $login = new Login($conn);
        $error_message = $login->authenticate($email, $password);
    } else {
        $error_message = "Please enter a valid email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-500 to-blue-700 text-white">
