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
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-gray-800 w-full max-w-md mx-4 p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-extrabold text-center text-blue-400 mb-6">Login</h2>
        <?php if (!empty($error_message)): ?>
            <div class="mb-4 text-red-500 text-center">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <input type="email" name="email" placeholder="Email" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <input type="password" name="password" placeholder="Password" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-black ">
            </div>
            <button type="submit" class="w-full py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Login
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">Don't have an account? <a href="register.php" class="text-blue-500 hover:text-blue-700">Register here</a></p>
    </div>
</div>
</body>
</html>
