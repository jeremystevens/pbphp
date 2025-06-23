<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'maintenance_check.php';
require_once 'database.php';

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
$theme = $_COOKIE['theme'] ?? 'dark';
?>
<!DOCTYPE html>
<html class="<?= $theme ?>">
<head>
    <title>PasteForge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' };</script>
    <script>
        function initTheme() {
            const stored = localStorage.getItem('theme');
            if (stored) {
                document.documentElement.classList.remove('light', 'dark');
                document.documentElement.classList.add(stored);
            }
        }

        function toggleTheme() {
            const html = document.documentElement;
            const newTheme = html.classList.contains('dark') ? 'light' : 'dark';
            html.classList.remove('dark', 'light');
            html.classList.add(newTheme);
            localStorage.setItem('theme', newTheme);
            document.cookie = `theme=${newTheme};path=/`;
        }

        document.addEventListener('DOMContentLoaded', initTheme);
    </script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen">
<nav class="bg-blue-600 dark:bg-blue-800 text-white shadow-lg fixed w-full z-10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16 items-center">
            <a href="/" class="flex items-center space-x-3">
                <i class="fas fa-paste text-2xl"></i>
                <span class="text-xl font-bold">PasteForge</span>
            </a>

            <button id="navToggle" class="sm:hidden p-2 focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>

            <div id="navMenu" class="hidden sm:flex sm:items-center space-x-4">
                <a href="/" class="hover:bg-blue-700 px-3 py-2 rounded">Home</a>
                <a href="/?page=archive" class="hover:bg-blue-700 px-3 py-2 rounded">Archive</a>
                <a href="/?page=projects" class="px-3 py-2 text-sm font-medium text-white hover:text-blue-300 transition">Projects</a>
                <button onclick="toggleTheme()" class="p-2 rounded hover:bg-blue-700"><i class="fas fa-moon"></i></button>
            </div>
        </div>
    </div>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('navToggle');
        const menu = document.getElementById('navMenu');
        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    });
</script>
<div class="mt-20">

