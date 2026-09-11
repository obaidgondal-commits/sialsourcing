<?php
// admin/includes/auth.php — Include at top of every admin page
require_once __DIR__ . '/../../config.php';

header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow');

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

// Auto-logout after 60 minutes of inactivity
if (isset($_SESSION['admin_last_seen']) && time() - $_SESSION['admin_last_seen'] > 3600) {
    session_unset();
    session_destroy();
    header('Location: ' . ADMIN_URL . '/index.php?timeout=1');
    exit;
}
$_SESSION['admin_last_seen'] = time();

// Central CSRF gate — every admin page includes this file before reading $_POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ok()) {
    http_response_code(400);
    exit('Invalid or expired form token. Go back, refresh the page, and try again.');
}

$adminUser = $_SESSION['admin_user'] ?? 'Admin';

// Forms in this CMS accept scalar fields only. Reject malformed arrays before
// individual handlers call trim(), compare values, or bind them to SQL.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $value) {
        if (!is_string($value)) {
            http_response_code(400);
            exit('Invalid form data. Refresh this page and try again.');
        }
    }
}
