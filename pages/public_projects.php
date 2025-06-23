<?php
session_start();

require_once 'maintenance_check.php';
require_once 'database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT pr.id, pr.name, pr.description, pr.license_type, u.username
                      FROM projects pr
                      JOIN users u ON pr.user_id = u.id
                      WHERE pr.is_public = 1
                      ORDER BY pr.created_at DESC");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$theme = $_COOKIE['theme'] ?? 'dark';
?>
<!DOCTYPE html>
<html class="<?= $theme ?>">
<head>
    <title>Public Projects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">
    <div class="max-w-5xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Public Projects</h1>
        <div class="space-y-6">
            <?php foreach ($projects as $project): ?>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        <a href="project_manager.php?action=view&project_id=<?= $project['id'] ?>">
                            <?= htmlspecialchars($project['name']) ?>
                        </a>
                    </h2>
                    <?php if (!empty($project['description'])): ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            <?= nl2br(htmlspecialchars($project['description'])) ?>
                        </p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                        License: <span class="font-medium"><?= htmlspecialchars($project['license_type'] ?: 'Unspecified') ?></span>
                        • by <a href="?page=profile&username=<?= urlencode($project['username']) ?>" class="hover:underline">
                            <?= htmlspecialchars($project['username']) ?>
                        </a>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
