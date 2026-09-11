<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/xml; charset=utf-8');
$origin = defined('CANONICAL_URL') ? CANONICAL_URL : 'https://sialsourcing.com';
$core = ['/', '/products', '/solutions', '/lab-qc', '/about', '/team', '/faq', '/contact', '/blog', '/resources', '/for-manufacturers', '/qc-inspection-request', '/sourcing-regions', '/sialkot-sourcing', '/wazirabad-sourcing', '/faisalabad-sourcing', '/home-textiles'];
$entries = [];
foreach($core as $path) {
    $file = __DIR__.($path==='/'?'/index.php':$path.'.php');
    if (is_file($file)) $entries[$path] = gmdate('Y-m-d', filemtime($file));
}
foreach(db()->query('SELECT slug, updated_at FROM products WHERE is_active=1 ORDER BY sort_order')->fetchAll() as $p) {
    if (!preg_match('/^[a-z0-9-]+$/',$p['slug']) || !is_file(__DIR__.'/'.$p['slug'].'.php')) continue;
    $entries['/'.$p['slug']] = !empty($p['updated_at']) && strtotime($p['updated_at']) ? gmdate('Y-m-d',strtotime($p['updated_at'])) : null;
}
$posts=db()->prepare('SELECT slug, COALESCE(updated_at,published_at,created_at) AS lastmod FROM blog_posts WHERE is_published=1 AND COALESCE(published_at,created_at) <= ? ORDER BY published_at DESC');
$posts->execute([gmdate('Y-m-d H:i:s')]);
foreach($posts->fetchAll() as $p) {
    if (!preg_match('/^[a-zA-Z0-9-]+$/',$p['slug'])) continue;
    $entries['/blog/'.$p['slug']] = !empty($p['lastmod']) && strtotime($p['lastmod']) ? gmdate('Y-m-d',strtotime($p['lastmod'])) : null;
}
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach($entries as $path=>$lastmod): ?><url><loc><?= htmlspecialchars($origin.$path,ENT_XML1|ENT_QUOTES,'UTF-8') ?></loc><?php if($lastmod && $lastmod<=gmdate('Y-m-d')): ?><lastmod><?= $lastmod ?></lastmod><?php endif; ?></url>
<?php endforeach; ?></urlset>
