<?php
/**
 * Blog Sitemap Generator
 * Access: /blog/sitemap.xml
 * Add to robots.txt: Sitemap: https://lpuonlineuniversity.in/blog/sitemap.xml
 */
require_once __DIR__ . '/../blog-cms/config.php';

header('Content-Type: application/xml; charset=UTF-8');

$posts = $pdo->query("SELECT slug, last_updated, publish_date FROM blog_posts
    WHERE status = 'published' AND (publish_date IS NULL OR publish_date <= CURDATE())
    ORDER BY publish_date DESC")->fetchAll();

$categories = $pdo->query("SELECT slug FROM blog_categories")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= e(BLOG_URL) ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
<?php foreach ($posts as $post): ?>
    <url>
        <loc><?= e(BLOG_URL . '/' . $post['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($post['last_updated'] ?: $post['publish_date'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endforeach; ?>
<?php foreach ($categories as $cat): ?>
    <url>
        <loc><?= e(BLOG_URL . '/?category=' . $cat['slug']) ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
<?php endforeach; ?>
</urlset>
