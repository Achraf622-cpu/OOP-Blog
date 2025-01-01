<?php
require '../conexions/connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../conexions/login.php");
    exit;
}


echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "Role: " . $_SESSION['role'] . "<br>";

// $user_id = $_SESSION['user_id'];
// $role = $_SESSION['role']; hado li 5rjo 3lia fi kolchiiiiiiiiiiiiiiiiiiiii


if ($_SESSION['role'] == 'admin') { 
    echo "Redirecting to admin profile...";
    header("Location: ../admin/admin.php");
    exit;
} else {
    echo "Redirecting to user profile...";
    header("Location: profile.php");
    exit;
}
?>
