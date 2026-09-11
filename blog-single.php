<?php
require_once __DIR__ . '/config.php';
$slug = is_string($_GET['slug'] ?? null) ? trim($_GET['slug']) : '';
if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug) || strlen($slug) > 200) {
    http_response_code(404); include __DIR__ . '/404.php'; exit;
}

$canonicalOrigin = defined('CANONICAL_URL') ? CANONICAL_URL : 'https://sialsourcing.com';
$now = gmdate('Y-m-d H:i:s');
// SQL dates are UTC. Invalid/absent dates stay absent instead of becoming 1970.
$blogDate = static function (mixed $value): ?DateTimeImmutable {
    if (!is_string($value) || $value === '') return null;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value, new DateTimeZone('UTC'));
    $errors = DateTimeImmutable::getLastErrors();
    return $date && $date->format('Y') !== '0000' && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0)) && $date->format('Y-m-d H:i:s') === $value ? $date : null;
};
$blogImageUrl = static function (mixed $value) use ($canonicalOrigin): string {
    if (!is_string($value)) return '';
    $value = trim($value);
    if ($value === '' || preg_match('/[\x00-\x20\\\\]/', $value)) return '';
    if (str_starts_with($value, '/') && !str_starts_with($value, '//')) return $canonicalOrigin . $value;
    if (!filter_var($value, FILTER_VALIDATE_URL)) return '';
    $parts = parse_url($value);
    return ($parts['scheme'] ?? '') === 'https' && !isset($parts['user']) && !isset($parts['pass']) ? $value : '';
};

$st = db()->prepare("SELECT * FROM blog_posts WHERE slug=? AND is_published=1 AND COALESCE(published_at, created_at) <= ?");
$st->execute([$slug, $now]);
$post = $st->fetch();
if (!$post) { http_response_code(404); include __DIR__ . '/404.php'; exit; }

$pageTitle = (string)$post['title'];
$plainDescription = trim(preg_replace('/\s+/u', ' ', strip_tags((string)($post['excerpt'] ?: $post['content']))) ?? '');
$pageDesc = function_exists('mb_substr') ? mb_substr($plainDescription, 0, 170) : $plainDescription;
$canonicalPath = '/blog/' . $post['slug'];
$ogType = 'article';
$featuredImage = $blogImageUrl($post['featured_image'] ?? '');
if ($featuredImage !== '') $ogImage = $featuredImage;
$shareUrl = $canonicalOrigin . $canonicalPath;
$publishedDate = $blogDate($post['published_at'] ?? null);
$modifiedDate = $blogDate($post['updated_at'] ?? null);
$authorName = trim((string)($post['author'] ?? ''));

$relatedQuery = db()->prepare("SELECT * FROM blog_posts WHERE is_published=1 AND COALESCE(published_at, created_at) <= ? AND id!=? AND category=? ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT 3");
$relatedQuery->execute([$now, $post['id'], $post['category']]);
$related = $relatedQuery->fetchAll();

$postSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    '@id' => $shareUrl . '#article',
    'headline' => $post['title'],
    'description' => $pageDesc,
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $shareUrl],
    'publisher' => ['@type' => 'Organization', '@id' => $canonicalOrigin . '/#organization', 'name' => 'SialSourcing'],
];
if ($featuredImage !== '') $postSchema['image'] = $featuredImage;
if ($publishedDate && $publishedDate->format('Y-m-d H:i:s') <= $now) $postSchema['datePublished'] = $publishedDate->format(DATE_ATOM);
if ($modifiedDate && $modifiedDate->format('Y-m-d H:i:s') <= $now && (!$publishedDate || $modifiedDate >= $publishedDate)) $postSchema['dateModified'] = $modifiedDate->format(DATE_ATOM);
// Other bylines remain visible. Do not guess a person's identity/type or URL.
if (in_array(strtolower($authorName), ['sialsourcing', 'sialsourcing team'], true)) {
    $postSchema['author'] = ['@type' => 'Organization', '@id' => $canonicalOrigin . '/#organization', 'name' => $authorName];
}
$breadcrumbSchema = [
    '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>$canonicalOrigin . '/'],
        ['@type'=>'ListItem','position'=>2,'name'=>'Blog','item'=>$canonicalOrigin . '/blog'],
        ['@type'=>'ListItem','position'=>3,'name'=>$post['title'],'item'=>$shareUrl],
    ],
];
require_once __DIR__ . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($postSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script type="application/ld+json"><?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>

<div style="padding-top:80px;">

<!-- Hero -->
<section style="background:var(--navy);padding:4rem 0 3rem;">
  <div class="container" style="max-width:800px;">
    <div style="display:flex;gap:0.75rem;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;" class="reveal">
      <span class="blog-cat" style="background:rgba(201,168,76,0.15);color:var(--gold);padding:0.25rem 0.75rem;border-radius:50px;font-size:0.78rem;font-weight:700;"><?= e($post['category']) ?></span>
      <?php if ($publishedDate): ?><time datetime="<?= e($publishedDate->format(DATE_ATOM)) ?>" style="color:rgba(255,255,255,0.4);font-size:0.82rem;"><?= e($publishedDate->format('F j, Y')) ?></time><?php endif; ?>
      <span style="color:rgba(255,255,255,0.4);font-size:0.82rem;"><?= $authorName !== '' ? 'By ' . e($authorName) : '' ?></span>
    </div>
    <h1 style="color:var(--white);font-size:clamp(1.8rem,4vw,2.8rem);margin-bottom:1rem;" class="reveal delay-1"><?= e($post['title']) ?></h1>
    <?php if ($post['excerpt']): ?>
    <p style="color:rgba(255,255,255,0.6);font-size:1.05rem;line-height:1.7;" class="reveal delay-2"><?= e($post['excerpt']) ?></p>
    <?php endif; ?>
  </div>
</section>

<?php if ($featuredImage !== ''): ?>
<div style="max-width:800px;margin:0 auto;padding:0 2rem;">
  <img src="<?= e($featuredImage) ?>" alt="<?= e($post['title']) ?>" style="width:100%;border-radius:var(--radius);margin-top:-1px;box-shadow:0 20px 60px rgba(0,0,0,0.15);aspect-ratio:16/9;object-fit:cover;">
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
        <div style="font-weight:700;color:var(--text);"><?= e($authorName !== '' ? $authorName : 'SialSourcing') ?></div>
        <div style="font-size:0.85rem;color:var(--muted);">Sourcing and supply chain coordination in Pakistan</div>
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
      <?php foreach ($related as $r): $relatedImage = $blogImageUrl($r['featured_image'] ?? ''); ?>
      <a href="/blog/<?= e($r['slug']) ?>" class="blog-card">
        <div class="blog-img"><?= $relatedImage !== '' ? '<img src="'.e($relatedImage).'" alt="'.e($r['title']).'" loading="lazy">' : '<span class="card-placeholder">'.icon('file-text', 34).'</span>' ?></div>
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
  <p class="reveal delay-1">Tell us your product, quantity and destination market so we can plan the next sourcing steps.</p>
  <a href="/contact" class="btn btn-gold reveal delay-2">Get a Free Quote</a>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
