<?php
require_once __DIR__ . '/config.php';
$slug = trim($_GET['slug'] ?? '');
if (!$slug) { http_response_code(404); include __DIR__ . '/404.php'; exit; }

$st = db()->prepare("SELECT * FROM blog_posts WHERE slug=? AND is_published=1");
$st->execute([$slug]);
$post = $st->fetch();
if (!$post) { http_response_code(404); include __DIR__ . '/404.php'; exit; }

$pageTitle     = $post['title'];
$pageDesc      = $post['excerpt'] ?: substr(strip_tags($post['content']), 0, 160);
$canonicalPath = '/blog/' . $post['slug'];
$ogType        = 'article';
if (!empty($post['featured_image'])) {
    $ogImage = (str_starts_with($post['featured_image'], 'http') ? '' : SITE_URL) . $post['featured_image'];
}
$shareUrl = SITE_URL . '/blog/' . $post['slug'];
require_once __DIR__ . '/includes/header.php';

$related = db()->prepare("SELECT * FROM blog_posts WHERE is_published=1 AND id!=? AND category=? ORDER BY published_at DESC LIMIT 3");
$related->execute([$post['id'], $post['category']]);
$related = $related->fetchAll();

$postSchema = [
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => $post['title'],
    'description'      => $pageDesc,
    'image'            => $ogImage ?? SITE_URL . '/og-image.jpg',
    'datePublished'    => date('c', strtotime($post['published_at'] ?? $post['created_at'])),
    'dateModified'     => date('c', strtotime($post['updated_at'] ?? $post['published_at'] ?? $post['created_at'])),
    'author'           => ['@type' => 'Organization', 'name' => $post['author'] ?: 'SialSourcing Team', 'url' => SITE_URL],
    'publisher'        => [
        '@type' => 'Organization',
        'name'  => 'SialSourcing',
        'logo'  => ['@type' => 'ImageObject', 'url' => SITE_URL . '/sialsourcing-logo.png'],
    ],
    'mainEntityOfPage' => $shareUrl,
];
$breadcrumbSchema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => SITE_URL . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => SITE_URL . '/blog'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $shareUrl],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($postSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/ld+json"><?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<div style="padding-top:80px;">

<!-- Hero -->
<section style="background:var(--navy);padding:4rem 0 3rem;">
  <div class="container" style="max-width:800px;">
    <div style="display:flex;gap:0.75rem;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;" class="reveal">
      <span class="blog-cat" style="background:rgba(201,168,76,0.15);color:var(--gold);padding:0.25rem 0.75rem;border-radius:50px;font-size:0.78rem;font-weight:700;"><?= e($post['category']) ?></span>
      <span style="color:rgba(255,255,255,0.4);font-size:0.82rem;"><?= date('F d, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
      <span style="color:rgba(255,255,255,0.4);font-size:0.82rem;">By <?= e($post['author']) ?></span>
    </div>
    <h1 style="color:var(--white);font-size:clamp(1.8rem,4vw,2.8rem);margin-bottom:1rem;" class="reveal delay-1"><?= e($post['title']) ?></h1>
    <?php if ($post['excerpt']): ?>
    <p style="color:rgba(255,255,255,0.6);font-size:1.05rem;line-height:1.7;" class="reveal delay-2"><?= e($post['excerpt']) ?></p>
    <?php endif; ?>
  </div>
</section>

<?php if ($post['featured_image']): ?>
<div style="max-width:800px;margin:0 auto;padding:0 2rem;">
  <img src="<?= e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>" style="width:100%;border-radius:var(--radius);margin-top:-1px;box-shadow:0 20px 60px rgba(0,0,0,0.15);aspect-ratio:16/9;object-fit:cover;">
</div>
<?php endif; ?>

<!-- Content -->
<section class="section section-white">
  <div class="container" style="max-width:800px;">
    <div class="reveal" style="font-size:1rem;line-height:1.9;color:var(--text);">
      <?php
$content = $post['content'];
if (strip_tags($content) === $content) {
    $sections = array_filter(array_map('trim', explode("\n\n", $content)));
    foreach ($sections as $section) {
        $section = trim($section);
        if (strtoupper($section) === $section && strlen($section) < 100) {
            echo '<h2>' . e($section) . '</h2>';
        } else {
            echo '<p>' . nl2br(e($section)) . '</p>';
        }
    }
} else {
    echo $content;
}
?>
    </div>

    <!-- Author box -->
    <div style="margin-top:3rem;padding:1.75rem;background:var(--cream);border-radius:var(--radius);display:flex;gap:1.25rem;align-items:center;">
      <div style="width:56px;height:56px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0;"><?= icon('globe', 26) ?></div>
      <div>
        <div style="font-weight:700;color:var(--text);"><?= e($post['author']) ?></div>
        <div style="font-size:0.85rem;color:var(--muted);">SialSourcing — Pakistan's Premier Buying House based in Sialkot</div>
      </div>
    </div>

    <!-- Share -->
    <div style="margin-top:2rem;display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
      <span style="font-size:0.85rem;font-weight:600;color:var(--muted);">Share:</span>
      <a class="share-pill" href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&amp;url=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener">X / Twitter</a>
      <a class="share-pill" href="https://www.linkedin.com/shareArticle?mini=true&amp;url=<?= urlencode($shareUrl) ?>&amp;title=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener">LinkedIn</a>
      <a class="share-pill share-pill--wa" href="https://wa.me/?text=<?= urlencode($post['title'] . ' — ' . $shareUrl) ?>" target="_blank" rel="noopener">WhatsApp</a>
      <button class="share-pill" type="button" data-copy-link="<?= e($shareUrl) ?>">Copy link</button>
    </div>
  </div>
</section>

<!-- Related posts -->
<?php if (!empty($related)): ?>
<section class="section section-cream">
  <div class="container">
    <h2 style="margin-bottom:2rem;font-size:1.5rem;">More in <?= e($post['category']) ?></h2>
    <div class="blog-grid">
      <?php foreach ($related as $r): ?>
      <a href="/blog/<?= e($r['slug']) ?>" class="blog-card">
        <div class="blog-img"><?= $r['featured_image'] ? '<img src="'.e($r['featured_image']).'" alt="'.e($r['title']).'" loading="lazy">' : '<span class="card-placeholder">'.icon('file-text', 34).'</span>' ?></div>
        <div class="blog-body">
          <div class="blog-meta"><span class="blog-cat"><?= e($r['category']) ?></span></div>
          <h3><?= e($r['title']) ?></h3>
          <span class="blog-link">Read <?= icon('arrow-right', 16) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<div class="cta-section">
  <h2 class="reveal">Ready to Start Sourcing?</h2>
  <p class="reveal delay-1">Our team is ready to help you find the right manufacturer in Sialkot.</p>
  <a href="/contact" class="btn btn-gold reveal delay-2">Get a Free Quote</a>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
