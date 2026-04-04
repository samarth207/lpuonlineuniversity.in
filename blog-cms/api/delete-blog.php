<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../admin/blogs.php');
    exit;
}

$postId = intval($_POST['post_id'] ?? 0);
if ($postId <= 0) {
    header('Location: ../admin/blogs.php');
    exit;
}

try {
    // Get image filename to delete
    $stmt = $pdo->prepare("SELECT feature_image FROM blog_posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if ($post && !empty($post['feature_image'])) {
        $imgPath = UPLOAD_DIR . '/blog/' . $post['feature_image'];
        if (file_exists($imgPath)) unlink($imgPath);
    }

    // Delete post (cascade deletes categories and tags)
    $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$postId]);

    header('Location: ../admin/blogs.php?deleted=1');
} catch (PDOException $e) {
    header('Location: ../admin/blogs.php');
}
exit;
