<?php
require_once __DIR__ . '/../config.php';

$error  = '';
$notice = isset($_GET['timeout']) ? 'Your session timed out. Please sign in again.' : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Your session expired — please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username && $password) {
            $stmt = db()->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = $user['username'];
                $_SESSION['admin_last_seen'] = time();
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — SialSourcing</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap');
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'DM Sans',sans-serif;background:#08122a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;}
    .box{background:#fff;border-radius:16px;padding:3rem;width:100%;max-width:420px;box-shadow:0 40px 80px rgba(0,0,0,0.3);}
    .logo{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:#08122a;margin-bottom:0.25rem;}
    .logo span{color:#c9a84c}
    .subtitle{color:#6b7280;font-size:0.88rem;margin-bottom:2rem;}
    label{display:block;font-size:0.85rem;font-weight:600;color:#374151;margin-bottom:0.4rem;}
    input{width:100%;padding:0.8rem 1rem;border:1.5px solid #e5e7eb;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:0.95rem;outline:none;transition:border-color 0.2s;margin-bottom:1.25rem;}
    input:focus{border-color:#c9a84c;}
    .btn{width:100%;padding:0.9rem;background:#c9a84c;color:#08122a;border:none;border-radius:8px;font-weight:700;font-size:1rem;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background 0.2s;}
    .btn:hover{background:#e8c96a;}
    .error{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:8px;padding:0.75rem 1rem;font-size:0.88rem;margin-bottom:1.25rem;}
    .note{margin-top:1.5rem;font-size:0.78rem;color:#9ca3af;text-align:center;line-height:1.6;}
  </style>
</head>
<body>
<div class="box">
  <div class="logo">SialSourcing<span>.</span></div>
  <div class="subtitle">Admin Panel — Secure Login</div>
  <?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
  <?php elseif ($notice): ?>
    <div class="error" style="background:#fffbeb;color:#92400e;border-color:#fcd34d;"><?= e($notice) ?></div>
  <?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Username</label>
    <input type="text" name="username" required autocomplete="username" placeholder="admin">
    <label>Password</label>
    <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
    <button type="submit" class="btn">Sign In</button>
  </form>
  <div class="note">Authorized staff only. You can change your password in Settings.</div>
</div>
</body>
</html>
