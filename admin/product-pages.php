<?php
$pageTitle = 'Product Pages';
require_once __DIR__ . '/includes/auth.php';

$msg = '';
$slug = $_GET['slug'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($action === 'save_page') {
        $hero      = trim($_POST['hero_headline'] ?? '');
        $meta      = trim($_POST['meta_desc'] ?? '');
        $certs     = trim($_POST['certifications'] ?? '');
        $moq       = trim($_POST['moq'] ?? '');
        $lead      = trim($_POST['lead_time'] ?? '');
        $materials = trim($_POST['materials'] ?? '');
        $video     = trim($_POST['video_url'] ?? '');
        $details   = trim($_POST['details'] ?? '');
        $desc      = trim($_POST['description'] ?? '');

        $img = '';
        if (!empty($_FILES['image']['name'])) {
            $img = uploadFile($_FILES['image'], 'products') ?? '';
        }

        $q = "UPDATE products SET hero_headline=?,meta_desc=?,certifications=?,moq=?,lead_time=?,materials=?,video_url=?,details=?,description=?" . ($img ? ",image=?" : "") . " WHERE id=?";
        $p = [$hero,$meta,$certs,$moq,$lead,$materials,$video,$details,$desc];
        if ($img) $p[] = $img;
        $p[] = $product_id;
        db()->prepare($q)->execute($p);
        $msg = 'success:Page saved.';

    } elseif ($action === 'add_gallery') {
        $caption = trim($_POST['caption'] ?? '');
        $sort    = (int)($_POST['sort_order'] ?? 0);
        $img = '';
        if (!empty($_FILES['gallery_image']['name'])) {
            $img = uploadFile($_FILES['gallery_image'], 'products') ?? '';
        }
        if ($img) {
            db()->prepare("INSERT INTO product_gallery (product_id,image,caption,sort_order) VALUES (?,?,?,?)")
               ->execute([$product_id,$img,$caption,$sort]);
            $msg = 'success:Photo added.';
        } else {
            $msg = 'error:Please select an image file.';
        }

    } elseif ($action === 'delete_gallery') {
        db()->prepare("DELETE FROM product_gallery WHERE id=?")->execute([(int)$_POST['gallery_id']]);
        $msg = 'success:Photo deleted.';

    } elseif ($action === 'add_faq') {
        $q = trim($_POST['faq_question'] ?? '');
        $a = trim($_POST['faq_answer'] ?? '');
        $sort = (int)($_POST['sort_order'] ?? 0);
        if ($q && $a) {
            db()->prepare("INSERT INTO product_faqs (product_id,question,answer,sort_order) VALUES (?,?,?,?)")
               ->execute([$product_id,$q,$a,$sort]);
            $msg = 'success:FAQ added.';
        }

    } elseif ($action === 'delete_faq') {
        db()->prepare("DELETE FROM product_faqs WHERE id=?")->execute([(int)$_POST['faq_id']]);
        $msg = 'success:FAQ deleted.';
    }

    if ($slug) {
        header("Location: product-pages.php?slug=" . urlencode($slug) . "&saved=" . urlencode($msg), true, 303);
        exit;
    }
}

if (isset($_GET['saved']) && !$msg) $msg = urldecode($_GET['saved']);

$products    = db()->query("SELECT * FROM products ORDER BY sort_order")->fetchAll();
$editProduct = null;
$gallery     = [];
$faqs        = [];

if ($slug) {
    $st = db()->prepare("SELECT * FROM products WHERE slug=?");
    $st->execute([$slug]);
    $editProduct = $st->fetch();
    if ($editProduct) {
        $st2 = db()->prepare("SELECT * FROM product_gallery WHERE product_id=? ORDER BY sort_order");
        $st2->execute([$editProduct['id']]);
        $gallery = $st2->fetchAll();
        $st3 = db()->prepare("SELECT * FROM product_faqs WHERE product_id=? ORDER BY sort_order");
        $st3->execute([$editProduct['id']]);
        $faqs = $st3->fetchAll();
    }
}

[$msgType,$msgText] = $msg ? explode(':',$msg,2) : [null,null];
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($msgText): ?>
<div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>"><?= e($msgText) ?></div>
<?php endif; ?>

<?php if (!$editProduct): ?>

<!-- ===== PRODUCT LIST ===== -->
<div class="card">
  <div class="card-header">
    <h2>Product Pages (<?= count($products) ?>)</h2>
    <small style="color:#9ca3af;">Click Edit Page to manage each product's full page content</small>
  </div>
  <table>
    <thead>
      <tr><th>Product</th><th>Hero Headline</th><th>Gallery</th><th>FAQs</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p):
        $gc = db()->prepare("SELECT COUNT(*) FROM product_gallery WHERE product_id=?");
        $gc->execute([$p['id']]);
        $gcount = $gc->fetchColumn();
        $fc = db()->prepare("SELECT COUNT(*) FROM product_faqs WHERE product_id=?");
        $fc->execute([$p['id']]);
        $fcount = $fc->fetchColumn();
      ?>
      <tr>
        <td>
          <strong><?= e($p['title']) ?></strong><br>
          <small style="color:#9ca3af;">/<?= e($p['slug']) ?>.php</small>
        </td>
        <td>
          <?php if (!empty($p['hero_headline'])): ?>
            <span style="color:#374151;font-size:0.85rem;"><?= e(substr($p['hero_headline'],0,50)) ?>...</span>
          <?php else: ?>
            <span style="color:#d1d5db;font-size:0.82rem;">Not set</span>
          <?php endif; ?>
        </td>
        <td>
          <span class="badge <?= $gcount > 0 ? 'badge-green' : 'badge-gray' ?>">
            <?= $gcount ?> photo<?= $gcount != 1 ? 's' : '' ?>
          </span>
        </td>
        <td>
          <span class="badge <?= $fcount > 0 ? 'badge-green' : 'badge-gray' ?>">
            <?= $fcount ?> FAQ<?= $fcount != 1 ? 's' : '' ?>
          </span>
        </td>
        <td><?= $p['is_active'] ? '<span class="badge badge-green">Active</span>' : '<span class="badge badge-gray">Hidden</span>' ?></td>
        <td style="display:flex;gap:0.5rem;">
          <a href="product-pages.php?slug=<?= e($p['slug']) ?>" class="btn-save btn-sm" style="color:#08122a;">✏️ Edit Page</a>
          <a href="<?= SITE_URL ?>/<?= e($p['slug']) ?>.php" target="_blank" class="btn-edit btn-sm">🌐 View</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php else: ?>

<!-- ===== EDIT PRODUCT PAGE ===== -->
<div style="margin-bottom:1rem;">
  <a href="product-pages.php" class="btn-edit btn-sm">← Back to All Products</a>
</div>

<!-- SECTION 1: Main Page Content -->
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2>📝 Page Content — <?= e($editProduct['title']) ?></h2>
  </div>
  <form method="post" enctype="multipart/form-data" style="padding:1.5rem;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_page">
    <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
    <div class="form-grid">

      <div class="form-group form-full">
        <label>Hero Headline (large text at top of page)</label>
        <input type="text" name="hero_headline" value="<?= e($editProduct['hero_headline'] ?? '') ?>"
               placeholder="e.g. ISO 13485 Certified Surgical Instruments from Sialkot, Pakistan">
      </div>

      <div class="form-group form-full">
        <label>SEO Meta Description (shown in Google search results — keep under 155 characters)</label>
        <textarea name="meta_desc" rows="2" placeholder="e.g. Source CE-marked surgical instruments from verified Sialkot manufacturers..."><?= e($editProduct['meta_desc'] ?? '') ?></textarea>
      </div>

      <div class="form-group form-full">
        <label>Short Description (shown on homepage and products listing)</label>
        <textarea name="description" rows="3"><?= e($editProduct['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group form-full">
        <label>Full Page Content (HTML supported — describe what you source, sub-categories, materials, etc.)</label>
        <textarea name="details" rows="12" placeholder="<h3>What We Source</h3><ul><li>...</li></ul>"><?= e($editProduct['details'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label>Certifications (comma separated)</label>
        <input type="text" name="certifications" value="<?= e($editProduct['certifications'] ?? '') ?>"
               placeholder="e.g. ISO 13485, CE Marking, FDA Registered, ISO 9001">
        <small style="color:#9ca3af;font-size:0.75rem;">These appear as badges on the product page</small>
      </div>

      <div class="form-group">
        <label>Minimum Order Quantity</label>
        <input type="text" name="moq" value="<?= e($editProduct['moq'] ?? '') ?>"
               placeholder="e.g. 500 units (varies by product)">
      </div>

      <div class="form-group">
        <label>Lead Time</label>
        <input type="text" name="lead_time" value="<?= e($editProduct['lead_time'] ?? '') ?>"
               placeholder="e.g. 30–45 days after sample approval">
      </div>

      <div class="form-group">
        <label>Materials (brief summary)</label>
        <input type="text" name="materials" value="<?= e($editProduct['materials'] ?? '') ?>"
               placeholder="e.g. 410, 420, 440 stainless steel grades">
      </div>

      <div class="form-group">
        <label>Video URL (YouTube or Vimeo embed link — optional)</label>
        <input type="text" name="video_url" value="<?= e($editProduct['video_url'] ?? '') ?>"
               placeholder="e.g. https://www.youtube.com/embed/VIDEOID">
      </div>

      <div class="form-group">
        <label>Main Product Image</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($editProduct['image'])): ?>
          <img src="<?= e($editProduct['image']) ?>" style="height:80px;margin-top:0.5rem;border-radius:8px;object-fit:cover;">
        <?php endif; ?>
      </div>

    </div>
    <div style="margin-top:1.25rem;">
      <button type="submit" class="btn-save">💾 Save Page Content</button>
    </div>
  </form>
</div>

<!-- SECTION 2: Photo Gallery -->
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2>📸 Photo Gallery (<?= count($gallery) ?> photos)</h2>
  </div>
  <div style="padding:1.5rem;">

    <?php if (!empty($gallery)): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
      <?php foreach ($gallery as $g): ?>
      <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;position:relative;">
        <img src="<?= e($g['image']) ?>" style="width:100%;height:120px;object-fit:cover;">
        <div style="padding:0.5rem;">
          <div style="font-size:0.75rem;color:#6b7280;margin-bottom:0.4rem;"><?= e($g['caption'] ?: 'No caption') ?></div>
          <form method="post" onsubmit="return confirm('Delete this photo?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_gallery">
            <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
            <input type="hidden" name="gallery_id" value="<?= $g['id'] ?>">
            <button class="btn-danger" style="width:100%;font-size:0.72rem;">🗑 Delete</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr 1fr auto;gap:1rem;align-items:end;">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_gallery">
      <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
      <div class="form-group">
        <label>Add Photo</label>
        <input type="file" name="gallery_image" accept="image/*" required>
      </div>
      <div class="form-group">
        <label>Caption (optional)</label>
        <input type="text" name="caption" placeholder="e.g. Surgical scissors set — ISO 13485">
      </div>
      <div>
        <button type="submit" class="btn-save">+ Add Photo</button>
      </div>
    </form>
  </div>
</div>

<!-- SECTION 3: FAQs -->
<div class="card" style="margin-bottom:1.5rem;">
  <div class="card-header">
    <h2>❓ FAQs for This Product (<?= count($faqs) ?>)</h2>
  </div>
  <div style="padding:1.5rem;">

    <?php if (!empty($faqs)): ?>
    <table style="margin-bottom:1.5rem;">
      <thead><tr><th>#</th><th>Question</th><th>Answer</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($faqs as $i => $f): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td style="font-weight:600;font-size:0.88rem;"><?= e($f['question']) ?></td>
          <td style="font-size:0.85rem;color:#6b7280;"><?= e(substr($f['answer'],0,80)) ?>...</td>
          <td>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this FAQ?')">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_faq">
              <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
              <input type="hidden" name="faq_id" value="<?= $f['id'] ?>">
              <button class="btn-danger">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <form method="post" style="display:grid;gap:1rem;">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_faq">
      <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
      <div class="form-group">
        <label>Question</label>
        <input type="text" name="faq_question" placeholder="e.g. What is the minimum order for surgical instruments?" required>
      </div>
      <div class="form-group">
        <label>Answer</label>
        <textarea name="faq_answer" rows="3" placeholder="e.g. Our minimum order varies by product..." required></textarea>
      </div>
      <div>
        <button type="submit" class="btn-save">+ Add FAQ</button>
      </div>
    </form>
  </div>
</div>

<?php endif; ?>

</body>
</html>