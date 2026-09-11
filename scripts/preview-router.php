<?php
/** Local preview router; not a production front controller. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true) ||
    !in_array($_SERVER['HTTP_HOST'] ?? '', ['127.0.0.1:8765', 'localhost:8765', '[::1]:8765'], true)) {
    http_response_code(403);
    exit('The preview is available on localhost only.');
}
if (function_exists('mail')) {
    http_response_code(503);
    exit('Start preview with php -d disable_functions=mail -S 127.0.0.1:8765 scripts/preview-router.php');
}
// Compatibility with legacy forms. PHP 8 permits a disabled function to be
// redefined. This local implementation never accesses any mail transport.
if (!function_exists('mail')) {
    function mail(mixed ...$arguments): bool { return false; }
}
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
$root = dirname(__DIR__);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if (str_contains($path, "\0") || str_contains($path, '\\') || str_contains($path, '//') || preg_match('~(?:^|/)\.|^/(?:admin|scripts|database|docs|includes|work|outputs|uploads)(?:/|$)|^/(?:config(?:\.example)?|product-template|blog-single|sitemap|default)(?:\.php)?$~i', $path)) {
    http_response_code(404);
    exit('Not found.');
}
require $root . '/config.php';
if (!defined('SIAL_LOCAL_PREVIEW_CONFIG') || SIAL_LOCAL_PREVIEW_CONFIG !== true || APP_ENV !== 'local' || app_env('SIAL_DB_DRIVER') !== 'sqlite') {
    http_response_code(503);
    exit('Run setup-preview.php --local in a dedicated preview checkout first.');
}
$query = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);
$querySuffix = $query === null ? '' : '?' . $query;
if ($path === '/index.php') { header('Location: /' . $querySuffix, true, 301); exit; }
if ($path !== '/' && str_ends_with($path, '/')) { header('Location: ' . rtrim($path, '/') . $querySuffix, true, 301); exit; }
if (preg_match('~^/products/([a-z0-9-]+)$~', $path, $match)) { header('Location: /' . $match[1] . $querySuffix, true, 301); exit; }
if (preg_match('~^/([a-z0-9-]+)\.php$~', $path, $match) && is_file($root . '/' . $match[1] . '.php') && $match[1] !== '404') {
    header('Location: /' . $match[1] . $querySuffix, true, 301); exit;
}
if ($path === '/sitemap.xml') { require $root . '/sitemap.php'; return true; }
if ($path === '/robots.txt') { header('Content-Type: text/plain'); echo "User-agent: *\nDisallow: /\n"; return true; }
if (preg_match('~^/blog/([a-z0-9-]+)$~i', $path, $match)) { $_GET['slug'] = $match[1]; require $root . '/blog-single.php'; return true; }
if ($path === '/') { require $root . '/index.php'; return true; }
if (preg_match('~^/([a-z0-9-]+)$~', $path, $match) && is_file($root . '/' . $match[1] . '.php')) {
    require $root . '/' . $match[1] . '.php'; return true;
}
$staticFile = realpath($root . $path);
if ($staticFile && str_starts_with($staticFile, $root . '/') && is_file($staticFile) &&
    preg_match('~\.(?:css|js|png|jpe?g|webp|gif|svg|ico|woff2?|pdf)$~i', $path)) return false;
http_response_code(404);
require $root . '/404.php';
return true;
