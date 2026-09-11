<?php
$pageTitle = 'Products';
require_once __DIR__ . '/includes/auth.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id   = (int)($_POST['id'] ?? 0);
        $data = [
            trim($_POST['title']??''),
            trim($_POST['description']??''),
            trim($_POST['details']??''),
            trim($_POST['icon']??'box'),
            (int)($_POST['sort_order']??0),
        ];
        $img = '';
        if (!empty($_FILES['image']['name'])) $img = uploadFile($_FILES['image'],'products') ?? '';

        if ($id) {
            // Slug is intentionally never regenerated on edit — the page URL,
            // wrapper file, and footer links all depend on it staying stable.
            $q = "UPDATE products SET title=?,description=?,details=?,icon=?,sort_order=?" . ($img?",image=?":'') . " WHERE id=?";
            $p = $data; if ($img) $p[]=$img; $p[]=$id;
            db()->prepare($q)->execute($p);
        } else {
            $ins = array_merge([$data[0], slug($data[0])], array_slice($data,1), [$img]);
            db()->prepare("INSERT INTO products (title,slug,description,details,icon,sort_order,image) VALUES (?,?,?,?,?,?,?)")->execute($ins);
        }
        $msg = 'success:Product saved.';
    } elseif ($action === 'delete') {
        db()->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = 'success:Product deleted.';
    } elseif ($action === 'toggle') {
        db()->prepare("UPDATE products SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = 'success:Updated.';
    }
}

$editItem = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare("SELECT * FROM products WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $editItem = $st->fetch();
}
$showForm = isset($_GET['new']) || $editItem;
$items    = db()->query("SELECT * FROM products ORDER BY sort_order")->fetchAll();
[$msgType,$msgText] = $msg ? explode(':',$msg,2) : [null,null];

$icons = ['box','scissors','activity','briefcase','tool','shield','zap','globe','layers','cpu','target','award','soccer-ball'];
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($msgText): ?>
<div class="alert alert-<?= $msgType==='success'?'success':'error' ?>"><?= e($msgText) ?></div>
<?php endif; ?>

<?php if ($showForm): ?>
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2><?= $editItem?'Edit Product':'New Product' ?></h2>
    <a href="products.php" class="btn-edit btn-sm">← Back</a>
  </div>
  <form method="post" enctype="multipart/form-data" style="padding:1.5rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editItem['id']??0 ?>">
    <div class="form-grid">
      <div class="form-group form-full">
        <label>Product Title *</label>
        <input type="text" name="title" required value="<?= e($editItem['title']??'') ?>" placeholder="e.g. Surgical Instruments">
      </div>
      <div class="form-group form-full">
        <label>Short Description (shown on homepage)</label>
        <textarea name="description" rows="3"><?= e($editItem['description']??'') ?></textarea>
      </div>
      <div class="form-group form-full">
        <label>Full Details (shown on products page — HTML supported)</label>
        <textarea name="details" rows="8"><?= e($editItem['details']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label>Icon (Feather Icons name)</label>
        <select name="icon">
          <?php foreach ($icons as $ic): ?>
          <option <?= ($editItem['icon']??'box')===$ic?'selected':'' ?>><?= $ic ?></option>
          <?php endforeach; ?>
        </select>
        <small style="color:#9ca3af;font-size:0.75rem;margin-top:0.3rem;">Browse icons at feathericons.com</small>
      </div>
      <div class="form-group">
        <label>Sort Order</label>
        <input type="number" name="sort_order" value="<?= $editItem['sort_order']??0 ?>">
      </div>
      <div class="form-group">
        <label>Product Image</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($editItem['image'])): ?>
          <img src="<?= e($editItem['image']) ?>" style="height:60px;margin-top:0.5rem;border-radius:6px;">
        <?php endif; ?>
      </div>
    </div>
    <div style="margin-top:1.25rem;"><button type="submit" class="btn-save">💾 Save Product</button></div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>All Products (<?= count($items) ?>)</h2>
    <a href="products.php?new=1" class="btn-save btn-sm" style="color:#08122a;">+ New Product</a>
  </div>
  <table>
    <thead><tr><th>#</th><th>Title</th><th>Icon</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($items as $p): ?>
      <tr>
        <td><?= $p['sort_order'] ?></td>
        <td><strong><?= e($p['title']) ?></strong><br><small style="color:#9ca3af;"><?= e(substr($p['description'],0,60)) ?>...</small></td>
        <td><code><?= e($p['icon']) ?></code></td>
        <td><?= $p['is_active']?'<span class="badge badge-green">Active</span>':'<span class="badge badge-gray">Hidden</span>' ?></td>
        <td style="display:flex;gap:0.5rem;">
          <a href="products.php?edit=<?= $p['id'] ?>" class="btn-edit btn-sm">Edit</a>
          <form method="post" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button class="btn-sm" style="background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1;"><?= $p['is_active']?'Hide':'Show' ?></button>
          </form>
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
