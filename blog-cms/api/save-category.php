<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../admin/categories.php');
    exit;
}

$categoryId = intval($_POST['category_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($name)) {
    header('Location: ../admin/categories.php');
    exit;
}

if (empty($slug)) {
    $slug = generateSlug($name);
}
$slug = generateSlug($slug);

try {
    if ($categoryId) {
        $stmt = $pdo->prepare("UPDATE blog_categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $description, $categoryId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $description]);
    }
    header('Location: ../admin/categories.php?msg=saved');
} catch (PDOException $e) {
    header('Location: ../admin/categories.php');
}
exit;
