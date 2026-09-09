<?php
// Dynamic XML sitemap — served at /sitemap.xml via .htaccess rewrite.
// Core pages are listed statically; category pages and blog posts come
// from the database, so new content appears here automatically.
require_once __DIR__ . '/config.php';
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';

$core = [
    '/'          => '1.0',
    '/products'  => '0.9',
    '/solutions' => '0.7',
    '/lab-qc'    => '0.7',
    '/about'     => '0.6',
    '/team'      => '0.5',
    '/faq'       => '0.6',
    '/contact'   => '0.8',
    '/blog'      => '0.7',
    '/resources' => '0.6',
    '/for-manufacturers'    => '0.5',
    '/qc-inspection-request' => '0.6',
];
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($core as $path => $priority): ?>
  <url><loc>https://sialsourcing.com<?= $path ?></loc><priority><?= $priority ?></priority></url>
<?php endforeach;

$products = db()->query("SELECT slug, updated_at FROM products WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
foreach ($products as $p): ?>
  <url>
    <loc>https://sialsourcing.com/<?= htmlspecialchars($p['slug']) ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($p['updated_at'] ?? 'now')) ?></lastmod>
    <priority>0.8</priority>
  </url>
<?php endforeach;

$posts = db()->query("SELECT slug, COALESCE(updated_at, published_at, created_at) AS lastmod FROM blog_posts WHERE is_published = 1 ORDER BY published_at DESC")->fetchAll();
foreach ($posts as $p): ?>
  <url>
    <loc>https://sialsourcing.com/blog/<?= htmlspecialchars($p['slug']) ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($p['lastmod'] ?? 'now')) ?></lastmod>
    <priority>0.6</priority>
  </url>
<?php endforeach; ?>
</urlset>
