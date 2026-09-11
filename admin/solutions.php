<?php
// admin/solutions.php
$pageTitle = 'Solutions';
require_once __DIR__ . '/includes/auth.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $d  = [trim($_POST['title']??''), trim($_POST['short_desc']??''), trim($_POST['description']??''), trim($_POST['icon']??'check-circle'), (int)($_POST['sort_order']??0)];
        if ($id) db()->prepare("UPDATE solutions SET title=?,short_desc=?,description=?,icon=?,sort_order=? WHERE id=?")->execute(array_merge($d,[$id]));
        else db()->prepare("INSERT INTO solutions (title,short_desc,description,icon,sort_order) VALUES (?,?,?,?,?)")->execute($d);
        $msg = 'success:Saved.';
    } elseif ($action==='delete') { db()->prepare("DELETE FROM solutions WHERE id=?")->execute([(int)$_POST['id']]); $msg='success:Deleted.'; }
    elseif ($action==='toggle') { db()->prepare("UPDATE solutions SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]); $msg='success:Updated.'; }
}
$editItem = null;
if (isset($_GET['edit'])) { $st=db()->prepare("SELECT * FROM solutions WHERE id=?"); $st->execute([(int)$_GET['edit']]); $editItem=$st->fetch(); }
$showForm = isset($_GET['new']) || $editItem;
$items    = db()->query("SELECT * FROM solutions ORDER BY sort_order")->fetchAll();
[$mt,$mx] = $msg ? explode(':',$msg,2) : [null,null];
$icons = ['shield','package','tag','file-text','truck','headphones','check-circle','globe','award','users','bar-chart','zap'];
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($mx): ?><div class="alert alert-<?= $mt==='success'?'success':'error' ?>"><?= e($mx) ?></div><?php endif; ?>

<?php if ($showForm): ?>
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header"><h2><?= $editItem?'Edit':'New' ?> Solution</h2><a href="solutions.php" class="btn-edit btn-sm">← Back</a></div>
  <form method="post" style="padding:1.5rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $editItem['id']??0 ?>">
    <div class="form-grid">
      <div class="form-group form-full"><label>Title *</label><input name="title" required value="<?= e($editItem['title']??'') ?>"></div>
      <div class="form-group form-full"><label>Short Description (homepage card)</label><textarea name="short_desc" rows="2"><?= e($editItem['short_desc']??'') ?></textarea></div>
      <div class="form-group form-full"><label>Full Description</label><textarea name="description" rows="5"><?= e($editItem['description']??'') ?></textarea></div>
      <div class="form-group"><label>Icon</label><select name="icon"><?php foreach ($icons as $ic): ?><option <?= ($editItem['icon']??'check-circle')===$ic?'selected':'' ?>><?= $ic ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $editItem['sort_order']??0 ?>"></div>
    </div>
    <div style="margin-top:1.25rem;"><button type="submit" class="btn-save">💾 Save</button></div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header"><h2>Solutions (<?= count($items) ?>)</h2><a href="solutions.php?new=1" class="btn-save btn-sm" style="color:#08122a;">+ New</a></div>
  <table>
    <thead><tr><th>#</th><th>Title</th><th>Icon</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($items as $s): ?>
      <tr>
        <td><?= $s['sort_order'] ?></td>
        <td><strong><?= e($s['title']) ?></strong><br><small style="color:#9ca3af;"><?= e(substr($s['short_desc'],0,60)) ?></small></td>
        <td><code><?= e($s['icon']) ?></code></td>
        <td><?= $s['is_active']?'<span class="badge badge-green">Active</span>':'<span class="badge badge-gray">Hidden</span>' ?></td>
        <td style="display:flex;gap:0.5rem;">
          <a href="solutions.php?edit=<?= $s['id'] ?>" class="btn-edit btn-sm">Edit</a>
          <form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn-sm" style="background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1;"><?= $s['is_active']?'Hide':'Show' ?></button></form>
            <?= csrf_field() ?>
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn-danger">Del</button></form>
            <?= csrf_field() ?>
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
