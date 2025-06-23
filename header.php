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
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen">
<nav class="bg-blue-600 dark:bg-blue-800 text-white shadow-lg fixed w-full z-10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-6">
                <a href="/" class="flex items-center space-x-3">
                    <i class="fas fa-paste text-2xl"></i>
                    <span class="text-xl font-bold">PasteForge</span>
                </a>
                <div class="flex space-x-4">
                    <a href="/" class="hover:bg-blue-700 px-3 py-2 rounded">Home</a>
                    <a href="/?page=archive" class="hover:bg-blue-700 px-3 py-2 rounded">Archive</a>
                    <a href="/?page=projects" class="px-3 py-2 text-sm font-medium text-white hover:text-blue-300 transition">Projects</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<div class="mt-20">

