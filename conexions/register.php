<?php
require 'connect.php'; 
require 'Human.php';
require 'User.php';  
session_start();

class Register {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function registerUser($username, $email, $password) {
        // Check if the email already exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return "Email already in use.";
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Set the default role to 'user' (role id = 1)
        $default_role_id = 1;  // '1' is for 'user' role

        // Insert user into the database
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, id_role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password, $default_role_id])) {
            $user_id = $this->conn->lastInsertId();

            // Store user info in session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';

            // Redirect to the profile page
            header("Location: ../Profile/profile.php");
            exit;
        } else {
            return "Error registering user. Please try again.";
        }
    }
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password) && !empty($username)) {
        $register = new Register($conn);
        $error_message = $register->registerUser($username, $email, $password);
    } else {
        $error_message = "Please fill in all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
    <div class="flex min-h-screen justify-center items-center">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg max-w-md w-full">
            <h2 class="text-3xl font-extrabold text-blue-400 text-center mb-6">Create an Account</h2>

            <?php if (!empty($error_message)): ?>
                <div class="mb-4 text-red-500 text-center"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="mb-4">
                    <label for="username" class="block text-white">Username</label>
                    <input type="text" name="username" id="username" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-black">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-white">Email</label>
                    <input type="email" name="email" id="email" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-black">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-white">Password</label>
                    <input type="password" name="password" id="password" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-black">
                </div>

                <button type="submit" class="w-full py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Register
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">Already have an account? <a href="login.php" class="text-blue-500 hover:text-blue-700">Login here</a></p>
        </div>
    </div>
</body>
</html>
