<?php
$pageTitle = 'Team Members';
require_once __DIR__ . '/includes/auth.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $role     = trim($_POST['role'] ?? '');
        $bio      = trim($_POST['bio'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $sort     = (int)($_POST['sort_order'] ?? 0);
        $active   = !empty($_POST['is_active']) ? 1 : 0;
        $img      = '';

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
            $img = uploadFile($_FILES['image'], 'team') ?? '';
        }

        try {
            if ($id) {
                $q = "UPDATE team_members SET name=?,role=?,bio=?,location=?,linkedin=?,email=?,sort_order=?,is_active=?" . ($img ? ",image=?" : "") . " WHERE id=?";
                $params = [$name,$role,$bio,$location,$linkedin,$email,$sort,$active];
                if ($img) $params[] = $img;
                $params[] = $id;
                db()->prepare($q)->execute($params);
                $msg = 'success:Team member updated.';
            } else {
                db()->prepare("INSERT INTO team_members (name,role,bio,location,linkedin,email,sort_order,is_active,image) VALUES (?,?,?,?,?,?,?,?,?)")
                   ->execute([$name,$role,$bio,$location,$linkedin,$email,$sort,$active,$img]);
                $msg = 'success:Team member added.';
            }
        } catch (Exception $e) {
            $msg = 'error:Database error — ' . $e->getMessage();
        }

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare("DELETE FROM team_members WHERE id=?")->execute([$id]);
        $msg = 'success:Team member deleted.';

    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare("UPDATE team_members SET is_active = 1-is_active WHERE id=?")->execute([$id]);
        $msg = 'success:Status updated.';
    }
}

$editMember = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare("SELECT * FROM team_members WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $editMember = $st->fetch();
}

$showForm = isset($_GET['new']) || $editMember;
$members  = db()->query("SELECT * FROM team_members ORDER BY sort_order, name")->fetchAll();

[$msgType, $msgText] = $msg ? explode(':', $msg, 2) : [null, null];
?>

<?php if ($msgText): ?>
<div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>"><?= e($msgText) ?></div>
<?php endif; ?>

<?php if ($showForm): ?>
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2><?= $editMember ? 'Edit Team Member' : 'Add Team Member' ?></h2>
    <a href="team.php" class="btn-edit btn-sm">← Back</a>
  </div>
  <form method="post" enctype="multipart/form-data" style="padding:1.5rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editMember['id'] ?? 0 ?>">
    <div class="form-grid">
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="name" required value="<?= e($editMember['name'] ?? '') ?>" placeholder="e.g. Ahmed Raza">
      </div>
      <div class="form-group">
        <label>Role / Title *</label>
        <input type="text" name="role" required value="<?= e($editMember['role'] ?? '') ?>" placeholder="e.g. Head of Quality Control">
      </div>
      <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" value="<?= e($editMember['location'] ?? '') ?>" placeholder="e.g. Sialkot, Pakistan">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($editMember['email'] ?? '') ?>" placeholder="ahmed@sialsourcing.com">
      </div>
      <div class="form-group form-full">
        <label>LinkedIn URL</label>
        <input type="text" name="linkedin" value="<?= e($editMember['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/ahmedraza">
      </div>
      <div class="form-group form-full">
        <label>Bio</label>
        <textarea name="bio" rows="4" placeholder="Short bio about this team member..."><?= e($editMember['bio'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label>Photo</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($editMember['image'])): ?>
          <img src="<?= e($editMember['image']) ?>" style="height:70px;margin-top:0.5rem;border-radius:50%;object-fit:cover;width:70px;">
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Sort Order (lower = first)</label>
        <input type="number" name="sort_order" value="<?= e($editMember['sort_order'] ?? 0) ?>" min="0">
        <label style="cursor:pointer;display:flex;align-items:center;gap:0.5rem;margin-top:1rem;">
          <input type="checkbox" name="is_active" value="1" <?= !isset($editMember) || !empty($editMember['is_active']) ? 'checked' : '' ?>>
          Show on website
        </label>
      </div>
    </div>
    <div style="margin-top:1.25rem;">
      <button type="submit" class="btn-save">💾 Save Member</button>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>Team Members (<?= count($members) ?>)</h2>
    <a href="team.php?new=1" class="btn-save btn-sm" style="color:#08122a;">+ Add Member</a>
  </div>
  <table>
    <thead>
      <tr>
        <th>Photo</th>
        <th>Name</th>
        <th>Role</th>
        <th>Location</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($members as $m): ?>
      <tr>
        <td>
          <?php if (!empty($m['image'])): ?>
            <img src="<?= e($m['image']) ?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
          <?php else: ?>
            <div style="width:44px;height:44px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">👤</div>
          <?php endif; ?>
        </td>
        <td><strong><?= e($m['name']) ?></strong></td>
        <td><?= e($m['role']) ?></td>
        <td><?= e($m['location'] ?? '—') ?></td>
        <td><?= $m['is_active'] ? '<span class="badge badge-green">Visible</span>' : '<span class="badge badge-gray">Hidden</span>' ?></td>
        <td style="display:flex;gap:0.5rem;align-items:center;">
          <a href="team.php?edit=<?= $m['id'] ?>" class="btn-edit btn-sm">Edit</a>
          <form method="post" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button type="submit" class="btn-sm" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;">
              <?= $m['is_active'] ? 'Hide' : 'Show' ?>
            </button>
          </form>
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete this team member?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button type="submit" class="btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($members)): ?>
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:#9ca3af;">No team members yet. Add your first one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>