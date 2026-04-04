<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../admin/authors.php');
    exit;
}

$authorId = intval($_POST['author_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$authorPage = trim($_POST['author_page'] ?? '');

if (empty($name)) {
    header('Location: ../admin/authors.php');
    exit;
}

// Handle image upload
$image = $_POST['existing_image'] ?? '';
if (!empty($_FILES['author_image']['name']) && $_FILES['author_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['author_image'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedTypes = ['image/jpeg', 'image/webp', 'image/png'];

    if (in_array($mimeType, $allowedTypes) && $file['size'] <= 2 * 1024 * 1024) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'webp', 'png'])) $ext = 'jpg';

        $filename = 'author_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
        $uploadDir = UPLOAD_DIR . '/authors';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
            if ($image && file_exists($uploadDir . '/' . $image)) {
                unlink($uploadDir . '/' . $image);
            }
            $image = $filename;
        }
    }
}

try {
    if ($authorId) {
        $stmt = $pdo->prepare("UPDATE blog_authors SET name = ?, bio = ?, image = ?, author_page = ? WHERE id = ?");
        $stmt->execute([$name, $bio, $image, $authorPage, $authorId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO blog_authors (name, bio, image, author_page) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $bio, $image, $authorPage]);
    }
    header('Location: ../admin/authors.php?msg=saved');
} catch (PDOException $e) {
    header('Location: ../admin/authors.php');
}
exit;
