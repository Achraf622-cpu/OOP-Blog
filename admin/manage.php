<?php
require '../conexions/connect.php'; // Ensure this file returns a valid PDO connection.
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../conexions/login.php");
    exit;
}

// Handle blog deletion
if (isset($_GET['delete_blog'])) {
    $blog_id = intval($_GET['delete_blog']);
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = :id");
    $stmt->bindParam(':id', $blog_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Filtering and sorting logic
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'recent';

$query = "SELECT a.id, a.titre, a.para, a.img, a.date, u.username AS author 
          FROM articles a 
          JOIN users u ON a.id_users = u.id";

$params = [];
if ($filter_date) {
    $query .= " WHERE DATE(a.date) = :filter_date";
    $params[':filter_date'] = $filter_date;
}

if ($sort_order === 'oldest') {
    $query .= " ORDER BY a.date ASC";
} else {
    $query .= " ORDER BY a.date DESC"; // Default is recent
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blogs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-1/4 bg-gray-800 p-6 border-r border-gray-700">
        <h2 class="text-3xl font-extrabold text-blue-400 mb-6">Admin Panel</h2>
        <ul class="space-y-6">
            <li><a href="admin.php" class="block text-white hover:text-blue-400 transition duration-300">Manage Users</a></li>
            <li><a href="manage.php" class="block text-white hover:text-blue-400 transition duration-300">Manage Blogs</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="w-3/4 p-8 bg-gray-900">
        <div class="text-center mb-10">
            <h1 class="text-5xl font-extrabold text-blue-400 mb-4">Manage Blogs</h1>
            <p class="text-lg text-gray-300">View, filter, and manage blogs</p>
        </div>

        <form method="GET" class="mb-8 text-center flex items-center justify-center gap-4">
            <div>
                <label for="filter_date" class="text-gray-300 mr-4">Filter by Date:</label>
                <input type="date" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>" class="text-black p-2 rounded">
            </div>
            <div>
                <label for="sort_order" class="text-gray-300 mr-4">Sort:</label>
                <select id="sort_order" name="sort_order" class="text-black p-2 rounded">
                    <option value="recent" <?php echo $sort_order === 'recent' ? 'selected' : ''; ?>>Recent</option>
                    <option value="oldest" <?php echo $sort_order === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition duration-300">
                    Apply
                </button>
            </div>
        </form>

        <div class="grid grid-cols-3 gap-6">
            <?php
            if ($result) {
                foreach ($result as $row) {
                    ?>
                    <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                        <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="Blog Image" class="w-full h-40 object-cover rounded-md mb-4">
                        <h3 class="text-2xl font-bold text-blue-400 mb-2"><?php echo htmlspecialchars($row['titre']); ?></h3>
                        <p class="text-gray-300 mb-4"><?php echo htmlspecialchars(substr($row['para'], 0, 100)) . '...'; ?></p>
                        <p class="text-gray-400 text-sm mb-4">By: <?php echo htmlspecialchars($row['author']); ?></p>
                        <p class="text-gray-500 text-sm mb-4">Published on: <?php echo htmlspecialchars($row['date']); ?></p>
                        <div class="flex justify-end">
                            <a href="?delete_blog=<?php echo $row['id']; ?>"
                               class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded transition duration-300"
                               onclick="return confirm('Are you sure you want to delete this blog?');">
                                Delete Blog
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='text-gray-400 col-span-3'>No blogs found.</p>";
            }
            ?>
        </div>
    </main>

</div>

</body>
</html>
