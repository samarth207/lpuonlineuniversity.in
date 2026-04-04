<?php
require_once __DIR__ . '/../blog-cms/config.php';

// Get filter parameters
$categorySlug = trim($_GET['category'] ?? '');
$tagFilter = trim($_GET['tag'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = ["p.status = 'published'", "(p.publish_date IS NULL OR p.publish_date <= CURDATE())"];
$params = [];
$pageMetaTitle = 'Blog - LPU Online University';
$pageMetaDesc = 'Read the latest blogs, articles, and guides on online education, career tips, MBA, MCA, BBA, BCA and more from LPU Online University.';

if ($categorySlug) {
    $catCheck = $pdo->prepare("SELECT * FROM blog_categories WHERE slug = ?");
    $catCheck->execute([$categorySlug]);
    $currentCategory = $catCheck->fetch();
    if ($currentCategory) {
        $where[] = "pc.category_id = ?";
        $params[] = $currentCategory['id'];
        $pageMetaTitle = e($currentCategory['name']) . ' - Blog | LPU Online University';
        $pageMetaDesc = $currentCategory['description'] ?: "Browse articles about {$currentCategory['name']} from LPU Online University Blog.";
    }
}

if ($tagFilter) {
    $where[] = "t.tag = ?";
    $params[] = $tagFilter;
    $pageMetaTitle = "Tag: " . e($tagFilter) . " - Blog | LPU Online University";
}

if ($searchQuery) {
    $where[] = "(p.title LIKE ? OR p.short_description LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $pageMetaTitle = "Search: " . e($searchQuery) . " - Blog | LPU Online University";
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$joinSQL = "LEFT JOIN blog_post_categories pc ON p.id = pc.post_id
            LEFT JOIN blog_tags t ON p.id = t.post_id";

$countStmt = $pdo->prepare("SELECT COUNT(DISTINCT p.id) FROM blog_posts p {$joinSQL} {$whereSQL}");
$countStmt->execute($params);
$totalPosts = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalPosts / $perPage));

$stmt = $pdo->prepare("SELECT DISTINCT p.*, a.name as author_name, a.image as author_image
    FROM blog_posts p
    LEFT JOIN blog_authors a ON p.author_id = a.id
    {$joinSQL}
    {$whereSQL}
    ORDER BY p.publish_date DESC, p.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get categories with post counts for sidebar
$categories = $pdo->query("SELECT c.*, COUNT(pc.post_id) as post_count
    FROM blog_categories c
    INNER JOIN blog_post_categories pc ON c.id = pc.category_id
    INNER JOIN blog_posts p ON pc.post_id = p.id AND p.status = 'published'
    GROUP BY c.id
    ORDER BY post_count DESC")->fetchAll();

// Get post categories helper
function getPostCategories($pdo, $postId) {
    $stmt = $pdo->prepare("SELECT c.name, c.slug FROM blog_categories c
        INNER JOIN blog_post_categories pc ON c.id = pc.category_id
        WHERE pc.post_id = ?");
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

$canonicalUrl = BLOG_URL . '/';
if ($categorySlug) $canonicalUrl .= '?category=' . urlencode($categorySlug);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link rel="icon" type="image/png" sizes="32x32" href="../images/lpu-favicon.png">
    <title><?= $pageMetaTitle ?></title>
    <meta name="description" content="<?= e($pageMetaDesc) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:title" content="<?= e($pageMetaTitle) ?>">
    <meta property="og:description" content="<?= e($pageMetaDesc) ?>">
    <meta property="og:site_name" content="LPU Online University">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="assets/css/blog.css">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Blog",
        "name": "LPU Online University Blog",
        "url": "<?= e(BLOG_URL) ?>",
        "description": "<?= e($pageMetaDesc) ?>",
        "publisher": {
            "@type": "Organization",
            "name": "LPU Online University",
            "url": "<?= e(SITE_URL) ?>",
            "logo": "<?= e(SITE_URL) ?>/images/LPU-Online-Logo.svg"
        }
    }
    </script>
</head>
<body>

    <!-- Header -->
    <header class="header" id="header">
        <div class="header__inner">
            <div class="header__logo">
                <a href="/" aria-label="LPU Online University Home">
                    <img src="../images/LPU-Online-Logo.svg" alt="LPU Online University" width="280" height="60">
                </a>
            </div>
            <nav class="header__nav" aria-label="Main Navigation">
                <ul class="header__menu">
                    <li class="header__menu-item"><a href="/" class="header__menu-link">Home</a></li>
                    <li class="header__menu-item"><a href="/blog/" class="header__menu-link text-highlight">Blog</a></li>
                    <li class="header__menu-item"><a href="/#" class="header__menu-link">Programs</a></li>
                </ul>
            </nav>
            <div class="header__actions">
                <a href="tel:9311381814" class="btn-phone" aria-label="Call"><i class="las la-phone"></i> 93113 81814</a>
            </div>
        </div>
    </header>

    <!-- Blog Hero -->
    <section class="blog-hero">
        <div class="blog-hero__inner">
            <nav aria-label="Breadcrumb" class="breadcrumb-nav">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Blog</li>
                </ol>
            </nav>
            <h1 class="blog-hero__title">
                <?php if ($categorySlug && !empty($currentCategory)): ?>
                    <?= e($currentCategory['name']) ?>
                <?php elseif ($tagFilter): ?>
                    Tag: <?= e($tagFilter) ?>
                <?php elseif ($searchQuery): ?>
                    Search Results: "<?= e($searchQuery) ?>"
                <?php else: ?>
                    Blog & Resources
                <?php endif; ?>
            </h1>
            <p class="blog-hero__subtitle"><?= e($pageMetaDesc) ?></p>

            <!-- Search -->
            <form method="GET" class="blog-search-form">
                <input type="text" name="q" placeholder="Search articles..." value="<?= e($searchQuery) ?>" class="blog-search-input">
                <button type="submit" class="blog-search-btn"><i class="las la-search"></i></button>
            </form>
        </div>
    </section>

    <!-- Blog Content -->
    <section class="blog-section">
        <div class="blog-section__inner">
            <div class="blog-grid-wrapper">

                <!-- Posts Grid -->
                <div class="blog-posts-grid">
                    <?php if (empty($posts)): ?>
                        <div class="blog-empty">
                            <i class="las la-file-alt"></i>
                            <p>No articles found.</p>
                            <a href="/blog/">Browse all articles</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post):
                            $postCats = getPostCategories($pdo, $post['id']);
                        ?>
                        <article class="blog-card">
                            <a href="<?= e(BLOG_URL . '/' . $post['slug']) ?>" class="blog-card__image-link">
                                <?php if ($post['feature_image']): ?>
                                    <img src="<?= e(UPLOAD_URL . '/blog/' . $post['feature_image']) ?>"
                                         alt="<?= e($post['feature_image_alt'] ?: $post['title']) ?>"
                                         <?= $post['feature_image_title'] ? 'title="' . e($post['feature_image_title']) . '"' : '' ?>
                                         class="blog-card__image" loading="lazy" width="400" height="210">
                                <?php else: ?>
                                    <div class="blog-card__image-placeholder"><i class="las la-image"></i></div>
                                <?php endif; ?>
                            </a>
                            <div class="blog-card__body">
                                <?php if ($postCats): ?>
                                    <div class="blog-card__cats">
                                        <?php foreach (array_slice($postCats, 0, 2) as $cat): ?>
                                            <a href="<?= e(BLOG_URL . '/?category=' . $cat['slug']) ?>" class="blog-card__cat"><?= e($cat['name']) ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <h2 class="blog-card__title">
                                    <a href="<?= e(BLOG_URL . '/' . $post['slug']) ?>"><?= e($post['title']) ?></a>
                                </h2>
                                <p class="blog-card__excerpt"><?= e(substr($post['short_description'], 0, 150)) ?>...</p>
                                <div class="blog-card__meta">
                                    <?php if ($post['author_name']): ?>
                                        <span class="blog-card__author"><i class="las la-user"></i> <?= e($post['author_name']) ?></span>
                                    <?php endif; ?>
                                    <span class="blog-card__date"><i class="las la-calendar"></i> <?= date('M d, Y', strtotime($post['publish_date'] ?: $post['created_at'])) ?></span>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <aside class="blog-sidebar">
                    <?php if (!empty($categories)): ?>
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget__title">Categories</h3>
                        <ul class="sidebar-categories">
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a href="<?= e(BLOG_URL . '/?category=' . $cat['slug']) ?>"
                                       class="<?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                                        <?= e($cat['name']) ?> <span>(<?= $cat['post_count'] ?>)</span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </aside>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="blog-pagination" aria-label="Blog pagination">
                <?php
                $queryParams = [];
                if ($categorySlug) $queryParams['category'] = $categorySlug;
                if ($tagFilter) $queryParams['tag'] = $tagFilter;
                if ($searchQuery) $queryParams['q'] = $searchQuery;
                ?>
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $page - 1])) ?>" class="pagination-link">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $p])) ?>"
                       class="pagination-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $page + 1])) ?>" class="pagination-link">Next &raquo;</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer__inner">
            <div class="footer__top">
                <div class="footer__logo">
                    <img src="../images/footer-logo.svg" alt="LPU Online University" width="240" height="52">
                </div>
                <div class="footer__contact">
                    <div class="footer__contact-item">
                        <i class="la la-envelope"></i>
                        <div><strong>Email:</strong><p><a href="mailto:admissions@lpuonlineuniversity.in">admissions@lpuonlineuniversity.in</a></p></div>
                    </div>
                    <div class="footer__contact-item">
                        <i class="la la-phone"></i>
                        <div><strong>Admissions:</strong><p><a href="tel:9311381814">93113 81814</a></p></div>
                    </div>
                </div>
            </div>
            <div class="footer__bottom">
                <p class="footer__copyright">&copy; <?= date('Y') ?> Lovely Professional University. All rights reserved.</p>
                <div class="footer__links">
                    <a href="#">Privacy Policy</a><span>|</span><a href="#">Disclaimer</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
