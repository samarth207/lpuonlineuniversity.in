<?php
require_once __DIR__ . '/../blog-cms/config.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: /blog/');
    exit;
}

// Fetch the post
$stmt = $pdo->prepare("SELECT p.*, a.name as author_name, a.bio as author_bio, a.image as author_image, a.author_page
    FROM blog_posts p
    LEFT JOIN blog_authors a ON p.author_id = a.id
    WHERE p.slug = ? AND p.status = 'published' AND (p.publish_date IS NULL OR p.publish_date <= CURDATE())");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 - Post Not Found</title><meta name="robots" content="noindex"></head>';
    echo '<body style="font-family:Inter,sans-serif;text-align:center;padding:100px 20px">';
    echo '<h1>404</h1><p>Blog post not found.</p><a href="/blog/">Back to Blog</a></body></html>';
    exit;
}

// Categories
$catStmt = $pdo->prepare("SELECT c.name, c.slug FROM blog_categories c
    INNER JOIN blog_post_categories pc ON c.id = pc.category_id WHERE pc.post_id = ?");
$catStmt->execute([$post['id']]);
$postCategories = $catStmt->fetchAll();

// Tags
$tagStmt = $pdo->prepare("SELECT tag FROM blog_tags WHERE post_id = ?");
$tagStmt->execute([$post['id']]);
$postTags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);

// TOC
$toc = json_decode($post['toc'] ?? '[]', true) ?: [];

// Related posts (same category, excluding current)
$relatedPosts = [];
if (!empty($postCategories)) {
    $catIds = array_column($postCategories, 'slug');
    $relStmt = $pdo->prepare("SELECT DISTINCT p.id, p.title, p.slug, p.feature_image, p.feature_image_alt, p.publish_date, p.created_at
        FROM blog_posts p
        INNER JOIN blog_post_categories pc ON p.id = pc.post_id
        INNER JOIN blog_categories c ON pc.category_id = c.id AND c.slug IN (" . implode(',', array_fill(0, count($catIds), '?')) . ")
        WHERE p.id != ? AND p.status = 'published'
        ORDER BY p.publish_date DESC LIMIT 3");
    $relStmt->execute(array_merge($catIds, [$post['id']]));
    $relatedPosts = $relStmt->fetchAll();
}

// SEO variables
$metaTitle = $post['meta_title'] ?: $post['title'];
$metaDescription = $post['meta_description'] ?: $post['short_description'];
$canonicalUrl = BLOG_URL . '/' . $post['slug'];
$featureImageUrl = $post['feature_image'] ? UPLOAD_URL . '/blog/' . $post['feature_image'] : '';
$publishDate = $post['publish_date'] ?: date('Y-m-d', strtotime($post['created_at']));
$modifiedDate = $post['last_updated'] ?: $post['created_at'];

// Check for FAQ section in content
$hasFAQ = strpos($post['content'], 'faq-section') !== false;
$faqItems = [];
if ($hasFAQ) {
    preg_match_all('/<div class="faq-item">\s*<h3[^>]*>(.*?)<\/h3>\s*<p>(.*?)<\/p>\s*<\/div>/si', $post['content'], $faqMatches, PREG_SET_ORDER);
    foreach ($faqMatches as $faq) {
        $faqItems[] = ['question' => strip_tags($faq[1]), 'answer' => strip_tags($faq[2])];
    }
}

// Replace lead form markers with actual inline-styled form HTML
$leadFormHtml = '
<div style="background:linear-gradient(135deg,#1e293b 0%,#334155 100%);color:#fff;padding:36px 32px;border-radius:14px;margin:36px 0;text-align:center;position:relative;overflow:hidden;font-family:Inter,sans-serif;">
  <h3 style="color:#fff;margin:0 0 4px;font-size:22px;font-weight:700;">Still Confused? Get Free Expert Guidance</h3>
  <p style="color:#4ade80;font-size:14px;font-weight:600;margin:0 0 22px;">✅ 100% Free</p>
  <form class="blog-lead-form-live" style="display:flex;gap:10px;max-width:640px;margin:0 auto;flex-wrap:wrap;justify-content:center;">
    <input type="text" name="name" placeholder="Your Name" required style="flex:1;min-width:140px;padding:12px 16px;border:1px solid rgba(255,255,255,.18);border-radius:8px;background:rgba(255,255,255,.08);color:#fff;font-size:14px;font-family:inherit;outline:none;">
    <div style="display:flex;flex:1;min-width:180px;gap:0;">
      <select name="country_code" style="width:80px;padding:12px 4px;border:1px solid rgba(255,255,255,.18);border-right:none;border-radius:8px 0 0 8px;background:rgba(255,255,255,.08);color:#fff;font-size:13px;font-family:inherit;outline:none;">
        <option value="+91" selected>🇮🇳 +91</option>
        <option value="+1">🇺🇸 +1</option>
        <option value="+44">🇬🇧 +44</option>
        <option value="+971">🇦🇪 +971</option>
        <option value="+977">🇳🇵 +977</option>
        <option value="+880">🇧🇩 +880</option>
        <option value="+94">🇱🇰 +94</option>
        <option value="+65">🇸🇬 +65</option>
        <option value="+61">🇦🇺 +61</option>
      </select>
      <input type="tel" name="phone" placeholder="Phone Number" required style="flex:1;padding:12px 16px;border:1px solid rgba(255,255,255,.18);border-radius:0 8px 8px 0;background:rgba(255,255,255,.08);color:#fff;font-size:14px;font-family:inherit;outline:none;">
    </div>
    <input type="text" name="course" placeholder="Course" required style="flex:1;min-width:140px;padding:12px 16px;border:1px solid rgba(255,255,255,.18);border-radius:8px;background:rgba(255,255,255,.08);color:#fff;font-size:14px;font-family:inherit;outline:none;">
    <button type="submit" style="background:#f58220;color:#fff;border:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:14px;font-family:inherit;cursor:pointer;white-space:nowrap;">Get Guidance</button>
  </form>
  <p class="lead-form-msg" style="margin-top:14px;font-size:14px;font-weight:600;display:none;"></p>
</div>';

// Replace any blog-lead-form div (however TinyMCE saved it) with the real form
$post['content'] = preg_replace(
    '/<div[^>]*class="[^"]*blog-lead-form[^"]*"[^>]*>.*?<\/div>/si',
    $leadFormHtml,
    $post['content']
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link rel="icon" type="image/png" sizes="32x32" href="../images/lpu-favicon.png">
    <title><?= e($metaTitle) ?></title>
    <meta name="description" content="<?= e(substr($metaDescription, 0, 160)) ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <?php if ($post['focus_keyword']): ?>
    <meta name="keywords" content="<?= e($post['focus_keyword']) ?>">
    <?php endif; ?>

    <!-- Open Graph -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:title" content="<?= e($metaTitle) ?>">
    <meta property="og:description" content="<?= e(substr($metaDescription, 0, 200)) ?>">
    <?php if ($featureImageUrl): ?>
    <meta property="og:image" content="<?= e($featureImageUrl) ?>">
    <meta property="og:image:alt" content="<?= e($post['feature_image_alt'] ?: $post['title']) ?>">
    <?php endif; ?>
    <meta property="og:site_name" content="LPU Online University">
    <meta property="article:published_time" content="<?= e($publishDate) ?>">
    <meta property="article:modified_time" content="<?= e($modifiedDate) ?>">
    <?php if ($post['author_name']): ?>
    <meta property="article:author" content="<?= e($post['author_name']) ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($metaTitle) ?>">
    <meta name="twitter:description" content="<?= e(substr($metaDescription, 0, 200)) ?>">
    <?php if ($featureImageUrl): ?>
    <meta name="twitter:image" content="<?= e($featureImageUrl) ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="assets/css/blog.css">

    <!-- Article Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "<?= e($post['title']) ?>",
        "description": "<?= e($metaDescription) ?>",
        "url": "<?= e($canonicalUrl) ?>",
        <?php if ($featureImageUrl): ?>
        "image": {
            "@type": "ImageObject",
            "url": "<?= e($featureImageUrl) ?>",
            "width": 1200,
            "height": 628
        },
        <?php endif; ?>
        "datePublished": "<?= e($publishDate) ?>",
        "dateModified": "<?= e($modifiedDate) ?>",
        <?php if ($post['author_name']): ?>
        "author": {
            "@type": "Person",
            "name": "<?= e($post['author_name']) ?>"
            <?= $post['author_page'] ? ',"url": "' . e($post['author_page']) . '"' : '' ?>
        },
        <?php endif; ?>
        "publisher": {
            "@type": "Organization",
            "name": "LPU Online University",
            "url": "<?= e(SITE_URL) ?>",
            "logo": {
                "@type": "ImageObject",
                "url": "<?= e(SITE_URL) ?>/images/LPU-Online-Logo.svg"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?= e($canonicalUrl) ?>"
        }
    }
    </script>

    <!-- BreadcrumbList Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Home", "item": "<?= e(SITE_URL) ?>"},
            {"@type": "ListItem", "position": 2, "name": "Blog", "item": "<?= e(BLOG_URL) ?>"},
            {"@type": "ListItem", "position": 3, "name": "<?= e($post['title']) ?>", "item": "<?= e($canonicalUrl) ?>"}
        ]
    }
    </script>

    <?php if (!empty($faqItems)): ?>
    <!-- FAQPage Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            <?php foreach ($faqItems as $i => $faq): ?>
            {
                "@type": "Question",
                "name": "<?= e($faq['question']) ?>",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "<?= e($faq['answer']) ?>"
                }
            }<?= $i < count($faqItems) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
    }
    </script>
    <?php endif; ?>
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

    <!-- Breadcrumb -->
    <div class="post-breadcrumb">
        <div class="post-breadcrumb__inner">
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/blog/">Blog</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= e(substr($post['title'], 0, 50)) ?>...</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Post Content -->
    <article class="post-article">
        <div class="post-article__inner">
            <div class="post-layout">

                <!-- Main Content -->
                <div class="post-main">
                    <!-- Post Header -->
                    <header class="post-header">
                        <?php if ($postCategories): ?>
                            <div class="post-categories">
                                <?php foreach ($postCategories as $cat): ?>
                                    <a href="<?= e(BLOG_URL . '/?category=' . $cat['slug']) ?>" class="post-category-badge"><?= e($cat['name']) ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h1 class="post-title"><?= e($post['title']) ?></h1>

                        <div class="post-meta">
                            <?php if ($post['author_name']): ?>
                                <span class="post-meta__item">
                                    <?php if ($post['author_image']): ?>
                                        <img src="<?= e(UPLOAD_URL . '/authors/' . $post['author_image']) ?>"
                                             alt="<?= e($post['author_name']) ?>" class="post-author-avatar" width="28" height="28">
                                    <?php endif; ?>
                                    <span>By <?= e($post['author_name']) ?></span>
                                </span>
                            <?php endif; ?>
                            <span class="post-meta__item">
                                <i class="las la-calendar"></i> <?= date('F d, Y', strtotime($publishDate)) ?>
                            </span>
                            <?php if ($post['last_updated'] && $post['last_updated'] !== $post['created_at']): ?>
                                <span class="post-meta__item">
                                    <i class="las la-sync"></i> Updated: <?= date('M d, Y', strtotime($post['last_updated'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </header>

                    <!-- Feature Image -->
                    <?php if ($post['feature_image']): ?>
                        <figure class="post-feature-image">
                            <img src="<?= e($featureImageUrl) ?>"
                                 alt="<?= e($post['feature_image_alt'] ?: $post['title']) ?>"
                                 <?= $post['feature_image_title'] ? 'title="' . e($post['feature_image_title']) . '"' : '' ?>
                                 width="1200" height="628" class="post-feature-img" loading="eager">
                        </figure>
                    <?php endif; ?>

                    <!-- Table of Contents -->
                    <?php if (!empty($toc)): ?>
                    <nav class="post-toc" aria-label="Table of Contents">
                        <div class="post-toc__header" id="tocToggle">
                            <h2 class="post-toc__title"><i class="las la-list-ul"></i> Table of Contents</h2>
                            <button type="button" class="post-toc__toggle" aria-label="Toggle table of contents">
                                <i class="las la-angle-down"></i>
                            </button>
                        </div>
                        <ol class="post-toc__list" id="tocList">
                            <?php
                            $prevLevel = 2;
                            foreach ($toc as $item):
                                $level = intval(substr($item['tag'], 1));
                                $indent = ($level - 2) * 20;
                            ?>
                                <li style="padding-left: <?= $indent ?>px" class="toc-level-<?= $level ?>">
                                    <a href="#<?= e($item['id']) ?>"><?= e($item['text']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                    <?php endif; ?>

                    <!-- Blog Content -->
                    <div class="post-content">
                        <?= $post['content'] ?>
                    </div>

                    <!-- Tags -->
                    <?php if (!empty($postTags)): ?>
                    <div class="post-tags">
                        <i class="las la-tags"></i>
                        <?php foreach ($postTags as $tag): ?>
                            <a href="<?= e(BLOG_URL . '/?tag=' . urlencode($tag)) ?>" class="post-tag"><?= e($tag) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Author Box -->
                    <?php if ($post['author_name']): ?>
                    <div class="author-box">
                        <?php if ($post['author_image']): ?>
                            <img src="<?= e(UPLOAD_URL . '/authors/' . $post['author_image']) ?>"
                                 alt="<?= e($post['author_name']) ?>" class="author-box__avatar" width="80" height="80">
                        <?php endif; ?>
                        <div class="author-box__info">
                            <h3 class="author-box__name">
                                <?php if ($post['author_page']): ?>
                                    <a href="<?= e($post['author_page']) ?>"><?= e($post['author_name']) ?></a>
                                <?php else: ?>
                                    <?= e($post['author_name']) ?>
                                <?php endif; ?>
                            </h3>
                            <?php if ($post['author_bio']): ?>
                                <p class="author-box__bio"><?= e($post['author_bio']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Related Posts -->
                    <?php if (!empty($relatedPosts)): ?>
                    <section class="related-posts">
                        <h2 class="related-posts__title">Related Articles</h2>
                        <div class="related-posts__grid">
                            <?php foreach ($relatedPosts as $rel): ?>
                            <a href="<?= e(BLOG_URL . '/' . $rel['slug']) ?>" class="related-card">
                                <?php if ($rel['feature_image']): ?>
                                    <img src="<?= e(UPLOAD_URL . '/blog/' . $rel['feature_image']) ?>"
                                         alt="<?= e($rel['feature_image_alt'] ?: $rel['title']) ?>"
                                         class="related-card__img" loading="lazy" width="300" height="157">
                                <?php endif; ?>
                                <div class="related-card__body">
                                    <h3 class="related-card__title"><?= e($rel['title']) ?></h3>
                                    <span class="related-card__date"><?= date('M d, Y', strtotime($rel['publish_date'] ?: $rel['created_at'])) ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>

                <!-- Sticky Sidebar TOC (desktop) -->
                <?php if (!empty($toc)): ?>
                <aside class="post-sidebar">
                    <div class="post-sidebar__sticky">
                        <h3 class="post-sidebar__title">In This Article</h3>
                        <ol class="post-sidebar__toc">
                            <?php foreach ($toc as $item):
                                $level = intval(substr($item['tag'], 1));
                                $indent = ($level - 2) * 14;
                            ?>
                                <li style="padding-left: <?= $indent ?>px" class="toc-level-<?= $level ?>">
                                    <a href="#<?= e($item['id']) ?>"><?= e($item['text']) ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </aside>
                <?php endif; ?>

            </div>
        </div>
    </article>

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

    <script>
    // TOC Toggle
    var tocToggle = document.getElementById('tocToggle');
    if (tocToggle) {
        tocToggle.addEventListener('click', function() {
            var list = document.getElementById('tocList');
            var icon = this.querySelector('.la-angle-down, .la-angle-up');
            list.classList.toggle('collapsed');
            if (icon) icon.className = list.classList.contains('collapsed') ? 'las la-angle-down' : 'las la-angle-up';
        });
    }

    // Smooth scroll for TOC links
    document.querySelectorAll('.post-toc a, .post-sidebar a').forEach(function(a) {
        a.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.pushState(null, null, this.getAttribute('href'));
            }
        });
    });

    // Highlight active TOC item on scroll
    var tocLinks = document.querySelectorAll('.post-sidebar__toc a');
    if (tocLinks.length) {
        var headings = [];
        tocLinks.forEach(function(link) {
            var id = link.getAttribute('href').substring(1);
            var el = document.getElementById(id);
            if (el) headings.push({ el: el, link: link });
        });

        window.addEventListener('scroll', function() {
            var scrollPos = window.scrollY + 100;
            var active = null;
            headings.forEach(function(h) {
                if (h.el.offsetTop <= scrollPos) active = h;
            });
            tocLinks.forEach(function(l) { l.classList.remove('active'); });
            if (active) active.link.classList.add('active');
        });
    }

    // AJAX submission for inline lead forms
    document.querySelectorAll('.blog-lead-form-live').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = form.querySelector('button');
            var msgEl = form.parentElement.querySelector('.lead-form-msg');
            var nameVal = form.querySelector('input[name="name"]').value.trim();
            var phoneVal = form.querySelector('input[name="phone"]').value.trim();
            var courseVal = form.querySelector('input[name="course"]').value.trim();

            if (!nameVal || !phoneVal || !courseVal) return;

            btn.disabled = true;
            btn.textContent = 'Submitting...';
            if (msgEl) { msgEl.style.display = 'none'; }

            var ccSelect = form.querySelector('select[name="country_code"]');
            var ccVal = ccSelect ? ccSelect.value : '+91';

            var fd = new FormData();
            fd.append('form_type', 'blog_lead');
            fd.append('name', nameVal);
            fd.append('email', nameVal.toLowerCase().replace(/\s+/g, '') + '@blog-lead.local');
            fd.append('phone', ccVal + ' ' + phoneVal);
            fd.append('program', courseVal);
            fd.append('page_source', 'blog: <?= e($post['slug']) ?>');

            fetch('/submit_form.php', {
                method: 'POST',
                body: fd
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (msgEl) {
                    msgEl.style.display = 'block';
                    if (data.success) {
                        msgEl.style.color = '#4ade80';
                        msgEl.textContent = '\u2705 Thank you! Our expert will contact you shortly.';
                        form.reset();
                    } else {
                        msgEl.style.color = '#f87171';
                        msgEl.textContent = '\u274c ' + (data.message || 'Something went wrong.');
                    }
                }
                btn.disabled = false;
                btn.textContent = 'Get Guidance';
            })
            .catch(function() {
                if (msgEl) {
                    msgEl.style.display = 'block';
                    msgEl.style.color = '#f87171';
                    msgEl.textContent = '\u274c Network error. Please try again.';
                }
                btn.disabled = false;
                btn.textContent = 'Get Guidance';
            });
        });
    });
    </script>

</body>
</html>
