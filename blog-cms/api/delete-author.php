<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../admin/authors.php');
    exit;
}

$authorId = intval($_POST['author_id'] ?? 0);
if ($authorId > 0) {
    // Delete author image
    $stmt = $pdo->prepare("SELECT image FROM blog_authors WHERE id = ?");
    $stmt->execute([$authorId]);
    $author = $stmt->fetch();
    if ($author && !empty($author['image'])) {
        $imgPath = UPLOAD_DIR . '/authors/' . $author['image'];
        if (file_exists($imgPath)) unlink($imgPath);
    }

    $pdo->prepare("DELETE FROM blog_authors WHERE id = ?")->execute([$authorId]);
}

header('Location: ../admin/authors.php?msg=deleted');
exit;
