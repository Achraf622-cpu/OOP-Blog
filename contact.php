<?php
// contact.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">

<nav class="bg-gray-800 p-4 shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="text-2xl font-bold text-blue-400">My Blog</a>
        <ul class="flex space-x-6">
            <li><a href="index.php" class="text-white hover:text-blue-400">Home</a></li>
            <li><a href="about.php" class="text-white hover:text-blue-400">About</a></li>
            <li><a href="contact.php" class="text-white hover:text-blue-400">Contact</a></li>
        </ul>
    </div>
</nav>

<div class="container mx-auto py-16 px-4">
    <h1 class="text-4xl font-extrabold text-blue-400 mb-8 text-center">Contact Us</h1>

    <form action="process_contact.php" method="POST" class="bg-gray-800 p-6 rounded-lg shadow-lg max-w-xl mx-auto">
        <div class="mb-4">
            <label for="name" class="block text-gray-300 mb-2">Name</label>
            <input type="text" id="name" name="name" class="w-full p-2 rounded text-black" required>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-300 mb-2">Email</label>
            <input type="email" id="email" name="email" class="w-full p-2 rounded text-black" required>
        </div>

        <div class="mb-4">
            <label for="subject" class="block text-gray-300 mb-2">Subject</label>
            <input type="text" id="subject" name="subject" class="w-full p-2 rounded text-black">
        </div>

        <div class="mb-4">
            <label for="message" class="block text-gray-300 mb-2">Message</label>
            <textarea id="message" name="message" rows="4" class="w-full p-2 rounded text-black" required></textarea>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">Send Message</button>
    </form>
</div>

</body>
</html>