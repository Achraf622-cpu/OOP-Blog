<?php
require '../conexions/connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../conexions/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = htmlspecialchars($_POST['titre']);
    $para = htmlspecialchars($_POST['para']);
    $tags = htmlspecialchars($_POST['tags']);
    $img = $_FILES['img'];

    if ($img['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($img['type'], $allowed_types)) {
            $img_path = '../uploads/' . basename($img['name']);
            move_uploaded_file($img['tmp_name'], $img_path);
        } else {
            echo "Invalid image type. Only JPG, PNG, and GIF are allowed.";
            exit;
        }
    } else {
        $img_path = null;
    }

    $stmt = $conn->prepare("INSERT INTO articles (titre, para, img, id_users) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $titre, $para, $img_path, $user_id);

    if ($stmt->execute()) {
        $article_id = $stmt->insert_id;

        $tags_array = array_filter(array_map('trim', explode(',', $tags)));
        foreach ($tags_array as $tag) {
            $stmt_tag = $conn->prepare("SELECT id FROM tags WHERE tag = ?");
            $stmt_tag->bind_param("s", $tag);
            $stmt_tag->execute();
            $result_tag = $stmt_tag->get_result();

            if ($result_tag->num_rows == 0) {
                $stmt_insert_tag = $conn->prepare("INSERT INTO tags (tag) VALUES (?)");
                $stmt_insert_tag->bind_param("s", $tag);
                $stmt_insert_tag->execute();
                $tag_id = $stmt_insert_tag->insert_id;
            } else {
                $row_tag = $result_tag->fetch_assoc();
                $tag_id = $row_tag['id'];
            }

            $stmt_tagart = $conn->prepare("INSERT INTO tagart (id_article, id_tag) VALUES (?, ?)");
            $stmt_tagart->bind_param("ii", $article_id, $tag_id);
            $stmt_tagart->execute();
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ? AND id_users = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);

    if ($stmt->execute()) {
        echo "<script>Swal.fire('Deleted!', 'Your article has been deleted.', 'success');</script>";
    } else {
        echo "<script>Swal.fire('Error!', 'There was a problem deleting the article.', 'error');</script>";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
<div class="flex min-h-screen">
    <aside class="w-1/4 bg-gray-800 p-6 border-r border-gray-700">
        <h2 class="text-3xl font-extrabold text-blue-400 mb-6">User Menu</h2>
        <ul class="space-y-6">
            <li><a href="#" class="block text-white hover:text-blue-400 transition duration-300">Profile</a></li>
            <li><a href="../index.php" class="block text-white hover:text-blue-400 transition duration-300">Home</a></li>
            <li><a href="#" class="block text-white hover:text-blue-400 transition duration-300">Navigate Tags</a></li>
        </ul>
    </aside>

    <main class="w-3/4 p-8 bg-gray-900 relative">
        <a href="../conexions/logout.php" class="absolute top-4 right-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Logout</a>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg mb-6">
            <h1 class="text-2xl font-bold text-blue-400">Welcome, <?= htmlspecialchars($username) ?></h1>
            <p>Your recent activities are listed below.</p>
        </div>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg mb-6">
            <h2 class="text-xl font-bold text-blue-400 mb-4">Add New Post</h2>
            <form method="POST" enctype="multipart/form-data">
                <label class="block text-white mb-2">
                    Title:
                    <input type="text" name="titre" placeholder="Enter your post title" class="w-full p-2 rounded bg-gray-700 text-white mt-1" required>
                </label>
                <label class="block text-white mb-2">
                    Description:
                    <textarea name="para" placeholder="Write a description for your post..." class="w-full p-2 rounded bg-gray-700 text-white mt-1" required></textarea>
                </label>
                <label class="block text-white mb-2">
                    Tags (comma-separated):
                    <input type="text" name="tags" placeholder="E.g., coding, design, technology" class="w-full p-2 rounded bg-gray-700 text-white mt-1">
                </label>
                <label class="block text-white mb-4">
                    Upload Image:
                    <input type="file" name="img" class="block w-full text-white mt-1">
                </label>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Submit Post</button>
            </form>
        </div>

        <div class="mt-10">
            <h2 class="text-2xl font-bold text-blue-400 mb-4">Your Blogs</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $stmt = $conn->prepare("SELECT * FROM articles WHERE id_users = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()): ?>
                        <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
                            <h3 class="text-lg font-bold text-blue-400"><?php echo htmlspecialchars($row['titre']); ?></h3>
                            <?php if (!empty($row['img'])): ?>
                                <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="<?php echo htmlspecialchars($row['titre']); ?>" class="w-full rounded-lg mt-4">
                            <?php endif; ?>
                            <p class="text-gray-400 mt-4"><?php echo htmlspecialchars(substr($row['para'], 0, 100)) . '...'; ?></p>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="text-red-600 hover:text-red-800 mt-4 block">Delete Post</a>
                        </div>
                    <?php endwhile;
                else: ?>
                    <p class="text-gray-400">No articles found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
