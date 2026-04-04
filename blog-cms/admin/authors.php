<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../config.php';
requireLogin();

$pageTitle = 'Authors';
$currentPage = 'authors';

$authors = $pdo->query("SELECT a.*, COUNT(p.id) as post_count
    FROM blog_authors a
    LEFT JOIN blog_posts p ON a.id = p.author_id
    GROUP BY a.id
    ORDER BY a.name")->fetchAll();

$editAuthor = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM blog_authors WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editAuthor = $stmt->fetch();
}

$csrf = generateCSRFToken();
$msg = $_GET['msg'] ?? '';

include 'layout-header.php';
?>

<?php if ($msg === 'saved'): ?>
    <div class="alert alert-success py-2 alert-dismissible fade show">Author saved. <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'deleted'): ?>
    <div class="alert alert-success py-2 alert-dismissible fade show">Author deleted. <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><?= $editAuthor ? 'Edit Author' : 'Add New Author' ?></h6>
            </div>
            <div class="card-body">
                <form method="POST" action="../api/save-author.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="author_id" value="<?= $editAuthor['id'] ?? 0 ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Author Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= e($editAuthor['name'] ?? '') ?>" placeholder="Full Name">
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label fw-semibold">Bio <span class="text-muted">(Optional)</span></label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"
                                  placeholder="Short author biography"><?= e($editAuthor['bio'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="author_image" class="form-label fw-semibold">Profile Image <span class="text-muted">(Optional)</span></label>
                        <input type="file" class="form-control" id="author_image" name="author_image" accept=".jpg,.jpeg,.webp,.png">
                        <?php if (!empty($editAuthor['image'])): ?>
                            <div class="mt-2">
                                <img src="<?= e(UPLOAD_URL . '/authors/' . $editAuthor['image']) ?>" width="60" height="60"
                                     class="rounded-circle" alt="<?= e($editAuthor['name']) ?>" style="object-fit:cover">
                                <input type="hidden" name="existing_image" value="<?= e($editAuthor['image']) ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="author_page" class="form-label fw-semibold">Author Page URL <span class="text-muted">(Optional)</span></label>
                        <input type="url" class="form-control" id="author_page" name="author_page"
                               value="<?= e($editAuthor['author_page'] ?? '') ?>" placeholder="https://...">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><?= $editAuthor ? 'Update' : 'Add' ?> Author</button>
                        <?php if ($editAuthor): ?>
                            <a href="authors.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($authors)): ?>
                    <div class="text-center py-5 text-muted">No authors yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Author</th><th>Posts</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($authors as $author): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($author['image'])): ?>
                                                <img src="<?= e(UPLOAD_URL . '/authors/' . $author['image']) ?>" width="36" height="36"
                                                     class="rounded-circle" style="object-fit:cover" alt="">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center"
                                                     style="width:36px;height:36px"><i class="las la-user text-secondary"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-medium"><?= e($author['name']) ?></div>
                                                <?php if (!empty($author['bio'])): ?>
                                                    <small class="text-muted"><?= e(substr($author['bio'], 0, 60)) ?><?= strlen($author['bio']) > 60 ? '...' : '' ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $author['post_count'] ?></td>
                                    <td>
                                        <a href="authors.php?edit=<?= $author['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="las la-edit"></i></a>
                                        <form method="POST" action="../api/delete-author.php" class="d-inline"
                                              onsubmit="return confirm('Delete author &quot;<?= e($author['name']) ?>&quot;?')">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="author_id" value="<?= $author['id'] ?>">
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

<?php include 'layout-footer.php'; ?>
