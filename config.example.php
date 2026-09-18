<?php
/**
 * Copy to ignored config.php, or require this file from a private wrapper.
 * Production: SIAL_DB_HOST, SIAL_DB_PORT, SIAL_DB_NAME, SIAL_DB_USER,
 * SIAL_DB_PASS, SIAL_DB_CHARSET; SIAL_SITE_URL defaults to the public origin.
 * Staging: SIAL_STAGING=1, SIAL_SITE_URL, SIAL_CMS_FILE; optional
 * SIAL_SESSION_DIR. SQLite and sessions must remain outside the document root.
 * Authentication belongs to the hosting/staging prepend, not this file.
 */

if (!defined('SIAL_STAGING')) {
    define('SIAL_STAGING', in_array(strtolower((string) getenv('SIAL_STAGING')), ['1', 'true', 'yes', 'on'], true));
}

function sial_config_error(string $reason): never
{
    error_log('Site configuration: ' . $reason);
    http_response_code(500);
    exit('<div style="font-family:sans-serif;padding:2rem;">We are experiencing a temporary problem. Please try again shortly.</div>');
}

function sial_private_path(string $path, bool $directory = false): string
{
    $resolved = $path !== '' && $path[0] === '/' ? realpath($path) : false;
    if ($resolved === false || ($directory ? !is_dir($resolved) : !is_file($resolved))) {
        sial_config_error('A required private file or directory is unavailable.');
    }
    foreach ([__DIR__, $_SERVER['DOCUMENT_ROOT'] ?? ''] as $root) {
        $root = $root !== '' ? realpath($root) : false;
        if ($root !== false && ($resolved === $root || str_starts_with($resolved, rtrim($root, '/') . '/'))) {
            sial_config_error('Private storage must be outside the document root.');
        }
    }
    return $resolved;
}

$configuredSiteUrl = getenv('SIAL_SITE_URL');
if ($configuredSiteUrl === false || $configuredSiteUrl === '') {
    if (SIAL_STAGING) {
        sial_config_error('Staging requires an explicit site origin.');
    }
    $configuredSiteUrl = 'https://sialsourcing.com';
}
$siteOrigin = rtrim($configuredSiteUrl, '/');
$siteParts = parse_url($siteOrigin);
if (!is_array($siteParts)
    || !in_array($siteParts['scheme'] ?? '', ['http', 'https'], true)
    || empty($siteParts['host'])
    || isset($siteParts['user'], $siteParts['pass'])
    || isset($siteParts['user']) || isset($siteParts['pass'])
    || isset($siteParts['query']) || isset($siteParts['fragment'])
    || ($siteParts['path'] ?? '') !== ''
    || preg_match('/[\x00-\x20\x7F<>"\x27\\\\]/', $siteOrigin)
    || filter_var($siteOrigin, FILTER_VALIDATE_URL) === false) {
    sial_config_error('The site URL must be a valid HTTP or HTTPS origin.');
}
if (!SIAL_STAGING && ($siteParts['scheme'] ?? '') !== 'https') {
    sial_config_error('The production site origin must use HTTPS.');
}
define('SITE_URL', $siteOrigin);
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('DB_HOST', getenv('SIAL_DB_HOST') ?: 'localhost');
define('DB_NAME', (string) getenv('SIAL_DB_NAME'));
define('DB_USER', (string) getenv('SIAL_DB_USER'));
define('DB_PASS', (string) getenv('SIAL_DB_PASS'));
define('DB_CHARSET', getenv('SIAL_DB_CHARSET') ?: 'utf8mb4');

if (SIAL_STAGING) {
    $noindex = true;
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header('Cache-Control: private, no-store');
    $stagingCmsFile = sial_private_path((string) getenv('SIAL_CMS_FILE'));
    if (!is_readable($stagingCmsFile) || !is_writable($stagingCmsFile) || !is_writable(dirname($stagingCmsFile))) {
        sial_config_error('Staging storage must be readable and writable.');
    }
    define('SIAL_STAGING_CMS_FILE', $stagingCmsFile);
    $stagingSessions = getenv('SIAL_SESSION_DIR') ?: dirname($stagingCmsFile) . '/sessions';
    if (!is_dir($stagingSessions)) {
        $sessionParent = sial_private_path(dirname($stagingSessions), true);
        if ($stagingSessions[0] !== '/' || !is_writable($sessionParent) || !mkdir($stagingSessions, 0700)) {
            sial_config_error('Staging session storage is unavailable.');
        }
    }
    $stagingSessions = sial_private_path($stagingSessions, true);
    if (!is_writable($stagingSessions)) {
        sial_config_error('Staging session storage is not writable.');
    }
    if (session_status() !== PHP_SESSION_NONE) {
        sial_config_error('Configure private staging sessions before starting a session.');
    }
    session_save_path($stagingSessions);
    session_name('SIALSTAGINGSESSID');
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            if (SIAL_STAGING) {
                $pdo = new PDO('sqlite:' . SIAL_STAGING_CMS_FILE, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $pdo->exec('PRAGMA busy_timeout = 5000');
                $pdo->exec('PRAGMA foreign_keys = ON');
            } else {
                $port = getenv('SIAL_DB_PORT') ?: '3306';
                if (DB_NAME === '' || DB_USER === '' || !ctype_digit($port) || (int) $port < 1 || (int) $port > 65535
                    || preg_match('/[;\x00-\x1F]/', DB_HOST . DB_NAME) || !preg_match('/^[a-zA-Z0-9_]+$/D', DB_CHARSET)) {
                    sial_config_error('The database environment is incomplete or invalid.');
                }
                $dsn = 'mysql:host=' . DB_HOST . ';port=' . $port . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }
        } catch (PDOException $error) {
            // Avoid placing credentials, local paths, or driver connection details in output.
            sial_config_error('Database connection failed.');
        }
    }
    return $pdo;
}

function setting(string $key, string $default = ''): string
{
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $cache[$key] = $stmt->fetchColumn() ?: $default;
    }
    return $cache[$key];
}

function slug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function uploadFile(array $file, string $subdir = 'blog'): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)
        return null;
    if (($file['size'] ?? 0) > 5 * 1024 * 1024)
        return null;
    $mimeMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($mimeMap[$mime]))
        return null;
    $filename = bin2hex(random_bytes(8)) . '.' . $mimeMap[$mime];
    $dir = UPLOAD_PATH . $subdir . '/';
    if (!is_dir($dir))
        mkdir($dir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        return UPLOAD_URL . $subdir . '/' . $filename;
    }
    return null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    $expected = $_SESSION['csrf_token'] ?? null;
    $received = $_POST['csrf_token'] ?? null;
    return is_string($expected) && $expected !== '' && is_string($received) && $received !== ''
        && hash_equals($expected, $received);
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
