<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../config.php';
requireLogin();

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Get stats
$totalPosts = $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
$published = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status='published'")->fetchColumn();
$drafts = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status='draft'")->fetchColumn();
$scheduled = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status='scheduled'")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
$totalAuthors = $pdo->query("SELECT COUNT(*) FROM blog_authors")->fetchColumn();

// Recent posts
$recentPosts = $pdo->query("SELECT id, title, slug, status, publish_date, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 5")->fetchAll();

include 'layout-header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card bg-primary-subtle">
            <div class="stat-icon"><i class="las la-file-alt"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalPosts ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card bg-success-subtle">
            <div class="stat-icon"><i class="las la-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $published ?></div>
                <div class="stat-label">Published</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card bg-warning-subtle">
            <div class="stat-icon"><i class="las la-edit"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $drafts ?></div>
                <div class="stat-label">Drafts</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card bg-info-subtle">
            <div class="stat-icon"><i class="las la-clock"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $scheduled ?></div>
                <div class="stat-label">Scheduled</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold">Recent Posts</h5>
                <a href="blog-editor.php" class="btn btn-sm btn-primary"><i class="las la-plus"></i> New Post</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentPosts)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="las la-file-alt" style="font-size:48px"></i>
                        <p class="mt-2">No posts yet. <a href="blog-editor.php">Create your first blog post!</a></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Title</th><th>Status</th><th>Date</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentPosts as $post): ?>
                                <tr>
                                    <td class="fw-medium"><?= e($post['title']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $post['status'] === 'published' ? 'success' : ($post['status'] === 'draft' ? 'secondary' : ($post['status'] === 'scheduled' ? 'info' : 'warning')) ?>">
                                            <?= e(ucfirst($post['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= e($post['publish_date'] ?? date('Y-m-d', strtotime($post['created_at']))) ?></td>
                                    <td><a href="blog-editor.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Quick Stats</h5>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Categories</span><strong><?= $totalCategories ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Authors</span><strong><?= $totalAuthors ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Pending Review</span>
                        <strong><?= $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status='pending'")->fetchColumn() ?></strong>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Quick Links</h5>
                <div class="d-grid gap-2">
                    <a href="blog-editor.php" class="btn btn-primary"><i class="las la-plus-circle"></i> New Blog Post</a>
                    <a href="categories.php" class="btn btn-outline-secondary"><i class="las la-folder"></i> Manage Categories</a>
                    <a href="authors.php" class="btn btn-outline-secondary"><i class="las la-user-edit"></i> Manage Authors</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout-footer.php'; ?>
