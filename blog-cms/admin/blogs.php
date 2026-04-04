<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../config.php';
requireLogin();

$pageTitle = 'All Blogs';
$currentPage = 'blogs';

// Filters
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($statusFilter && in_array($statusFilter, ['draft', 'pending', 'published', 'scheduled'])) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}
if ($search !== '') {
    $where[] = "(p.title LIKE ? OR p.slug LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalRows = $pdo->prepare("SELECT COUNT(*) FROM blog_posts p {$whereSQL}");
$totalRows->execute($params);
$total = $totalRows->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

$stmt = $pdo->prepare("SELECT p.*, a.name as author_name
    FROM blog_posts p
    LEFT JOIN blog_authors a ON p.author_id = a.id
    {$whereSQL}
    ORDER BY p.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$csrf = generateCSRFToken();

include 'layout-header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div class="d-flex flex-wrap gap-2">
        <a href="blogs.php" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">All (<?= $total ?>)</a>
        <a href="blogs.php?status=published" class="btn btn-sm <?= $statusFilter === 'published' ? 'btn-success' : 'btn-outline-secondary' ?>">Published</a>
        <a href="blogs.php?status=draft" class="btn btn-sm <?= $statusFilter === 'draft' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Draft</a>
        <a href="blogs.php?status=scheduled" class="btn btn-sm <?= $statusFilter === 'scheduled' ? 'btn-info' : 'btn-outline-secondary' ?>">Scheduled</a>
        <a href="blogs.php?status=pending" class="btn btn-sm <?= $statusFilter === 'pending' ? 'btn-warning' : 'btn-outline-secondary' ?>">Pending</a>
    </div>
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex gap-2">
            <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search posts..." value="<?= e($search) ?>" style="width:200px">
            <button class="btn btn-sm btn-outline-primary" type="submit"><i class="las la-search"></i></button>
        </form>
        <a href="blog-editor.php" class="btn btn-sm btn-primary"><i class="las la-plus"></i> New Post</a>
    </div>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        Blog post deleted successfully.
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($posts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="las la-search" style="font-size:48px"></i>
                <p class="mt-2">No posts found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="width:150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($posts as $i => $post): ?>
                        <tr>
                            <td class="text-muted"><?= $offset + $i + 1 ?></td>
                            <td>
                                <a href="blog-editor.php?id=<?= $post['id'] ?>" class="fw-medium text-decoration-none"><?= e($post['title']) ?></a>
                                <br><small class="text-muted"><?= e($post['slug']) ?></small>
                            </td>
                            <td class="text-muted"><?= e($post['author_name'] ?? '—') ?></td>
                            <td>
                                <span class="badge bg-<?= $post['status'] === 'published' ? 'success' : ($post['status'] === 'draft' ? 'secondary' : ($post['status'] === 'scheduled' ? 'info' : 'warning')) ?>">
                                    <?= e(ucfirst($post['status'])) ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= e($post['publish_date'] ?? date('Y-m-d', strtotime($post['created_at']))) ?></td>
                            <td>
                                <a href="blog-editor.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="las la-edit"></i></a>
                                <?php if ($post['status'] === 'published'): ?>
                                    <a href="<?= e(BLOG_URL . '/' . $post['slug']) ?>" class="btn btn-sm btn-outline-success" target="_blank" title="View"><i class="las la-external-link-alt"></i></a>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger delete-post-btn" data-id="<?= $post['id'] ?>" data-title="<?= e($post['title']) ?>" title="Delete"><i class="las la-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?><?= $statusFilter ? '&status=' . e($statusFilter) : '' ?><?= $search ? '&q=' . e($search) : '' ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Delete Post</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <p>Are you sure you want to delete "<strong id="deletePostTitle"></strong>"?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="../api/delete-blog.php" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="post_id" id="deletePostId">
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.delete-post-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('deletePostTitle').textContent = this.dataset.title;
        document.getElementById('deletePostId').value = this.dataset.id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});
</script>

<?php include 'layout-footer.php'; ?>
