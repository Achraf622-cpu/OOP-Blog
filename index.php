<?php
require './conexions/connect.php';
session_start();


$query = "SELECT a.id, a.titre, a.para, a.img, a.date, u.username AS author 
          FROM articles a 
          JOIN users u ON a.id_users = u.id 
          ORDER BY a.date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$blogs = $stmt->get_result();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_id'], $_POST['comment_text'])) {
    $blog_id = intval($_POST['blog_id']);
    $comment_text = trim($_POST['comment_text']);
    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id && !empty($comment_text)) {
        $stmt = $conn->prepare("INSERT INTO coment (text, id_user, id_article) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $comment_text, $user_id, $blog_id);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">


<nav class="bg-gray-800 p-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="text-2xl font-bold text-blue-400">My Blog</a>
        <ul class="flex space-x-6">
            <li><a href="index.php" class="text-white hover:text-blue-400">Home</a></li>
            <li><a href="./Profile/verification.php" class="text-white hover:text-blue-400">Profile</a></li>
            <li><a href="about.php" class="text-white hover:text-blue-400">About</a></li>
            <li><a href="contact.php" class="text-white hover:text-blue-400">Contact</a></li>
        </ul>
    </div>
</nav>

<div class="min-h-screen p-8">

    <h1 class="text-5xl font-extrabold text-blue-400 mb-10 text-center">Blogs</h1>


    <div class="grid grid-cols-3 gap-6">
        <?php
        if ($blogs->num_rows > 0) {
            while ($blog = $blogs->fetch_assoc()) {

                $stmt = $conn->prepare("SELECT c.text, c.date, u.username 
                                        FROM coment c 
                                        JOIN users u ON c.id_user = u.id 
                                        WHERE c.id_article = ? 
                                        ORDER BY c.date DESC LIMIT 3");
                $stmt->bind_param("i", $blog['id']);
                $stmt->execute();
                $comments = $stmt->get_result();
                ?>
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <img src="<?php echo htmlspecialchars($blog['img']); ?>" alt="Blog Image" class="w-full h-40 object-cover rounded-md mb-4">
                    <h3 class="text-2xl font-bold text-blue-400 mb-2"><?php echo htmlspecialchars($blog['titre']); ?></h3>
                    <p class="text-gray-300 mb-4"><?php echo htmlspecialchars(substr($blog['para'], 0, 100)) . '...'; ?></p>
                    <p class="text-gray-400 text-sm mb-4">By: <?php echo htmlspecialchars($blog['author']); ?></p>
                    <p class="text-gray-500 text-sm mb-4">Published on: <?php echo htmlspecialchars($blog['date']); ?></p>


                    <div class="bg-gray-700 p-4 rounded-md mb-4">
                        <h4 class="text-xl font-bold text-white mb-2">Comments</h4>
                        <?php
                        if ($comments->num_rows > 0) {
                            while ($comment = $comments->fetch_assoc()) {
                                ?>
                                <div class="mb-2">
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
                        <a href="blog_details.php?id=<?php echo $blog['id']; ?>" class="text-blue-400 hover:underline text-sm">View All Comments</a>
                    </div>


                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                            <textarea name="comment_text" rows="2" class="w-full text-black p-2 rounded mb-2" placeholder="Add a comment..." required></textarea>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition duration-300">
                                Submit
                            </button>
                        </form>
                    <?php } else { ?>
                        <p class="text-gray-400 text-sm">Login to add a comment.</p>
                    <?php } ?>
                </div>
                <?php
            }
        } else {
            echo "<p class='text-gray-400 col-span-3'>No blogs found.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>
