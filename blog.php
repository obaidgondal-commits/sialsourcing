<?php
require_once __DIR__ . '/config.php';

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

$cat = $_GET['cat'] ?? '';
$pageInput = $_GET['page'] ?? '1';
if (!is_string($cat) || strlen($cat) > 100 || !is_string($pageInput) || !preg_match('/^[1-9][0-9]{0,5}$/D', $pageInput)) {
    http_response_code(404); include __DIR__ . '/404.php'; exit;
}
$cat = trim($cat);
$page = (int)$pageInput;
$perPage = 12;
$visibleWhere = 'is_published=1 AND COALESCE(published_at, created_at) <= ?';
$categoryQuery = db()->prepare("SELECT DISTINCT category FROM blog_posts WHERE $visibleWhere ORDER BY category");
$categoryQuery->execute([$now]);
$cats = $categoryQuery->fetchAll(PDO::FETCH_COLUMN);
if ($cat !== '' && !in_array($cat, $cats, true)) {
    http_response_code(404); include __DIR__ . '/404.php'; exit;
}
$params = [$now];
if ($cat !== '') { $visibleWhere .= ' AND category=?'; $params[] = $cat; }
$countQuery = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE $visibleWhere");
$countQuery->execute($params);
$totalPosts = (int)$countQuery->fetchColumn();
$totalPages = max(1, (int)ceil($totalPosts / $perPage));
if ($page > $totalPages) { http_response_code(404); include __DIR__ . '/404.php'; exit; }
$offset = ($page - 1) * $perPage;
// LIMIT/OFFSET are bounded integers derived above, never raw request strings.
$st = db()->prepare("SELECT * FROM blog_posts WHERE $visibleWhere ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT $perPage OFFSET $offset");
$st->execute($params);
$posts = $st->fetchAll();
$blogPageUrl = static function (int $number) use ($cat): string {
    $query = [];
    if ($cat !== '') $query['cat'] = $cat;
    if ($number > 1) $query['page'] = $number;
    return '/blog' . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
};
$canonicalPath = $blogPageUrl($page);
// Filter views are navigation aids; unfiltered pagination remains indexable.
$noindex = $cat !== '';
$pageTitle = 'Sourcing Insights & Guides' . ($page > 1 ? ' — Page ' . $page : '');
$pageDesc = 'Practical articles and buyer guides for sourcing products from Pakistan: specifications, supplier selection, quality checks and shipment planning.';
require_once __DIR__ . '/includes/header.php';
?>

<div style="padding-top:80px;">

<section style="background:var(--navy);padding:4rem 0 3rem;text-align:center;">
  <div class="container">
    <div class="section-label reveal">Insights</div>
    <h1 style="color:var(--white);font-size:clamp(2rem,4vw,3rem);margin-bottom:1rem;" class="reveal delay-1">Sourcing Knowledge Hub</h1>
    <p style="color:rgba(255,255,255,0.55);max-width:520px;margin:0 auto 2rem;" class="reveal delay-2">Practical guides and sourcing tips for international buyers working with manufacturers in Pakistan.</p>
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
      <?php foreach ($posts as $i => $p): $cardImage = $blogImageUrl($p['featured_image'] ?? ''); $cardDate = $blogDate($p['published_at'] ?? null); ?>
      <a href="/blog/<?= e($p['slug']) ?>" class="blog-card reveal delay-<?= ($i%3)+1 ?>" style="display:block;">
        <div class="blog-img">
          <?php if ($cardImage !== ''): ?>
            <img src="<?= e($cardImage) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
          <?php else: ?>
            <span class="card-placeholder"><?= icon('file-text', 34) ?></span>
          <?php endif; ?>
        </div>
        <div class="blog-body">
          <div class="blog-meta">
            <span class="blog-cat"><?= e($p['category']) ?></span>
            <?php if ($cardDate): ?><time datetime="<?= e($cardDate->format(DATE_ATOM)) ?>"><?= e($cardDate->format('M j, Y')) ?></time><?php endif; ?>
            <?php if (!empty($p['author'])): ?><span>By <?= e($p['author']) ?></span><?php endif; ?>
          </div>
          <h3><?= e($p['title']) ?></h3>
          <p><?= e(function_exists('mb_substr') ? mb_substr(strip_tags($p['excerpt'] ?? ''), 0, 150) : strip_tags($p['excerpt'] ?? '')) ?></p>
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
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Blog pagination" style="display:flex;gap:1rem;justify-content:center;align-items:center;flex-wrap:wrap;margin-top:2.5rem;">
      <?php if ($page > 1): ?><a href="<?= e($blogPageUrl(1)) ?>" class="share-pill">First page</a><a href="<?= e($blogPageUrl($page - 1)) ?>" class="share-pill" rel="prev">Previous</a><?php endif; ?>
      <span aria-current="page">Page <?= $page ?> of <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a href="<?= e($blogPageUrl($page + 1)) ?>" class="share-pill" rel="next">Next</a><?php endif; ?>
    </nav>
    <?php endif; ?>
  </div>
</section>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
