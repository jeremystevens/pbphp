
<?php
session_start();

require_once 'maintenance_check.php';
require_once 'database.php';

$db = Database::getInstance()->getConnection();

// Fetch public projects with owner info
$public_stmt = $db->prepare("SELECT pr.id, pr.name, pr.description, pr.license_type, pr.is_public, u.username, u.profile_image
                             FROM projects pr
                             JOIN users u ON pr.user_id = u.id
                             WHERE pr.is_public = 1
                             ORDER BY pr.created_at DESC");
$public_stmt->execute();
$public_projects = $public_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's own projects if logged in
$my_projects = [];
if (isset($_SESSION['user_id'])) {
    $mine_stmt = $db->prepare("SELECT pr.id, pr.name, pr.description, pr.license_type, pr.is_public, u.username, u.profile_image
                               FROM projects pr
                               JOIN users u ON pr.user_id = u.id
                               WHERE pr.user_id = ?
                               ORDER BY pr.created_at DESC");
    $mine_stmt->execute([$_SESSION['user_id']]);
    $my_projects = $mine_stmt->fetchAll(PDO::FETCH_ASSOC);
}

$theme = $_COOKIE['theme'] ?? 'dark';
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
?>
<!DOCTYPE html>
<html class="<?= $theme ?>">
<head>
    <title>Projects - PasteForge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }

        function toggleTheme() {
            const html = document.documentElement;
            const newTheme = html.classList.contains('dark') ? 'light' : 'dark';
            html.classList.remove('dark', 'light');
            html.classList.add(newTheme);
            document.cookie = `theme=${newTheme};path=/`;
        }
    </script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen">
    <!-- Modern Navigation Bar -->
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
                        <a href="/?page=projects" class="hover:bg-blue-700 px-3 py-2 rounded">Projects</a>
                        <?php if ($user_id): ?>
                            <a href="/?page=collections" class="hover:bg-blue-700 px-3 py-2 rounded">Collections</a>
                        <?php else: ?>
                            <a href="/?page=about" class="hover:bg-blue-700 px-3 py-2 rounded">About</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($user_id): ?>
                        <!-- Notification Bell -->
                        <a href="/notifications.php" class="relative p-2 rounded hover:bg-blue-700 transition-colors">
                            <i class="fas fa-bell text-lg"></i>
                            <?php
                            // Get unread notification count for navigation
                            $stmt = $db->prepare("SELECT COUNT(*) FROM comment_notifications WHERE user_id = ? AND is_read = 0");
                            $stmt->execute([$user_id]);
                            $nav_unread_notifications = $stmt->fetchColumn();
                            if ($nav_unread_notifications > 0):
                            ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center min-w-[20px] animate-pulse">
                                    <?= $nav_unread_notifications > 99 ? '99+' : $nav_unread_notifications ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <button onclick="toggleTheme()" class="p-2 rounded hover:bg-blue-700">
                        <i class="fas fa-moon"></i>
                    </button>
                    <?php if (!$user_id): ?>
                        <div class="flex items-center space-x-2">
                            <a href="/?page=login" class="flex items-center space-x-2 hover:bg-blue-700 px-3 py-2 rounded">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Login</span>
                            </a>
                            <a href="/?page=signup" class="flex items-center space-x-2 hover:bg-blue-700 px-3 py-2 rounded">
                                <i class="fas fa-user-plus"></i>
                                <span>Sign Up</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 hover:bg-blue-700 px-3 py-2 rounded">
                                <?php
                                $stmt = $db->prepare("SELECT profile_image FROM users WHERE id = ?");
                                $stmt->execute([$user_id]);
                                $user_avatar = $stmt->fetch()['profile_image'];
                                ?>
                                <img src="<?= $user_avatar ?? 'https://www.gravatar.com/avatar/'.md5(strtolower($username)).'?d=mp&s=32' ?>" 
                                     class="w-8 h-8 rounded-full" alt="Profile">
                                <span><?= htmlspecialchars($username) ?></span>
                                <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <!-- Account Group -->
                                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account</div>
                                    <a href="/?page=edit-profile" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-user-edit mr-2"></i> Edit Profile
                                    </a>
                                    <a href="/?page=profile&username=<?= urlencode($username) ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-user mr-2"></i> View Profile
                                    </a>
                                    <a href="/?page=account" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-crown mr-2"></i> Account
                                    </a>
                                    <a href="/?page=settings" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-cog mr-2"></i> Edit Settings
                                    </a>

                                    <hr class="my-1 border-gray-200 dark:border-gray-700">

                                    <!-- Messages Group -->
                                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Messages</div>
                                    <a href="/threaded_messages.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-envelope mr-2"></i> My Messages
                                    </a>

                                    <hr class="my-1 border-gray-200 dark:border-gray-700">

                                    <!-- Tools Group -->
                                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tools</div>
                                    <a href="/project_manager.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-folder-tree mr-2"></i> Projects
                                    </a>
                                    <a href="/following.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-users mr-2"></i> Following
                                    </a>
                                    <a href="/?page=import-export" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-exchange-alt mr-2"></i> Import/Export
                                    </a>

                                    <hr class="my-1 border-gray-200 dark:border-gray-700">

                                    <!-- Logout -->
                                    <a href="/?logout=1" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="pt-16">
        <div class="max-w-6xl mx-auto p-6">
            <?php if (!empty($my_projects)): ?>
                <h2 class="text-2xl font-bold mb-4">My Projects</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($my_projects as $p): ?>
                        <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow hover:border-blue-500 hover:scale-[1.01] transform duration-200">
                            <div class="flex justify-between items-center mb-2">
                                <a href="/project_manager.php?action=view&project_id=<?= $p['id'] ?>" class="text-xl font-semibold text-blue-400 hover:text-blue-300 hover:underline transition">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                                <span class="text-xs px-2 py-1 rounded-full <?= $p['is_public'] ? 'bg-green-600' : 'bg-yellow-600' ?> text-white">
                                    <?= $p['is_public'] ? 'Public' : 'Private' ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-400 mb-3">
                                <?= htmlspecialchars($p['description']) ?: 'No description provided.' ?>
                            </p>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <div>
                                    <i class="fas fa-code-branch mr-1"></i>License: <?= htmlspecialchars($p['license_type'] ?: 'Unspecified') ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h2 class="text-2xl font-bold mt-10 mb-4">Public Projects</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($public_projects as $p): ?>
                    <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow hover:border-blue-500 hover:scale-[1.01] transform duration-200">
                        <div class="flex justify-between items-center mb-2">
                            <a href="/project_manager.php?action=view&project_id=<?= $p['id'] ?>" class="text-xl font-semibold text-blue-400 hover:text-blue-300 hover:underline transition">
                                <?= htmlspecialchars($p['name']) ?>
                            </a>
                            <span class="text-xs px-2 py-1 rounded-full bg-green-600 text-white">Public</span>
                        </div>
                        <p class="text-sm text-gray-400 mb-3">
                            <?= htmlspecialchars($p['description']) ?: 'No description provided.' ?>
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <div>
                                <i class="fas fa-code-branch mr-1"></i>License: <?= htmlspecialchars($p['license_type'] ?: 'Unspecified') ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <img src="<?= $p['profile_image'] ?? 'https://www.gravatar.com/avatar/' . md5(strtolower($p['username'])) . '?d=mp&s=24' ?>" class="w-5 h-5 rounded-full" alt="avatar">
                                <a href="/?page=profile&username=<?= urlencode($p['username']) ?>" class="hover:underline">
                                    <?= htmlspecialchars($p['username']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
