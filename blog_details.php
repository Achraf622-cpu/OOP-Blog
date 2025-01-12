<?php
require './conexions/connect.php';
session_start();

// Check if the blog ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$blog_id = intval($_GET['id']);

// Fetch the blog details
$query = "SELECT a.id, a.titre, a.para, a.img, a.date, u.username AS author 
          FROM articles a 
          JOIN users u ON a.id_users = u.id 
          WHERE a.id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $blog_id, PDO::PARAM_INT);
$stmt->execute();
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    echo "<p class='text-gray-400'>Blog not found.</p>";
    exit;
}

// Fetch all comments for the blog
$query = "SELECT c.text, c.date, u.username 
          FROM coment c 
          JOIN users u ON c.id_user = u.id 
          WHERE c.id_article = :id 
          ORDER BY c.date DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $blog_id, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    $comment_text = trim($_POST['comment_text']);
    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id && !empty($comment_text)) {
        $stmt = $conn->prepare("INSERT INTO coment (text, id_user, id_article) VALUES (:text, :user_id, :article_id)");
        $stmt->bindParam(':text', $comment_text, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':article_id', $blog_id, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $blog_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['titre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">

<!-- Navbar -->
<nav class="bg-gray-800 p-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="text-2xl font-bold text-blue-400">My Blog</a>
        <ul class="flex space-x-6">
            <li><a href="index.php" class="text-white hover:text-blue-400">Home</a></li>
            <li><a href="profile.php" class="text-white hover:text-blue-400">Profile</a></li>
            <li><a href="about.php" class="text-white hover:text-blue-400">About</a></li>
            <li><a href="contact.php" class="text-white hover:text-blue-400">Contact</a></li>
        </ul>
    </div>
</nav>

<div class="min-h-screen p-8">

    <!-- Blog Details -->
    <div class="max-w-4xl mx-auto bg-gray-800 p-6 rounded-lg shadow-lg">
        <img src="<?php echo htmlspecialchars($blog['img']); ?>" alt="Blog Image" class="w-full h-60 object-cover rounded-md mb-4">
        <h1 class="text-4xl font-bold text-blue-400 mb-4"><?php echo htmlspecialchars($blog['titre']); ?></h1>
        <p class="text-gray-300 mb-4">By: <?php echo htmlspecialchars($blog['author']); ?></p>
        <p class="text-gray-500 text-sm mb-6">Published on: <?php echo htmlspecialchars($blog['date']); ?></p>
        <p class="text-gray-300 leading-relaxed mb-6"><?php echo nl2br(htmlspecialchars($blog['para'])); ?></p>
    </div>

    <!-- Comments Section -->
    <div class="max-w-4xl mx-auto mt-8 bg-gray-800 p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-blue-400 mb-4">Comments</h2>
        <?php
        if ($comments) {
            foreach ($comments as $comment) {
                ?>
                <div class="mb-4">
                    <p class="text-gray-300 text-sm">
                        <span class="text-blue-400 font-bold"><?php echo htmlspecialchars($comment['username']); ?>:</span>
                        <?php echo htmlspecialchars($comment['text']); ?>
                    </p>
                    <p class="text-gray-500 text-xs"><?php echo htmlspecialchars($comment['date']); ?></p>
                </div>
                <?php
            }
        } else {
            echo "<p class='text-gray-400'>No comments yet.</p>";
        }
        ?>

        <!-- Add Comment Form -->
        <?php if (isset($_SESSION['user_id'])) { ?>
            <form method="POST" class="mt-4">
                <textarea name="comment_text" rows="3" class="w-full text-black p-2 rounded mb-2" placeholder="Add a comment..." required></textarea>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition duration-300">
                    Submit
                </button>
            </form>
        <?php } else { ?>
            <p class="text-gray-400 text-sm">Login to add a comment.</p>
        <?php } ?>
    </div>
</div>

</body>
</html>
