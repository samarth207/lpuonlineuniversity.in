<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../config.php';
requireLogin();

$pageTitle = 'Categories';
$currentPage = 'categories';

$categories = $pdo->query("SELECT c.*, COUNT(pc.post_id) as post_count
    FROM blog_categories c
    LEFT JOIN blog_post_categories pc ON c.id = pc.category_id
    GROUP BY c.id
    ORDER BY c.name")->fetchAll();

$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM blog_categories WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editCat = $stmt->fetch();
}

$csrf = generateCSRFToken();
$msg = $_GET['msg'] ?? '';

include 'layout-header.php';
?>

<?php if ($msg === 'saved'): ?>
    <div class="alert alert-success py-2 alert-dismissible fade show">Category saved. <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'deleted'): ?>
    <div class="alert alert-success py-2 alert-dismissible fade show">Category deleted. <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><?= $editCat ? 'Edit Category' : 'Add New Category' ?></h6>
            </div>
            <div class="card-body">
                <form method="POST" action="../api/save-category.php">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="category_id" value="<?= $editCat['id'] ?? 0 ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= e($editCat['name'] ?? '') ?>" placeholder="e.g., Online Education">
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label fw-semibold">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug"
                               value="<?= e($editCat['slug'] ?? '') ?>" placeholder="auto-generated">
                        <small class="text-muted">Leave empty to auto-generate from name</small>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Optional category description"><?= e($editCat['description'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?= $editCat ? 'Update' : 'Add' ?> Category</button>
                        <?php if ($editCat): ?>
                            <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($categories)): ?>
                    <div class="text-center py-5 text-muted">No categories yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Name</th><th>Slug</th><th>Posts</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="fw-medium"><?= e($cat['name']) ?></td>
                                    <td class="text-muted small"><?= e($cat['slug']) ?></td>
                                    <td><?= $cat['post_count'] ?></td>
                                    <td>
                                        <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="las la-edit"></i></a>
                                        <form method="POST" action="../api/delete-category.php" class="d-inline"
                                              onsubmit="return confirm('Delete &quot;<?= e($cat['name']) ?>&quot;?')">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="las la-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('name').addEventListener('input', function() {
    var slugField = document.getElementById('slug');
    if (!slugField.dataset.edited) {
        slugField.value = this.value.toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/[\s-]+/g, '-').replace(/^-|-$/g, '');
    }
});
document.getElementById('slug').addEventListener('input', function() { this.dataset.edited = '1'; });
</script>

<?php include 'layout-footer.php'; ?>
