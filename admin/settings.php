<?php
$pageTitle = 'Site Settings';
require_once __DIR__ . '/includes/auth.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'settings') {
        $fields = ['site_name','site_tagline','contact_email','contact_phone','contact_address',
                   'hero_heading','hero_subheading','stats_clients','stats_manufacturers','stats_countries','stats_years'];
        foreach ($fields as $k) {
            $v = trim($_POST[$k] ?? '');
            $st = db()->prepare("INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?");
            $st->execute([$k,$v,$v]);
        }
        $msg = 'success:Settings saved.';
    } elseif ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $st = db()->prepare("SELECT password FROM admin_users WHERE username=?");
        $st->execute([$_SESSION['admin_user']]);
        $hash = $st->fetchColumn();
        if (strlen($new) < 12 || strlen($new) > 72) {
            $msg = 'error:Use a password between 12 and 72 bytes long.';
        } elseif ($hash && password_verify($current,$hash)) {
            db()->prepare("UPDATE admin_users SET password=? WHERE username=?")->execute([password_hash($new,PASSWORD_DEFAULT),$_SESSION['admin_user']]);
            session_regenerate_id(true);
            $msg = 'success:Password changed.';
        } else {
            $msg = 'error:Current password is incorrect.';
        }
    }
}

$keys = ['site_name','site_tagline','contact_email','contact_phone','contact_address','hero_heading','hero_subheading','stats_clients','stats_manufacturers','stats_countries','stats_years'];
$s = [];
foreach ($keys as $k) $s[$k] = setting($k,'');
[$msgType,$msgText] = $msg ? explode(':',$msg,2) : [null,null];
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($msgText): ?>
<div class="alert alert-<?= $msgType==='success'?'success':'error' ?>"><?= e($msgText) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

<div class="card">
  <div class="card-header"><h2>Site Settings</h2></div>
  <form method="post" style="padding:1.5rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="settings">
    <div class="form-grid">
      <div class="form-group"><label>Site Name</label><input name="site_name" value="<?= e($s['site_name']) ?>"></div>
      <div class="form-group"><label>Tagline</label><input name="site_tagline" value="<?= e($s['site_tagline']) ?>"></div>
      <div class="form-group"><label>Contact Email</label><input type="email" name="contact_email" value="<?= e($s['contact_email']) ?>"></div>
      <div class="form-group"><label>Contact Phone</label><input name="contact_phone" value="<?= e($s['contact_phone']) ?>"></div>
      <div class="form-group form-full"><label>Office Address</label><input name="contact_address" value="<?= e($s['contact_address']) ?>"></div>
      <div class="form-group form-full"><label>Hero Heading</label><input name="hero_heading" value="<?= e($s['hero_heading']) ?>"></div>
      <div class="form-group form-full"><label>Hero Sub-Heading</label><textarea name="hero_subheading" rows="2"><?= e($s['hero_subheading']) ?></textarea></div>
      <div class="form-group"><label>Stat: Clients</label><input name="stats_clients" value="<?= e($s['stats_clients']) ?>"></div>
      <div class="form-group"><label>Stat: Manufacturers</label><input name="stats_manufacturers" value="<?= e($s['stats_manufacturers']) ?>"></div>
      <div class="form-group"><label>Stat: Countries</label><input name="stats_countries" value="<?= e($s['stats_countries']) ?>"></div>
      <div class="form-group"><label>Stat: Years Experience</label><input name="stats_years" value="<?= e($s['stats_years']) ?>"></div>
    </div>
    <div style="margin-top:1.25rem;"><button type="submit" class="btn-save">💾 Save Settings</button></div>
  </form>
</div>

<div>
  <div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header"><h2>Change Password</h2></div>
    <form method="post" style="padding:1.5rem;">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <div class="form-group" style="margin-bottom:1rem;"><label>Current Password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
      <div class="form-group" style="margin-bottom:1.25rem;"><label>New Password</label><input type="password" name="new_password" required minlength="12" maxlength="72" autocomplete="new-password"></div>
      <button type="submit" class="btn-save">🔐 Change Password</button>
    </form>
  </div>
  <div class="card">
    <div class="card-header"><h2>Quick Links</h2></div>
    <div style="padding:1.25rem;display:flex;flex-direction:column;gap:0.75rem;">
      <a href="<?= SITE_URL ?>" target="_blank" class="btn-edit btn-sm" style="text-align:center;">🌐 View Live Site</a>
      <a href="<?= SITE_URL ?>/admin/inbox.php" class="btn-edit btn-sm" style="text-align:center;">📬 Contact Inbox</a>
    </div>
  </div>
</div>

</div>

  </div>
</div>
</body>
</html>
