<?php
// admin/manufacturers.php — manufacturer network applications
$pageTitle = 'Manufacturer Applications';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['set_status'])) {
    $status = in_array($_POST['status'] ?? '', ['new','screening','approved','rejected']) ? $_POST['status'] : 'new';
    db()->prepare("UPDATE manufacturer_applications SET status=? WHERE id=?")->execute([$status,(int)$_POST['id']]);
    header('Location: manufacturers.php', true, 303); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete'])) {
    db()->prepare("DELETE FROM manufacturer_applications WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: manufacturers.php', true, 303); exit;
}

$view = isset($_GET['view']) ? (int)$_GET['view'] : 0;
if ($view) {
    $st = db()->prepare("SELECT * FROM manufacturer_applications WHERE id=?");
    $st->execute([$view]);
    $app = $st->fetch();
}
$apps = db()->query("SELECT * FROM manufacturer_applications ORDER BY submitted_at DESC")->fetchAll();
$badge = fn($s) => ['new'=>'badge-green','screening'=>'badge-gold','approved'=>'badge-green','rejected'=>'badge-gray'][$s] ?? 'badge-gray';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($view && $app): ?>
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2>Application — <?= e($app['company_name']) ?></h2>
    <a href="manufacturers.php" class="btn-edit btn-sm">← Back</a>
  </div>
  <div style="padding:1.5rem;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
      <?php foreach ([['Contact',$app['contact_name']],['Email',$app['email']],['Phone',$app['phone']],['City',$app['city']],['Products',$app['product_categories']],['Certifications',$app['certifications']],['Website',$app['website']],['Submitted',date('d M Y, H:i', strtotime($app['submitted_at']))]] as $f): ?>
      <div><div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;"><?= $f[0] ?></div><div style="font-weight:600;"><?= e($f[1] ?: '—') ?></div></div>
      <?php endforeach; ?>
    </div>
    <?php if ($app['message']): ?>
    <div style="background:#f9fafb;border-radius:8px;padding:1.25rem;border:1px solid #e5e7eb;margin-bottom:1.25rem;">
      <div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.5rem;">Capacity &amp; Notes</div>
      <p style="line-height:1.8;color:#374151;"><?= nl2br(e($app['message'])) ?></p>
    </div>
    <?php endif; ?>
    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
      <form method="post" style="display:flex;gap:0.5rem;align-items:center;">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $app['id'] ?>">
        <select name="status" class="btn-sm" style="border:1px solid #e5e7eb;background:#fff;padding:0.45rem 0.75rem;">
          <?php foreach (['new','screening','approved','rejected'] as $s): ?>
          <option <?= $app['status']===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <button name="set_status" class="btn-save btn-sm">Update Status</button>
      </form>
      <a href="mailto:<?= e($app['email']) ?>?subject=SialSourcing — Manufacturer Application: <?= e($app['company_name']) ?>" class="btn-edit btn-sm">Reply by Email</a>
      <form method="post" style="display:inline;" onsubmit="return confirm('Delete this application?')">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $app['id'] ?>">
        <button name="delete" class="btn-danger">Delete</button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Manufacturer Applications (<?= count($apps) ?>)</h2>
    <span style="font-size:0.82rem;color:#9ca3af;"><?= count(array_filter($apps, fn($a) => $a['status']==='new')) ?> new</span>
  </div>
  <table>
    <thead><tr><th>Company</th><th>City</th><th>Products</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($apps as $a): ?>
      <tr style="<?= $a['status']==='new'?'font-weight:600;':'' ?>">
        <td><?= e($a['company_name']) ?><br><small style="color:#9ca3af;font-weight:400;"><?= e($a['contact_name']) ?> · <?= e($a['email']) ?></small></td>
        <td><?= e($a['city']) ?></td>
        <td><?= e(substr($a['product_categories'],0,40)) ?></td>
        <td><?= date('d M Y', strtotime($a['submitted_at'])) ?></td>
        <td><span class="badge <?= $badge($a['status']) ?>"><?= e(ucfirst($a['status'])) ?></span></td>
        <td><a href="manufacturers.php?view=<?= $a['id'] ?>" class="btn-edit btn-sm">View</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($apps)): ?>
      <tr><td colspan="6" style="text-align:center;padding:2rem;color:#9ca3af;">No applications yet. The form lives at /for-manufacturers.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

  </div>
</div>
</body>
</html>
