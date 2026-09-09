<?php
// admin/inbox.php
$pageTitle = 'Contact Enquiries';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['mark_read'])) {
    db()->prepare("UPDATE contact_submissions SET is_read=1 WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: inbox.php'); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete'])) {
    db()->prepare("DELETE FROM contact_submissions WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: inbox.php'); exit;
}

$view = isset($_GET['view']) ? (int)$_GET['view'] : 0;
if ($view) {
    db()->prepare("UPDATE contact_submissions SET is_read=1 WHERE id=?")->execute([$view]);
    $st = db()->prepare("SELECT * FROM contact_submissions WHERE id=?");
    $st->execute([$view]);
    $msg = $st->fetch();
}

$submissions = db()->query("SELECT * FROM contact_submissions ORDER BY submitted_at DESC")->fetchAll();
?>

<?php if ($view && $msg): ?>
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2>Enquiry from <?= e($msg['name']) ?></h2>
    <a href="inbox.php" class="btn-edit btn-sm">← Back to Inbox</a>
  </div>
  <div style="padding:1.5rem;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
      <div><div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Name</div><div style="font-weight:600;"><?= e($msg['name']) ?></div></div>
      <div><div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Email</div><a href="mailto:<?= e($msg['email']) ?>" style="color:#0369a1;font-weight:600;"><?= e($msg['email']) ?></a></div>
      <div><div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Company</div><div><?= e($msg['company'] ?? '—') ?></div></div>
      <div><div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Phone</div><div><?= e($msg['phone'] ?? '—') ?></div></div>
      <div><div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Subject</div><div><?= e($msg['subject'] ?? '—') ?></div></div>
      <div><div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;">Date</div><div><?= date('d M Y, H:i', strtotime($msg['submitted_at'])) ?></div></div>
    </div>
    <div style="background:#f9fafb;border-radius:8px;padding:1.25rem;border:1px solid #e5e7eb;">
      <div style="font-size:0.75rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.5rem;">Message</div>
      <p style="line-height:1.8;color:#374151;"><?= nl2br(e($msg['message'])) ?></p>
    </div>
    <div style="margin-top:1.25rem;display:flex;gap:0.75rem;">
      <a href="mailto:<?= e($msg['email']) ?>?subject=Re: <?= e($msg['subject']) ?>" class="btn-save" style="display:inline-block;">📧 Reply by Email</a>
      <form method="post" style="display:inline;" onsubmit="return confirm('Delete this enquiry?')">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
        <button name="delete" class="btn-danger">Delete</button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>All Enquiries (<?= count($submissions) ?>)</h2>
    <span style="font-size:0.82rem;color:#9ca3af;"><?= count(array_filter($submissions, fn($s) => !$s['is_read'])) ?> unread</span>
  </div>
  <table>
    <thead><tr><th>Name</th><th>Company</th><th>Product</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($submissions as $s): ?>
      <tr style="<?= !$s['is_read']?'font-weight:600;':'' ?>">
        <td><?= e($s['name']) ?><br><small style="color:#9ca3af;font-weight:400;"><?= e($s['email']) ?></small></td>
        <td><?= e($s['company'] ?? '—') ?></td>
        <td><?= e($s['subject'] ?? '—') ?></td>
        <td><?= date('d M Y', strtotime($s['submitted_at'])) ?></td>
        <td><?= $s['is_read']?'<span class="badge badge-gray">Read</span>':'<span class="badge badge-green">New</span>' ?></td>
        <td style="display:flex;gap:0.5rem;">
          <a href="inbox.php?view=<?= $s['id'] ?>" class="btn-edit btn-sm">View</a>
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <button name="delete" class="btn-danger">Del</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($submissions)): ?>
      <tr><td colspan="6" style="text-align:center;padding:2rem;color:#9ca3af;">No enquiries yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

  </div>
</div>
</body>
</html>
