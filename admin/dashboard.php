<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/auth.php';

$counts = [
    'products'  => db()->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn(),
    'solutions' => db()->query("SELECT COUNT(*) FROM solutions WHERE is_active=1")->fetchColumn(),
    'lab'       => db()->query("SELECT COUNT(*) FROM lab_tests WHERE is_active=1")->fetchColumn(),
    'blogs'     => db()->query("SELECT COUNT(*) FROM blog_posts WHERE is_published=1")->fetchColumn(),
    'faqs'      => db()->query("SELECT COUNT(*) FROM faqs WHERE is_active=1")->fetchColumn(),
    'enquiries' => db()->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read=0")->fetchColumn(),
];

$recentEnquiries = db()->query("SELECT * FROM contact_submissions ORDER BY submitted_at DESC LIMIT 5")->fetchAll();
$recentBlogs     = db()->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="stat-grid">
  <div class="stat-card"><div class="num"><?= $counts['products'] ?></div><div class="label">Products</div></div>
  <div class="stat-card"><div class="num"><?= $counts['solutions'] ?></div><div class="label">Solutions</div></div>
  <div class="stat-card"><div class="num"><?= $counts['blogs'] ?></div><div class="label">Blog Posts</div></div>
  <div class="stat-card"><div class="num"><?= $counts['faqs'] ?></div><div class="label">FAQs</div></div>
  <div class="stat-card"><div class="num" style="color:#dc2626"><?= $counts['enquiries'] ?></div><div class="label">New Enquiries</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
  <div class="card">
    <div class="card-header">
      <h2>Recent Enquiries</h2>
      <a href="inbox.php" class="btn-edit btn-sm">View All</a>
    </div>
    <table>
      <thead><tr><th>Name</th><th>Company</th><th>Date</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($recentEnquiries as $e): ?>
        <tr>
          <td><?= htmlspecialchars($e['name']) ?></td>
          <td><?= htmlspecialchars($e['company'] ?? '—') ?></td>
          <td><?= date('d M', strtotime($e['submitted_at'])) ?></td>
          <td><?= $e['is_read'] ? '<span class="badge badge-gray">Read</span>' : '<span class="badge badge-green">New</span>' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentEnquiries)): ?>
        <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:2rem;">No enquiries yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header">
      <h2>Recent Blog Posts</h2>
      <a href="blog.php" class="btn-edit btn-sm">Manage</a>
    </div>
    <table>
      <thead><tr><th>Title</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recentBlogs as $b): ?>
        <tr>
          <td><?= htmlspecialchars(substr($b['title'],0,40)) ?>...</td>
          <td><?= $b['is_published'] ? '<span class="badge badge-green">Published</span>' : '<span class="badge badge-gray">Draft</span>' ?></td>
          <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentBlogs)): ?>
        <tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:2rem;">No blog posts yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div style="margin-top:1.5rem;padding:1.25rem;background:#fff;border-radius:12px;border:1px solid #e5e7eb;display:flex;gap:1rem;flex-wrap:wrap;">
  <a href="<?= SITE_URL ?>" target="_blank" class="btn-edit btn-sm" style="display:inline-block;">🌐 View Website</a>
  <a href="blog.php?new=1" class="btn-save btn-sm" style="display:inline-block;color:#08122a;">✍️ New Blog Post</a>
  <a href="faq.php?new=1" class="btn-save btn-sm" style="display:inline-block;color:#08122a;">❓ Add FAQ</a>
  <a href="settings.php" class="btn-edit btn-sm" style="display:inline-block;">⚙️ Settings</a>
</div>

  </div><!-- .content -->
</div><!-- .main -->
</body>
</html>
