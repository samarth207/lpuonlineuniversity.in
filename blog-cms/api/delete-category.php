<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../admin/categories.php');
    exit;
}

$categoryId = intval($_POST['category_id'] ?? 0);
if ($categoryId > 0) {
    $pdo->prepare("DELETE FROM blog_categories WHERE id = ?")->execute([$categoryId]);
}

header('Location: ../admin/categories.php?msg=deleted');
exit;
