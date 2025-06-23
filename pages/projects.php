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
?>
<!DOCTYPE html>
<html class="<?= $theme ?>">
<head>
    <title>Projects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-900 text-white">
    <div class="max-w-6xl mx-auto p-6">
        <?php if (!empty($my_projects)): ?>
            <h2 class="text-2xl font-bold mb-4">My Projects</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($my_projects as $p): ?>
                    <div class="bg-gray-800 border border-gray-700 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow hover:border-blue-500 hover:scale-[1.01] transform duration-200">
                        <div class="flex justify-between items-center mb-2">
                            <a href="project_manager.php?action=view&project_id=<?= $p['id'] ?>" class="text-xl font-semibold text-blue-400 hover:text-blue-300 hover:underline transition">
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
                        <a href="project_manager.php?action=view&project_id=<?= $p['id'] ?>" class="text-xl font-semibold text-blue-400 hover:text-blue-300 hover:underline transition">
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
                            <a href="?page=profile&username=<?= urlencode($p['username']) ?>" class="hover:underline">
                                <?= htmlspecialchars($p['username']) ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
