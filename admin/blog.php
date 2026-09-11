<?php
$pageTitle = 'Blog Posts';
require_once __DIR__ . '/includes/auth.php';

$msg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id      = (int)($_POST['id'] ?? 0);
        $title   = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $author  = trim($_POST['author'] ?? 'SialSourcing Team');
        $cat     = trim($_POST['category'] ?? 'Insights');
        $pub     = !empty($_POST['is_published']) ? 1 : 0;
        $img     = '';

        if (!empty($_FILES['featured_image']['name'])) {
            $img = uploadFile($_FILES['featured_image'], 'blog') ?? '';
        }

        if ($id) {
            // Slug intentionally kept stable on edit — the post URL is indexed and shared
            $q = "UPDATE blog_posts SET title=?,excerpt=?,content=?,author=?,category=?,is_published=?,published_at=IF(?=1 AND published_at IS NULL,NOW(),published_at)" . ($img ? ",featured_image=?" : "") . " WHERE id=?";
            $params = [$title,$excerpt,$content,$author,$cat,$pub,$pub];
            if ($img) $params[] = $img;
            $params[] = $id;
            db()->prepare($q)->execute($params);
            $msg = 'success:Post updated.';
        } else {
            $q = "INSERT INTO blog_posts (title,slug,excerpt,content,author,category,is_published,published_at,featured_image) VALUES (?,?,?,?,?,?,?,?,?)";
            db()->prepare($q)->execute([$title,slug($title),$excerpt,$content,$author,$cat,$pub, $pub?date('Y-m-d H:i:s'):null,$img]);
            $msg = 'success:Post created.';
        }

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$id]);
        $msg = 'success:Post deleted.';

    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare("UPDATE blog_posts SET is_published = 1-is_published, published_at=IF(is_published=0,NOW(),published_at) WHERE id=?")->execute([$id]);
        $msg = 'success:Status updated.';
    }
}

$editPost = null;
if (isset($_GET['edit'])) {
    $st = db()->prepare("SELECT * FROM blog_posts WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $editPost = $st->fetch();
}

$showForm = isset($_GET['new']) || $editPost;
$posts    = db()->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();

[$msgType, $msgText] = $msg ? explode(':', $msg, 2) : [null, null];
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($msgText): ?>
<div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>"><?= e($msgText) ?></div>
<?php endif; ?>

<?php if ($showForm): ?>
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2><?= $editPost ? 'Edit Post' : 'New Blog Post' ?></h2>
    <a href="blog.php" class="btn-edit btn-sm">← Back</a>
  </div>
  <form method="post" enctype="multipart/form-data" style="padding:1.5rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editPost['id'] ?? 0 ?>">
    <div class="form-grid">
      <div class="form-group form-full">
        <label>Post Title *</label>
        <input type="text" name="title" required value="<?= e($editPost['title'] ?? '') ?>" placeholder="e.g. Why Sialkot Leads Global Surgical Exports">
      </div>
      <div class="form-group">
        <label>Author</label>
        <input type="text" name="author" value="<?= e($editPost['author'] ?? 'SialSourcing Team') ?>">
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="category">
          <?php foreach (['Insights','Industry News','Sourcing Guide','Quality & QC','Trade Tips'] as $c): ?>
          <option <?= ($editPost['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group form-full">
        <label>Excerpt (shown in blog listing)</label>
        <textarea name="excerpt" rows="3"><?= e($editPost['excerpt'] ?? '') ?></textarea>
      </div>
      <div class="form-group form-full">
        <label>Full Content (HTML supported)</label>
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
        <div id="quill-editor" style="height:500px;background:#fff;border:1px solid #d1d5db;border-radius:6px;font-size:15px;"></div>
        <textarea name="content" id="content-hidden" style="display:none;"><?= e($editPost['content'] ?? '') ?></textarea>
        <script>
        var quill = new Quill('#quill-editor', {
          theme: 'snow',
          modules: {
            toolbar: [
              [{ 'header': [1, 2, 3, false] }],
              ['bold', 'italic', 'underline'],
              [{ 'color': [] }, { 'background': [] }],
              [{ 'list': 'ordered'}, { 'list': 'bullet' }],
              [{ 'align': [] }],
              ['link', 'blockquote'],
              ['clean']
            ]
          }
        });
        var savedContent = document.getElementById('content-hidden').value;
        if (savedContent) {
          quill.root.innerHTML = savedContent;
        }
        document.querySelector('form').addEventListener('submit', function() {
          document.getElementById('content-hidden').value = quill.root.innerHTML;
        });
        </script>
      </div>
      <div class="form-group">
        <label>Featured Image</label>
        <input type="file" name="featured_image" accept="image/*">
        <?php if (!empty($editPost['featured_image'])): ?>
          <img src="<?= e($editPost['featured_image']) ?>" style="height:60px;margin-top:0.5rem;border-radius:6px;">
        <?php endif; ?>
      </div>
      <div class="form-group" style="justify-content:center;">
        <label style="cursor:pointer;display:flex;align-items:center;gap:0.5rem;margin-top:1.5rem;">
          <input type="checkbox" name="is_published" value="1" <?= !empty($editPost['is_published']) ? 'checked' : '' ?>>
          Publish immediately
        </label>
      </div>
    </div>
    <div style="margin-top:1.25rem;">
      <button type="submit" class="btn-save">💾 Save Post</button>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h2>All Posts (<?= count($posts) ?>)</h2>
    <a href="blog.php?new=1" class="btn-save btn-sm" style="color:#08122a;">+ New Post</a>
  </div>
  <table>
    <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($posts as $p): ?>
      <tr>
        <td><strong><?= e($p['title']) ?></strong></td>
        <td><span class="badge badge-gold"><?= e($p['category']) ?></span></td>
        <td><?= e($p['author']) ?></td>
        <td><?= $p['is_published'] ? '<span class="badge badge-green">Published</span>' : '<span class="badge badge-gray">Draft</span>' ?></td>
        <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
        <td style="display:flex;gap:0.5rem;align-items:center;">
          <a href="blog.php?edit=<?= $p['id'] ?>" class="btn-edit btn-sm">Edit</a>
          <form method="post" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="btn-sm" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;"><?= $p['is_published'] ? 'Unpublish' : 'Publish' ?></button>
          </form>
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete this post?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($posts)): ?>
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:#9ca3af;">No posts yet. Create your first post.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

  </div>
</div>
</body>
</html>
