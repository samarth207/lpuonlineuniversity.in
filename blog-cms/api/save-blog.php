<?php
require_once __DIR__ . '/../config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/blogs.php');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../admin/blog-editor.php?error=Invalid+request');
    exit;
}

$postId = intval($_POST['post_id'] ?? 0);
$action = $_POST['action'] ?? 'save';

// Collect and sanitize inputs
$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$metaTitle = trim($_POST['meta_title'] ?? '');
$metaDescription = trim($_POST['meta_description'] ?? '');
$focusKeyword = trim($_POST['focus_keyword'] ?? '');
$primaryKeyword = trim($_POST['primary_keyword'] ?? '');
$shortDescription = trim($_POST['short_description'] ?? '');
$content = $_POST['content'] ?? '';
$authorId = intval($_POST['author_id'] ?? 0) ?: null;
$status = $_POST['status'] ?? 'draft';
$publishDate = $_POST['publish_date'] ?? null;
$publishTime = $_POST['publish_time'] ?? null;
$categories = $_POST['categories'] ?? [];
$tags = $_POST['tags'] ?? [];
$featureImageAlt = trim($_POST['feature_image_alt'] ?? '');
$featureImageTitle = trim($_POST['feature_image_title'] ?? '');

// Override status if saving as draft
if ($action === 'save_draft') {
    $status = 'draft';
}

// Validate required fields
if (empty($title)) {
    header('Location: ../admin/blog-editor.php?id=' . $postId . '&error=Title+is+required');
    exit;
}
if (empty($focusKeyword)) {
    header('Location: ../admin/blog-editor.php?id=' . $postId . '&error=Focus+keyword+is+required');
    exit;
}

// Validate status
$allowedStatuses = ['draft', 'pending', 'published', 'scheduled'];
if (!in_array($status, $allowedStatuses)) {
    $status = 'draft';
}

// Generate/validate slug
if (empty($slug)) {
    $slug = generateSlug($title);
}
$slug = generateSlug($slug);

// Ensure unique slug
$slugCheck = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ?");
$slugCheck->execute([$slug, $postId]);
if ($slugCheck->fetch()) {
    $slug .= '-' . time();
}

// Process content - add heading IDs for TOC
$content = addHeadingIDs($content);
$toc = generateTOC($content);
$tocJson = json_encode($toc);

// Handle feature image upload
$featureImage = $_POST['existing_image'] ?? '';
if (!empty($_FILES['feature_image']['name']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['feature_image'];
    $allowedTypes = ['image/jpeg', 'image/webp', 'image/png'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        header('Location: ../admin/blog-editor.php?id=' . $postId . '&error=Invalid+image+format.+Use+JPG,+WebP,+or+PNG');
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        header('Location: ../admin/blog-editor.php?id=' . $postId . '&error=Image+too+large.+Max+5MB');
        exit;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'webp', 'png'];
    $ext = strtolower($ext);
    if (!in_array($ext, $allowedExts)) $ext = 'jpg';

    $filename = $slug . '-' . time() . '.' . $ext;
    $uploadPath = UPLOAD_DIR . '/blog/' . $filename;

    if (!is_dir(UPLOAD_DIR . '/blog')) {
        mkdir(UPLOAD_DIR . '/blog', 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Delete old image if exists and different
        if ($featureImage && $featureImage !== $filename) {
            $oldPath = UPLOAD_DIR . '/blog/' . $featureImage;
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $featureImage = $filename;
    }
}

try {
    if ($postId) {
        // Update existing post
        $stmt = $pdo->prepare("UPDATE blog_posts SET
            title = ?, slug = ?, meta_title = ?, meta_description = ?,
            focus_keyword = ?, primary_keyword = ?, short_description = ?,
            content = ?, toc = ?, feature_image = ?, feature_image_alt = ?,
            feature_image_title = ?, author_id = ?, status = ?,
            publish_date = ?, publish_time = ?
            WHERE id = ?");
        $stmt->execute([
            $title, $slug, $metaTitle, $metaDescription,
            $focusKeyword, $primaryKeyword, $shortDescription,
            $content, $tocJson, $featureImage, $featureImageAlt,
            $featureImageTitle, $authorId, $status,
            $publishDate, $publishTime, $postId
        ]);
    } else {
        // Create new post
        $stmt = $pdo->prepare("INSERT INTO blog_posts
            (title, slug, meta_title, meta_description, focus_keyword, primary_keyword,
             short_description, content, toc, feature_image, feature_image_alt,
             feature_image_title, author_id, status, publish_date, publish_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $title, $slug, $metaTitle, $metaDescription, $focusKeyword, $primaryKeyword,
            $shortDescription, $content, $tocJson, $featureImage, $featureImageAlt,
            $featureImageTitle, $authorId, $status, $publishDate, $publishTime
        ]);
        $postId = $pdo->lastInsertId();
    }

    // Update categories
    $pdo->prepare("DELETE FROM blog_post_categories WHERE post_id = ?")->execute([$postId]);
    if (!empty($categories)) {
        $catStmt = $pdo->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
        foreach ($categories as $catId) {
            $catId = intval($catId);
            if ($catId > 0) {
                $catStmt->execute([$postId, $catId]);
            }
        }
    }

    // Update tags
    $pdo->prepare("DELETE FROM blog_tags WHERE post_id = ?")->execute([$postId]);
    if (!empty($tags)) {
        $tagStmt = $pdo->prepare("INSERT INTO blog_tags (post_id, tag) VALUES (?, ?)");
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
                $tagStmt->execute([$postId, $tag]);
            }
        }
    }

    header('Location: ../admin/blog-editor.php?id=' . $postId . '&saved=1');
    exit;

} catch (PDOException $e) {
    header('Location: ../admin/blog-editor.php?id=' . $postId . '&error=Database+error.+Please+try+again.');
    exit;
}
