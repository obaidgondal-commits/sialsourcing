<?php
$pageTitle = 'FAQs';
require_once __DIR__ . '/includes/auth.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id  = (int)($_POST['id'] ?? 0);
        $q   = trim($_POST['question'] ?? '');
        $a   = trim($_POST['answer'] ?? '');
        $cat = trim($_POST['category'] ?? 'General');
        $ord = (int)($_POST['sort_order'] ?? 0);
        if ($id) {
            db()->prepare("UPDATE faqs SET question=?,answer=?,category=?,sort_order=? WHERE id=?")->execute([$q,$a,$cat,$ord,$id]);
        } else {
            db()->prepare("INSERT INTO faqs (question,answer,category,sort_order) VALUES (?,?,?,?)")->execute([$q,$a,$cat,$ord]);
        }
        $msg = 'success:FAQ saved.';
    } elseif ($action === 'delete') {
        db()->prepare("DELETE FROM faqs WHERE id=?")->execute([(int)($_POST['id'] ?? 0)]);
        $msg = 'success:FAQ deleted.';
    } elseif ($action === 'toggle') {
        db()->prepare("UPDATE faqs SET is_active=1-is_active WHERE id=?")->execute([(int)($_POST['id'] ?? 0)]);
        $msg = 'success:Status updated.';
    }
}

$editItem = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare("SELECT * FROM faqs WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $editItem = $st->fetch();
}
$showForm = isset($_GET['new']) || $editItem;
$faqs     = db()->query("SELECT * FROM faqs ORDER BY sort_order,created_at DESC")->fetchAll();
[$msgType,$msgText] = $msg ? explode(':', $msg, 2) : [null,null];
?>

<?php if ($msgText): ?>
<div class="alert alert-<?= $msgType==='success'?'success':'error' ?>"><?= e($msgText) ?></div>
<?php endif; ?>

<?php if ($showForm): ?>
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2><?= $editItem ? 'Edit FAQ' : 'New FAQ' ?></h2>
    <a href="faq.php" class="btn-edit btn-sm">← Back</a>
  </div>
  <form method="post" style="padding:1.5rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editItem['id'] ?? 0 ?>">
    <div class="form-grid">
      <div class="form-group form-full">
        <label>Question *</label>
        <input type="text" name="question" required value="<?= e($editItem['question'] ?? '') ?>" placeholder="e.g. What is your minimum order quantity?">
      </div>
      <div class="form-group form-full">
        <label>Answer *</label>
        <textarea name="answer" rows="5" required><?= e($editItem['answer'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="category">
          <?php foreach (['General','Orders','Quality','Services','Payments','Shipping'] as $c): ?>
          <option <?= ($editItem['category']??'')===$c?'selected':'' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Sort Order (lower = first)</label>
        <input type="number" name="sort_order" value="<?= $editItem['sort_order'] ?? 0 ?>">
      </div>
    </div>
    <div style="margin-top:1.25rem;"><button type="submit" class="btn-save">💾 Save FAQ</button></div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>All FAQs (<?= count($faqs) ?>)</h2>
    <a href="faq.php?new=1" class="btn-save btn-sm" style="color:#08122a;">+ New FAQ</a>
  </div>
  <table>
    <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($faqs as $f): ?>
      <tr>
        <td><?= $f['sort_order'] ?></td>
        <td><?= e(substr($f['question'],0,80)) ?>...</td>
        <td><span class="badge badge-gold"><?= e($f['category']) ?></span></td>
        <td><?= $f['is_active']?'<span class="badge badge-green">Active</span>':'<span class="badge badge-gray">Hidden</span>' ?></td>
        <td style="display:flex;gap:0.5rem;">
          <a href="faq.php?edit=<?= $f['id'] ?>" class="btn-edit btn-sm">Edit</a>
          <form method="post" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button class="btn-sm" style="background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1;"><?= $f['is_active']?'Hide':'Show' ?></button>
          </form>
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button class="btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

  </div>
</div>
</body>
</html>
