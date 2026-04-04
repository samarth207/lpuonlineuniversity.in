<?php
require_once __DIR__ . '/../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

$file = $_FILES['image'];
$type = $_POST['type'] ?? $_GET['type'] ?? 'content';

// Validate MIME type
$allowedTypes = ['image/jpeg', 'image/webp', 'image/png', 'image/gif'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid image type. Use JPG, WebP, PNG, or GIF.']);
    exit;
}

// Validate file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Image too large. Maximum 5MB.']);
    exit;
}

// Determine upload directory
$subDir = ($type === 'content') ? 'content' : (($type === 'author') ? 'authors' : 'blog');
$uploadDir = UPLOAD_DIR . '/' . $subDir;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate safe filename
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'webp', 'png', 'gif'];
if (!in_array($ext, $allowedExts)) $ext = 'jpg';

$filename = uniqid('img_', true) . '.' . $ext;
$filepath = $uploadDir . '/' . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $url = UPLOAD_URL . '/' . $subDir . '/' . $filename;
    echo json_encode([
        'success' => true,
        'location' => $url,
        'url' => $url,
        'filename' => $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save image.']);
}
