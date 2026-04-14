<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../config.php';
requireLogin();

$currentPage = 'editor';
$editId = intval($_GET['id'] ?? 0);
$post = null;
$postCategories = [];
$postTags = [];

if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$editId]);
    $post = $stmt->fetch();
    if (!$post) {
        header('Location: blogs.php');
        exit;
    }
    $pageTitle = 'Edit Blog Post';

    // Get categories for this post
    $catStmt = $pdo->prepare("SELECT category_id FROM blog_post_categories WHERE post_id = ?");
    $catStmt->execute([$editId]);
    $postCategories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

    // Get tags for this post
    $tagStmt = $pdo->prepare("SELECT tag FROM blog_tags WHERE post_id = ?");
    $tagStmt->execute([$editId]);
    $postTags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $pageTitle = 'New Blog Post';
}

// Fetch all categories and authors
$categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll();
$authors = $pdo->query("SELECT * FROM blog_authors ORDER BY name")->fetchAll();

$csrf = generateCSRFToken();

// Success/error messages
$success = $_GET['saved'] ?? '';
$error = $_GET['error'] ?? '';

include 'layout-header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        Blog post saved successfully! <?php if ($post): ?><a href="<?= e(BLOG_URL . '/' . $post['slug']) ?>" target="_blank">View Post</a><?php endif; ?>
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
        <?= e($error) ?>
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="../api/save-blog.php" enctype="multipart/form-data" id="blogForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
    <input type="hidden" name="post_id" value="<?= $editId ?>">

    <div class="row g-4">
        <!-- Main Content Column -->
        <div class="col-lg-8">

            <!-- Blog Title (H1) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label for="title" class="form-label fw-bold">Blog Title (H1) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg" id="title" name="title" maxlength="200"
                           value="<?= e($post['title'] ?? '') ?>" required placeholder="Enter blog title...">
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">Auto-displayed as H1 on the page</small>
                        <small class="char-count" data-max="70"><span id="titleCount">0</span>/70</small>
                    </div>
                </div>
            </div>

            <!-- Short Description / Excerpt -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label for="short_description" class="form-label fw-bold">Short Description / Excerpt <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="short_description" name="short_description" rows="3"
                              maxlength="250" required placeholder="Brief description shown in blog listing..."><?= e($post['short_description'] ?? '') ?></textarea>
                    <div class="text-end mt-1">
                        <small class="char-count" data-max="250"><span id="descCount">0</span>/250</small>
                    </div>
                </div>
            </div>

            <!-- Blog Content Editor -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label class="form-label fw-bold">Blog Content <span class="text-danger">*</span></label>
                    <textarea id="blog_content" name="content"><?= e($post['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SEO Meta Data -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="las la-search"></i> SEO Meta Data</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label fw-semibold">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" maxlength="60"
                               value="<?= e($post['meta_title'] ?? '') ?>" placeholder="SEO meta title (max 60 chars)">
                        <div class="text-end mt-1">
                            <small class="char-count" data-max="60"><span id="metaTitleCount">0</span>/60</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="meta_description" class="form-label fw-semibold">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3"
                                  maxlength="250" placeholder="SEO meta description (200-250 chars recommended)"><?= e($post['meta_description'] ?? '') ?></textarea>
                        <div class="text-end mt-1">
                            <small class="char-count" data-max="250"><span id="metaDescCount">0</span>/250</small>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="focus_keyword" class="form-label fw-semibold">Focus Keyword <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="focus_keyword" name="focus_keyword" required
                                   value="<?= e($post['focus_keyword'] ?? '') ?>" placeholder="Primary focus keyword">
                        </div>
                        <div class="col-md-6">
                            <label for="primary_keyword" class="form-label fw-semibold">Primary Keyword (Analytics)</label>
                            <input type="text" class="form-control" id="primary_keyword" name="primary_keyword"
                                   value="<?= e($post['primary_keyword'] ?? '') ?>" placeholder="For SEO analytics">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="slug" class="form-label fw-semibold">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text text-muted small"><?= e(BLOG_URL) ?>/</span>
                            <input type="text" class="form-control" id="slug" name="slug"
                                   value="<?= e($post['slug'] ?? '') ?>" placeholder="auto-generated-from-title">
                        </div>
                        <small class="text-muted">Auto-generated from title. Editable. Use lowercase-with-hyphens.</small>
                    </div>

                    <!-- SERP Preview -->
                    <div class="mt-4 p-3 bg-light rounded" id="serpPreview">
                        <small class="text-muted d-block mb-2 fw-semibold">Google Search Preview:</small>
                        <div style="font-family: Arial, sans-serif;">
                            <div style="font-size:20px;color:#1a0dab;line-height:1.3" id="serpTitle">Blog Title Here</div>
                            <div style="font-size:14px;color:#006621;margin:2px 0" id="serpUrl"><?= e(BLOG_URL) ?>/your-blog-slug</div>
                            <div style="font-size:14px;color:#545454;line-height:1.4" id="serpDesc">Meta description will appear here...</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">

            <!-- Publish Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="las la-cog"></i> Publish Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="pending" <?= ($post['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                            <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="scheduled" <?= ($post['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="publish_date" class="form-label fw-semibold">Publish Date</label>
                        <input type="date" class="form-control" id="publish_date" name="publish_date"
                               value="<?= e($post['publish_date'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="publish_time" class="form-label fw-semibold">Publish Time</label>
                        <input type="time" class="form-control" id="publish_time" name="publish_time"
                               value="<?= e($post['publish_time'] ?? date('H:i')) ?>">
                    </div>
                    <?php if ($post): ?>
                        <div class="text-muted small">
                            <div>Created: <?= e($post['created_at']) ?></div>
                            <div>Last Updated: <?= e($post['last_updated']) ?></div>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-grid gap-2">
                        <button type="submit" name="action" value="save" class="btn btn-primary">
                            <i class="las la-save"></i> <?= $editId ? 'Update Post' : 'Save Post' ?>
                        </button>
                        <button type="submit" name="action" value="save_draft" class="btn btn-outline-secondary">
                            <i class="las la-edit"></i> Save as Draft
                        </button>
                    </div>
                </div>
            </div>

            <!-- Feature Image -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="las la-image"></i> Feature Image</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="feature_image" class="form-label fw-semibold">Upload Image <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="feature_image" name="feature_image"
                               accept=".jpg,.jpeg,.webp,.png" <?= !$post ? 'required' : '' ?>>
                        <small class="text-muted">JPG/WebP/PNG. Recommended: 1200x628px</small>
                    </div>
                    <div id="imagePreview" class="mb-3 <?= empty($post['feature_image']) ? 'd-none' : '' ?>">
                        <?php if (!empty($post['feature_image'])): ?>
                            <img src="<?= e(UPLOAD_URL . '/blog/' . $post['feature_image']) ?>" class="img-fluid rounded" alt="Preview"
                                 id="previewImg">
                            <input type="hidden" name="existing_image" value="<?= e($post['feature_image']) ?>">
                        <?php else: ?>
                            <img src="" class="img-fluid rounded" alt="Preview" id="previewImg">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="feature_image_alt" class="form-label fw-semibold">Image Alt Text <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="feature_image_alt" name="feature_image_alt"
                               value="<?= e($post['feature_image_alt'] ?? '') ?>" required placeholder="Descriptive alt text for SEO">
                    </div>
                    <div class="mb-0">
                        <label for="feature_image_title" class="form-label fw-semibold">Image Title <span class="text-muted">(Optional)</span></label>
                        <input type="text" class="form-control" id="feature_image_title" name="feature_image_title"
                               value="<?= e($post['feature_image_title'] ?? '') ?>" placeholder="Image title attribute">
                    </div>
                </div>
            </div>

            <!-- Author -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="las la-user"></i> Author</h6>
                </div>
                <div class="card-body">
                    <select class="form-select" id="author_id" name="author_id">
                        <option value="">Select Author</option>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?= $author['id'] ?>" <?= ($post['author_id'] ?? '') == $author['id'] ? 'selected' : '' ?>>
                                <?= e($author['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($authors)): ?>
                        <small class="text-muted mt-2 d-block">No authors yet. <a href="authors.php">Add one</a></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Categories -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="las la-folder"></i> Categories <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <p class="text-muted small mb-0">No categories yet. <a href="categories.php">Add one</a></p>
                    <?php else: ?>
                        <div class="category-checklist">
                            <?php foreach ($categories as $cat): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]"
                                           value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>"
                                           <?= in_array($cat['id'], $postCategories) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="cat_<?= $cat['id'] ?>"><?= e($cat['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tags -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="las la-tags"></i> Tags</h6>
                </div>
                <div class="card-body">
                    <div class="tags-input-wrapper" id="tagsWrapper">
                        <div id="tagsList" class="d-flex flex-wrap gap-1 mb-2">
                            <?php foreach ($postTags as $tag): ?>
                                <span class="badge bg-secondary tag-badge">
                                    <?= e($tag) ?> <button type="button" class="btn-close btn-close-white ms-1" style="font-size:.5em" onclick="this.parentElement.remove()"></button>
                                    <input type="hidden" name="tags[]" value="<?= e($tag) ?>">
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="tagInput" placeholder="Add a tag...">
                            <button class="btn btn-outline-secondary" type="button" id="addTagBtn">Add</button>
                        </div>
                        <small class="text-muted">Press Enter or click Add</small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<!-- TinyMCE Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#blog_content',
    height: 600,
    menubar: 'file edit view insert format table',
    plugins: 'lists link image table code fullscreen wordcount autolink autoresize searchreplace visualblocks noneditable',
    toolbar: [
        'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor',
        'alignleft aligncenter alignright | bullist numlist | link image table | faqblock addfaqitem | leadform | code fullscreen'
    ],
    toolbar_sticky: true,
    toolbar_sticky_offset: 56,
    verify_html: false,
    valid_elements: '*[*]',
    extended_valid_elements: 'div[class|contenteditable|data-*|style]',
    valid_children: '+body[div],+div[div|h3|p|span]',
    noneditable_class: 'blog-lead-form',
    block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
    image_title: true,
    image_caption: true,
    image_advtab: true,
    link_default_target: '_blank',
    link_target_list: [
        { title: 'Same window', value: '' },
        { title: 'New window', value: '_blank' }
    ],
    automatic_uploads: true,
    images_upload_url: '../api/upload-image.php?type=content',
    images_upload_handler: function(blobInfo, progress) {
        return new Promise(function(resolve, reject) {
            var formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());
            formData.append('type', 'content');

            fetch('../api/upload-image.php', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    resolve(data.location);
                } else {
                    reject(data.message || 'Upload failed');
                }
            })
            .catch(function() { reject('Upload failed'); });
        });
    },
    content_style: 'body { font-family: Inter, sans-serif; font-size: 16px; line-height: 1.8; } ' +
        'h2 { font-size: 24px; margin-top: 32px; } h3 { font-size: 20px; margin-top: 24px; } h4 { font-size: 18px; margin-top: 20px; } ' +
        '.faq-section { background: #f8f9fa; padding: 24px; border-radius: 8px; margin: 24px 0; } ' +
        '.faq-item { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #dee2e6; } ' +
        '.faq-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; } ' +
        'img { max-width: 100%; height: auto; border-radius: 8px; }' +
        'table { border-collapse: collapse; width: 100%; } table td, table th { border: 1px solid #dee2e6; padding: 8px 12px; }' +
        '.blog-lead-form { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #fff; padding: 32px; border-radius: 12px; margin: 32px 0; text-align: center; } ' +
        '.blog-lead-form h3 { color: #fff; margin: 0 0 4px; font-size: 22px; } ' +
        '.blog-lead-form .lead-form-free { color: #4ade80; font-size: 14px; font-weight: 600; margin-bottom: 20px; } ' +
        '.blog-lead-form .lead-form-fields { display: flex; gap: 10px; max-width: 600px; margin: 0 auto; } ' +
        '.blog-lead-form input { flex: 1; padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(255,255,255,0.1); color: #fff; font-size: 14px; } ' +
        '.blog-lead-form button { background: #f58220; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; white-space: nowrap; }',
    setup: function(editor) {
        // Helper: find the existing FAQ section in the editor content
        function findFaqSection() {
            return editor.dom.select('div.faq-section')[0] || null;
        }

        // Helper: count existing FAQ items
        function countFaqItems() {
            var section = findFaqSection();
            if (!section) return 0;
            return editor.dom.select('div.faq-item', section).length;
        }

        // Helper: create a single FAQ item HTML
        function createFaqItemHtml(num) {
            return '<div class="faq-item"><h3>Question ' + num + '?</h3><p>Answer ' + num + '.</p></div>';
        }

        // Insert FAQ Section button — creates new section or adds item to existing one
        editor.ui.registry.addButton('faqblock', {
            text: 'FAQ',
            tooltip: 'Insert FAQ Section (or add item to existing)',
            onAction: function() {
                var existingSection = findFaqSection();
                if (existingSection) {
                    // Add a new FAQ item to the existing section
                    var currentCount = countFaqItems();
                    var newNum = currentCount + 1;
                    var newItem = editor.dom.createFragment(createFaqItemHtml(newNum));
                    existingSection.appendChild(newItem);
                    editor.undoManager.add();
                    editor.nodeChanged();
                } else {
                    // No FAQ section exists — create one with 3 items
                    editor.insertContent(
                        '<div class="faq-section">' +
                        '<h2>Frequently Asked Questions</h2>' +
                        '<div class="faq-item"><h3>Question 1?</h3><p>Answer 1.</p></div>' +
                        '<div class="faq-item"><h3>Question 2?</h3><p>Answer 2.</p></div>' +
                        '<div class="faq-item"><h3>Question 3?</h3><p>Answer 3.</p></div>' +
                        '</div>'
                    );
                }
            }
        });

        // Add FAQ Item button — always appends to the existing FAQ section
        editor.ui.registry.addButton('addfaqitem', {
            text: '+ FAQ Item',
            tooltip: 'Add another FAQ item to the existing section',
            onAction: function() {
                var existingSection = findFaqSection();
                if (existingSection) {
                    var currentCount = countFaqItems();
                    var newNum = currentCount + 1;
                    var newItem = editor.dom.createFragment(createFaqItemHtml(newNum));
                    existingSection.appendChild(newItem);
                    editor.undoManager.add();
                    editor.nodeChanged();
                } else {
                    // No section yet — create one with a single item
                    editor.insertContent(
                        '<div class="faq-section">' +
                        '<h2>Frequently Asked Questions</h2>' +
                        '<div class="faq-item"><h3>Question 1?</h3><p>Answer 1.</p></div>' +
                        '</div>'
                    );
                }
            }
        });

        // Lead Form button — inserts inline lead capture form
        // Only stores a simple marker div (no inputs/buttons which TinyMCE strips)
        // The frontend JS in post.php builds the actual form from this marker
        editor.ui.registry.addButton('leadform', {
            text: 'Lead Form',
            tooltip: 'Insert inline lead capture form',
            onAction: function() {
                editor.insertContent(
                    '<div class="blog-lead-form">' +
                    '<h3>Still Confused? Get Free Expert Guidance</h3>' +
                    '<p class="lead-form-free">\u2705 100% Free</p>' +
                    '<p class="lead-form-placeholder">[Lead Form — Name, Phone, Course fields will appear here on the live blog]</p>' +
                    '</div><p>&nbsp;</p>'
                );
            }
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    var titleInput = document.getElementById('title');
    var slugInput = document.getElementById('slug');
    var slugChanged = <?= $editId ? 'true' : 'false' ?>;

    slugInput.addEventListener('input', function() { slugChanged = true; });

    titleInput.addEventListener('input', function() {
        if (!slugChanged) {
            slugInput.value = this.value.toLowerCase().trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-|-$/g, '');
        }
        updateSERPPreview();
    });

    // Character counters
    function setupCounter(inputId, countId, max) {
        var input = document.getElementById(inputId);
        var counter = document.getElementById(countId);
        if (!input || !counter) return;
        function update() {
            var len = input.value.length;
            counter.textContent = len;
            counter.parentElement.className = 'char-count' + (len > max ? ' text-danger fw-bold' : (len > max * 0.9 ? ' text-warning' : ''));
        }
        input.addEventListener('input', update);
        update();
    }

    setupCounter('title', 'titleCount', 70);
    setupCounter('short_description', 'descCount', 250);
    setupCounter('meta_title', 'metaTitleCount', 60);
    setupCounter('meta_description', 'metaDescCount', 250);

    // SERP Preview
    function updateSERPPreview() {
        var metaTitle = document.getElementById('meta_title').value || titleInput.value || 'Blog Title Here';
        var metaDesc = document.getElementById('meta_description').value ||
                       document.getElementById('short_description').value || 'Meta description will appear here...';
        var slug = slugInput.value || 'your-blog-slug';

        document.getElementById('serpTitle').textContent = metaTitle.substring(0, 60);
        document.getElementById('serpUrl').textContent = '<?= BLOG_URL ?>/' + slug;
        document.getElementById('serpDesc').textContent = metaDesc.substring(0, 160);
    }

    document.getElementById('meta_title').addEventListener('input', updateSERPPreview);
    document.getElementById('meta_description').addEventListener('input', updateSERPPreview);
    slugInput.addEventListener('input', updateSERPPreview);
    document.getElementById('short_description').addEventListener('input', updateSERPPreview);
    updateSERPPreview();

    // Feature image preview
    document.getElementById('feature_image').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('previewImg').src = ev.target.result;
                document.getElementById('imagePreview').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // Tags input
    var tagInput = document.getElementById('tagInput');
    var tagsList = document.getElementById('tagsList');
    var addTagBtn = document.getElementById('addTagBtn');

    function addTag() {
        var val = tagInput.value.trim();
        if (!val) return;

        // Check duplicate
        var existing = tagsList.querySelectorAll('input[name="tags[]"]');
        for (var i = 0; i < existing.length; i++) {
            if (existing[i].value.toLowerCase() === val.toLowerCase()) {
                tagInput.value = '';
                return;
            }
        }

        var badge = document.createElement('span');
        badge.className = 'badge bg-secondary tag-badge';
        badge.innerHTML = escapeHtml(val) +
            ' <button type="button" class="btn-close btn-close-white ms-1" style="font-size:.5em" onclick="this.parentElement.remove()"></button>' +
            '<input type="hidden" name="tags[]" value="' + escapeHtml(val) + '">';
        tagsList.appendChild(badge);
        tagInput.value = '';
    }

    addTagBtn.addEventListener('click', addTag);
    tagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); addTag(); }
    });

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
</script>

<?php include 'layout-footer.php'; ?>
