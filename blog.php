<?php
$pageTitle     = 'Sourcing Insights & News';
$pageDesc      = 'Articles, guides, and industry updates from SialSourcing — helping international buyers navigate Pakistani manufacturing.';
$canonicalPath = '/blog';
require_once __DIR__ . '/includes/header.php';

$cat    = $_GET['cat'] ?? '';
$params = $cat ? [$cat] : [];
$where  = $cat ? "AND category=?" : "";
$st = db()->prepare("SELECT * FROM blog_posts WHERE is_published=1 $where ORDER BY published_at DESC");
$st->execute($params);
$posts = $st->fetchAll();

$cats = db()->query("SELECT DISTINCT category FROM blog_posts WHERE is_published=1")->fetchAll(PDO::FETCH_COLUMN);
?>

<div style="padding-top:80px;">

<section style="background:var(--navy);padding:4rem 0 3rem;text-align:center;">
  <div class="container">
    <div class="section-label reveal">Insights</div>
    <h1 style="color:var(--white);font-size:clamp(2rem,4vw,3rem);margin-bottom:1rem;" class="reveal delay-1">Sourcing Knowledge Hub</h1>
    <p style="color:rgba(255,255,255,0.55);max-width:520px;margin:0 auto 2rem;" class="reveal delay-2">Practical guides, industry news, and sourcing tips for international buyers working with Sialkot's manufacturers.</p>
    <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;" class="reveal delay-3">
      <a href="/blog" style="padding:0.45rem 1.1rem;border-radius:50px;border:1px solid <?= !$cat?'var(--gold)':'rgba(255,255,255,0.2)' ?>;color:<?= !$cat?'var(--gold)':'rgba(255,255,255,0.5)' ?>;font-size:0.82rem;font-weight:600;transition:all 0.2s;">All</a>
      <?php foreach ($cats as $c): ?>
      <a href="/blog?cat=<?= urlencode($c) ?>" style="padding:0.45rem 1.1rem;border-radius:50px;border:1px solid <?= $cat===$c?'var(--gold)':'rgba(255,255,255,0.2)' ?>;color:<?= $cat===$c?'var(--gold)':'rgba(255,255,255,0.5)' ?>;font-size:0.82rem;font-weight:600;transition:all 0.2s;"><?= e($c) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-cream">
  <div class="container">
    <?php if (!empty($posts)): ?>
    <div class="blog-grid">
      <?php foreach ($posts as $i => $p): ?>
      <a href="/blog/<?= e($p['slug']) ?>" class="blog-card reveal delay-<?= ($i%3)+1 ?>" style="display:block;">
        <div class="blog-img">
          <?php if ($p['featured_image']): ?>
            <img src="<?= e($p['featured_image']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
          <?php else: ?>
            <span class="card-placeholder"><?= icon('file-text', 34) ?></span>
          <?php endif; ?>
        </div>
        <div class="blog-body">
          <div class="blog-meta">
            <span class="blog-cat"><?= e($p['category']) ?></span>
            <span><?= date('M d, Y', strtotime($p['published_at'] ?? $p['created_at'])) ?></span>
            <span>By <?= e($p['author']) ?></span>
          </div>
          <h3><?= e($p['title']) ?></h3>
          <p><?= e(substr($p['excerpt'] ?? '', 0, 150)) ?>...</p>
          <span class="blog-link">Read Article <?= icon('arrow-right', 16) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:4rem 0;color:var(--muted);">
      <p style="font-size:1.05rem;margin-bottom:1rem;">No articles in this category yet.</p>
      <a href="/blog" style="color:var(--gold);font-weight:600;">View all articles →</a>
    </div>
    <?php endif; ?>
  </div>
</section>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
