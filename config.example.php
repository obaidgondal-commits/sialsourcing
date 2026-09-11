<?php
/**
 * Shared runtime, PHP 8.2+ / PDO MySQL (Hostinger).
 * Copy to ignored config.php for production or use scripts/setup-preview.php.
 * Set SIAL_* in the server environment, or load them from a private PHP file
 * outside public_html before requiring this file. Never commit credentials.
 */
declare(strict_types=1);

set_exception_handler(static function (Throwable $error): void {
    // Do not disclose connection strings, submitted data, or stack traces.
    error_log('SialSourcing request failed: ' . get_class($error));
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'SialSourcing configuration or database error. Check the setup documentation.\n');
        exit(1);
    }
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo 'The service is temporarily unavailable. Please try again shortly.';
});

function app_env(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

define('APP_ENV', app_env('SIAL_ENV', 'production'));
if (!in_array(APP_ENV, ['production', 'local'], true)) {
    throw new RuntimeException('Unsupported SIAL_ENV.');
}
if (APP_ENV === 'local' && PHP_SAPI !== 'cli' &&
    (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true))) {
    throw new RuntimeException('Local preview is restricted to localhost.');
}

$origin = rtrim(app_env('SIAL_SITE_URL', 'https://sialsourcing.com'), '/');
$originParts = parse_url($origin);
if (!$originParts || empty($originParts['host']) || isset($originParts['user']) || isset($originParts['pass']) ||
    !empty($originParts['path']) || isset($originParts['query']) || isset($originParts['fragment']) ||
    (APP_ENV === 'production' && ($originParts['scheme'] ?? '') !== 'https') ||
    (APP_ENV === 'local' && (!in_array($originParts['host'], ['127.0.0.1', 'localhost', '[::1]'], true) ||
        !in_array($originParts['scheme'] ?? '', ['http', 'https'], true)))) {
    throw new RuntimeException('SIAL_SITE_URL must be a valid origin.');
}
define('SITE_URL', $origin);
$canonical = rtrim(app_env('SIAL_CANONICAL_URL', 'https://sialsourcing.com'), '/');
$canonicalParts = parse_url($canonical);
if (!filter_var($canonical, FILTER_VALIDATE_URL) || parse_url($canonical, PHP_URL_SCHEME) !== 'https' ||
    isset($canonicalParts['user']) || isset($canonicalParts['pass']) || !empty($canonicalParts['path']) ||
    isset($canonicalParts['query']) || isset($canonicalParts['fragment']) || preg_match('/[\r\n]/', $canonical)) {
    throw new RuntimeException('Invalid canonical URL.');
}
define('CANONICAL_URL', $canonical);
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

$privatePath = app_env('SIAL_PRIVATE_PATH', APP_ENV === 'local' ? __DIR__ . '/.preview' : '');
if ($privatePath === '' || !is_dir($privatePath) || !is_writable($privatePath)) {
    throw new RuntimeException('Create a writable private directory and set SIAL_PRIVATE_PATH.');
}
$privatePath = realpath($privatePath);
$publicRoot = realpath(__DIR__);
if (APP_ENV === 'production' && ($privatePath === $publicRoot || str_starts_with($privatePath, $publicRoot . DIRECTORY_SEPARATOR))) {
    throw new RuntimeException('Production private storage must be outside the document root.');
}
define('PRIVATE_PATH', $privatePath);
date_default_timezone_set('UTC');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    $sessionDir = PRIVATE_PATH . '/sessions';
    if (!is_dir($sessionDir) && !mkdir($sessionDir, 0700, true) && !is_dir($sessionDir)) {
        throw new RuntimeException('Cannot create session storage.');
    }
    session_save_path($sessionDir);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '3600');
    session_name('sial_session');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => str_starts_with(SITE_URL, 'https://'),
        'httponly' => true, 'samesite' => 'Lax',
    ]);
    if (!session_start()) {
        throw new RuntimeException('Session storage is unavailable.');
    }
}

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) return $connection;
    $driver = app_env('SIAL_DB_DRIVER', 'mysql');
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    if ($driver === 'sqlite' && APP_ENV === 'local') {
        $path = app_env('SIAL_DB_PATH', PRIVATE_PATH . '/preview.sqlite');
        if (!is_file($path) || realpath(dirname($path)) !== PRIVATE_PATH) {
            throw new RuntimeException('Run the preview setup first.');
        }
        $connection = new PDO('sqlite:' . $path, null, null, $options);
        $connection->exec('PRAGMA foreign_keys = ON');
        return $connection;
    }
    if ($driver !== 'mysql') throw new RuntimeException('Only MySQL is supported in production.');
    $host = app_env('SIAL_DB_HOST', 'localhost');
    $port = app_env('SIAL_DB_PORT', '3306');
    $name = app_env('SIAL_DB_NAME');
    $user = app_env('SIAL_DB_USER');
    $password = app_env('SIAL_DB_PASSWORD');
    if (!$name || !$user || !$password || !preg_match('/^[a-zA-Z0-9_.-]+$/D', $host) ||
        !preg_match('/^[a-zA-Z0-9_]+$/D', $name) || !ctype_digit($port)) {
        throw new RuntimeException('Provide all MySQL environment variables.');
    }
    $options[PDO::ATTR_EMULATE_PREPARES] = false;
    $connection = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $password, $options);
    return $connection;
}

function setting(string $key, string $default = ''): string
{
    static $values = [];
    if (array_key_exists($key, $values)) {
        return $values[$key] ?? $default;
    }
    $query = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $query->execute([$key]);
    $value = $query->fetchColumn();
    $values[$key] = $value === false || $value === null ? null : (string) $value;
    return $values[$key] ?? $default;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slug(string $text): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    return trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii === false ? $text : $ascii)), '-');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    return isset($_SESSION['csrf_token'], $_POST['csrf_token']) && is_string($_POST['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/** Atomic fixed-window throttle. Use REMOTE_ADDR, never untrusted forwarding headers, in $key. */
function rate_limit(string $key, int $limit, int $seconds): bool
{
    if ($limit < 1 || $seconds < 1) return false;
    $directory = PRIVATE_PATH . '/rate-limits';
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) return false;
    $handle = fopen($directory . '/' . hash('sha256', $key) . '.json', 'c+');
    if (!$handle) return false;
    if (!flock($handle, LOCK_EX)) { fclose($handle); return false; }
    $state = json_decode(stream_get_contents($handle), true);
    $now = time();
    if (!is_array($state) || ($state['expires'] ?? 0) <= $now) $state = ['expires' => $now + $seconds, 'count' => 0];
    $allowed = $state['count'] < $limit;
    if ($allowed) $state['count']++;
    rewind($handle);
    $written = fwrite($handle, json_encode($state, JSON_THROW_ON_ERROR));
    $saved = $written !== false && ftruncate($handle, $written) && fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $allowed && $saved;
}

/** The database is the source of truth; a failed notification must not lose the RFQ. */
function send_notification(string $to, string $subject, string $body, string $replyTo = ''): bool
{
    if (APP_ENV === 'local') return false;
    if (app_env('SIAL_MAIL_ENABLED', '0') !== '1') return false;
    $from = app_env('SIAL_MAIL_FROM');
    foreach ([$to, $from, $replyTo] as $address) {
        if ($address !== '' && (!filter_var($address, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $address))) return false;
    }
    if ($to === '' || $from === '' || preg_match('/[\r\n]/', $subject) || strlen($subject) > 250) return false;
    $headers = ['From' => $from, 'MIME-Version' => '1.0', 'Content-Type' => 'text/plain; charset=UTF-8'];
    if ($replyTo !== '') $headers['Reply-To'] = $replyTo;
    $sent = function_exists('mail') && @mail($to, $subject, $body, $headers);
    if (!$sent) error_log('SialSourcing notification transport failed.');
    return $sent;
}

/** Allow only decoded raster images. Re-encoding discards appended/script/metadata payloads. */
function uploadFile(array $file, string $subdir = 'blog'): ?string
{
    if (!in_array($subdir, ['blog', 'products', 'team'], true) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
        !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name']) ||
        !isset($file['size']) || $file['size'] < 1 || $file['size'] > 5 * 1024 * 1024 || !extension_loaded('gd')) return null;
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime])) return null;
    $dimensions = @getimagesize($file['tmp_name']);
    if (!$dimensions || $dimensions[0] < 1 || $dimensions[1] < 1 || $dimensions[0] * $dimensions[1] > 16000000 ||
        $dimensions[0] > 8000 || $dimensions[1] > 8000 || ($dimensions['mime'] ?? '') !== $mime) return null;
    $image = @imagecreatefromstring(file_get_contents($file['tmp_name']));
    if (!$image) return null;
    $directory = UPLOAD_PATH . $subdir;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) { imagedestroy($image); return null; }
    $name = bin2hex(random_bytes(20)) . '.' . $types[$mime];
    imagesavealpha($image, true);
    $saved = match ($mime) {
        'image/jpeg' => imagejpeg($image, $directory . '/' . $name, 88),
        'image/png' => imagepng($image, $directory . '/' . $name, 7),
        'image/webp' => imagewebp($image, $directory . '/' . $name, 85),
    };
    imagedestroy($image);
    if (!$saved) return null;
    chmod($directory . '/' . $name, 0644);
    return UPLOAD_URL . $subdir . '/' . $name;
}
