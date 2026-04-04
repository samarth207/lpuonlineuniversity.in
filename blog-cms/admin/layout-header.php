<?php
// Admin Layout Header - included by all admin pages
// Expects $pageTitle to be set before including
if (!defined('ADMIN_PAGE')) { die('Direct access not allowed.'); }
$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? 'Blog Admin') ?> - LPU Blog CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <span class="brand-text">Blog CMS</span>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="las la-tachometer-alt"></i> Dashboard
                </a>
                <a href="blogs.php" class="sidebar-link <?= ($currentPage ?? '') === 'blogs' ? 'active' : '' ?>">
                    <i class="las la-file-alt"></i> All Blogs
                </a>
                <a href="blog-editor.php" class="sidebar-link <?= ($currentPage ?? '') === 'editor' ? 'active' : '' ?>">
                    <i class="las la-plus-circle"></i> New Blog
                </a>
                <a href="categories.php" class="sidebar-link <?= ($currentPage ?? '') === 'categories' ? 'active' : '' ?>">
                    <i class="las la-folder"></i> Categories
                </a>
                <a href="authors.php" class="sidebar-link <?= ($currentPage ?? '') === 'authors' ? 'active' : '' ?>">
                    <i class="las la-user-edit"></i> Authors
                </a>
                <hr class="sidebar-divider">
                <a href="<?= e(BLOG_URL) ?>" class="sidebar-link" target="_blank">
                    <i class="las la-external-link-alt"></i> View Blog
                </a>
                <a href="/" class="sidebar-link" target="_blank">
                    <i class="las la-globe"></i> Main Site
                </a>
                <a href="change-password.php" class="sidebar-link <?= ($currentPage ?? '') === 'change-password' ? 'active' : '' ?>">
                    <i class="las la-lock"></i> Change Password
                </a>
                <a href="logout.php" class="sidebar-link text-danger">
                    <i class="las la-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <button class="btn btn-link sidebar-toggle d-lg-none" id="sidebarToggle">
                    <i class="las la-bars"></i>
                </button>
                <h1 class="topbar-title"><?= e($pageTitle ?? 'Dashboard') ?></h1>
                <div class="topbar-user">
                    <span><i class="las la-user-circle"></i> <?= e($_SESSION['admin_username'] ?? 'Admin') ?></span>
                </div>
            </header>
            <div class="admin-content">
