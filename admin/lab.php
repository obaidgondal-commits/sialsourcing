<?php
// admin/lab.php
$pageTitle = 'Lab Tests & QC';
require_once __DIR__ . '/includes/auth.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $d  = [trim($_POST['title']??''), trim($_POST['category']??'Lab Test'), trim($_POST['description']??''), trim($_POST['standards']??''), trim($_POST['icon']??'flask'), (int)($_POST['sort_order']??0)];
        if ($id) db()->prepare("UPDATE lab_tests SET title=?,category=?,description=?,standards=?,icon=?,sort_order=? WHERE id=?")->execute(array_merge($d,[$id]));
        else db()->prepare("INSERT INTO lab_tests (title,category,description,standards,icon,sort_order) VALUES (?,?,?,?,?,?)")->execute($d);
        $msg = 'success:Saved.';
    } elseif ($action==='delete') { db()->prepare("DELETE FROM lab_tests WHERE id=?")->execute([(int)$_POST['id']]); $msg='success:Deleted.'; }
    elseif ($action==='toggle') { db()->prepare("UPDATE lab_tests SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]); $msg='success:Updated.'; }
}
$editItem = null;
if (isset($_GET['edit'])) { $st=db()->prepare("SELECT * FROM lab_tests WHERE id=?"); $st->execute([(int)$_GET['edit']]); $editItem=$st->fetch(); }
$showForm = isset($_GET['new']) || $editItem;
$items    = db()->query("SELECT * FROM lab_tests ORDER BY sort_order")->fetchAll();
[$mt,$mx] = $msg ? explode(':',$msg,2) : [null,null];
$icons = ['flask','zap','thermometer','shield','layers','activity','check-square','clipboard','eye','check-circle'];
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($mx): ?><div class="alert alert-<?= $mt==='success'?'success':'error' ?>"><?= e($mx) ?></div><?php endif; ?>

<?php if ($showForm): ?>
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header"><h2><?= $editItem?'Edit':'New' ?> Lab/QC Entry</h2><a href="lab.php" class="btn-edit btn-sm">← Back</a></div>
  <form method="post" style="padding:1.5rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= $editItem['id']??0 ?>">
    <div class="form-grid">
      <div class="form-group form-full"><label>Title *</label><input name="title" required value="<?= e($editItem['title']??'') ?>" placeholder="e.g. Biocompatibility Testing"></div>
      <div class="form-group"><label>Category</label>
        <select name="category">
          <option <?= ($editItem['category']??'')==='Lab Test'?'selected':'' ?>>Lab Test</option>
          <option <?= ($editItem['category']??'')==='QC Process'?'selected':'' ?>>QC Process</option>
        </select>
      </div>
      <div class="form-group"><label>Icon</label><select name="icon"><?php foreach ($icons as $ic): ?><option <?= ($editItem['icon']??'flask')===$ic?'selected':'' ?>><?= $ic ?></option><?php endforeach; ?></select></div>
      <div class="form-group form-full"><label>Description</label><textarea name="description" rows="4"><?= e($editItem['description']??'') ?></textarea></div>
      <div class="form-group"><label>Standards / Reference</label><input name="standards" value="<?= e($editItem['standards']??'') ?>" placeholder="e.g. ISO 10993, REACH"></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $editItem['sort_order']??0 ?>"></div>
    </div>
    <div style="margin-top:1.25rem;"><button type="submit" class="btn-save">💾 Save</button></div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header"><h2>Lab & QC Entries (<?= count($items) ?>)</h2><a href="lab.php?new=1" class="btn-save btn-sm" style="color:#08122a;">+ New Entry</a></div>
  <table>
    <thead><tr><th>#</th><th>Title</th><th>Category</th><th>Standards</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($items as $l): ?>
      <tr>
        <td><?= $l['sort_order'] ?></td>
        <td><strong><?= e($l['title']) ?></strong></td>
        <td><span class="badge badge-gold"><?= e($l['category']) ?></span></td>
        <td><code style="font-size:0.78rem;"><?= e($l['standards']) ?></code></td>
        <td><?= $l['is_active']?'<span class="badge badge-green">Active</span>':'<span class="badge badge-gray">Hidden</span>' ?></td>
        <td style="display:flex;gap:0.5rem;">
          <a href="lab.php?edit=<?= $l['id'] ?>" class="btn-edit btn-sm">Edit</a>
          <form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $l['id'] ?>"><button class="btn-sm" style="background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1;"><?= $l['is_active']?'Hide':'Show' ?></button></form>
            <?= csrf_field() ?>
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $l['id'] ?>"><button class="btn-danger">Del</button></form>
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
