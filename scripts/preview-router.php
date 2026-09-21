<?php
/** Local-only router: php -S 127.0.0.1:8878 -t . scripts/preview-router.php */
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}
putenv('SIAL_STAGING=1');
if (!defined('SIAL_STAGING')) {
    define('SIAL_STAGING', true);
}
if (SIAL_STAGING !== true) {
    http_response_code(500);
    exit('Local preview requires staging mode.');
}
$noindex = true;
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: private, no-store');
header("Content-Security-Policy: script-src 'self' 'unsafe-inline'; connect-src 'self'");

$documentRoot = dirname(__DIR__);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($requestPath)) {
    http_response_code(400);
    exit('Invalid path.');
}
$path = rawurldecode($requestPath);
if (!str_starts_with($path, '/') || preg_match('/[\x00-\x1F\x7F\\\\]/', $path)
    || preg_match('~(?:^|/)\.[^/]*~', $path)
    || str_contains($path, '//')) {
    http_response_code(404);
    exit('Not found.');
}

// Explicit routes and static extensions prevent private JSON, SQLite, source,
// scripts, tests, configuration, direct includes and admin from being served.
$firstSegment = explode('/', trim($path, '/'))[0] ?? '';
if (in_array(strtolower($firstSegment), ['admin', 'includes', 'scripts', 'tests', 'data', 'docs', 'private', 'catalogue', 'config.php', 'config.example.php'], true)) {
    http_response_code(404);
    exit('Not found.');
}
if ($path === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nDisallow: /\n";
    return true;
}

if (preg_match('~\A/(?:assets/(?:css|js|docs)/[a-zA-Z0-9_.-]+\.(?:css|js|pdf)|uploads/(?:blog|products|team|instruments)/[a-zA-Z0-9_.-]+\.(?:jpe?g|png|webp|gif)|(?:favicon\.(?:png|webp)|sialsourcing-(?:logo\.png|icon\.webp)|og-image\.jpg))\z~D', $path)) {
    $file = realpath($documentRoot . $path);
    if ($file !== false && str_starts_with($file, $documentRoot . '/') && is_file($file)) {
        $types = ['css' => 'text/css; charset=utf-8', 'js' => 'application/javascript; charset=utf-8', 'pdf' => 'application/pdf', 'png' => 'image/png', 'webp' => 'image/webp', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif'];
        header('Content-Type: ' . $types[strtolower(pathinfo($file, PATHINFO_EXTENSION))]);
        header('X-Content-Type-Options: nosniff');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
            readfile($file);
        }
        return true;
    }
    http_response_code(404);
    exit('Not found.');
}

if (preg_match('~\A/knowledge-base(?:/[a-z0-9]+(?:-[a-z0-9]+)*)?/?\z~D', $path)) {
    require $documentRoot . '/knowledge-base.php';
    return true;
}
if (preg_match('~\A/(dental-instruments|surgical-instruments)/([a-z0-9]+(?:-[a-z0-9]+)*)(?:/([a-z0-9]+(?:-[a-z0-9]+)*))?/?\z~D', $path, $route)) {
    // Route identity wins over conflicting user query fields; search/filter
    // parameters remain available to the catalogue controller.
    $_GET['discipline'] = $route[1];
    $_GET['group'] = $route[2];
    $_GET['family'] = $route[3] ?? '';
    require $documentRoot . '/instrument-catalogue.php';
    return true;
}
if (preg_match('~\A/blog/([a-zA-Z0-9-]+)/?\z~D', $path, $route)) {
    $_GET['slug'] = $route[1];
    require $documentRoot . '/blog-single.php';
    return true;
}
if ($path === '/sitemap.xml') {
    require $documentRoot . '/sitemap.php';
    return true;
}

$pages = ['about', 'contact', 'blog', 'faq', 'team', 'solutions', 'lab-qc', 'products',
    'activewear-sports-uniforms', 'surgical-instruments', 'sports-goods', 'leather-goods',
    'cutlery', 'custom-soccer-balls', 'uniforms-tactical-wear', 'dental-instruments',
    'medical-scrubs', 'promotional-soccer-balls', 'security-guard-uniforms', 'resources',
    'for-manufacturers', 'qc-inspection-request'];
$page = trim($path, '/');
if ($page === '' || $page === 'index.php') {
    require $documentRoot . '/index.php';
    return true;
}
if (str_ends_with($page, '.php')) {
    $page = substr($page, 0, -4);
}
if (in_array($page, $pages, true)) {
    require $documentRoot . '/' . $page . '.php';
    return true;
}
require $documentRoot . '/404.php';
return true;
