<?php
// Add instrument detail browsing inside the existing medical category pages.
// The original category hero, content, gallery, specifications and CTA remain.
if (!in_array($productSlug ?? '', ['surgical-instruments', 'dental-instruments'], true)) {
    return;
}
if (!is_file(__DIR__ . '/instrument-data.php')) {
    return;
}
require_once __DIR__ . '/instrument-data.php';
try {
    $instrumentData = instrument_catalogue();
} catch (Throwable $error) {
    error_log('Instrument detail section is unavailable.');
    return;
}
$instrumentFamilies = array_values(array_filter($instrumentData['families'] ?? [], static function (array $family) use ($productSlug): bool {
    return $productSlug === 'surgical-instruments' || ($family['base_path'] ?? '') === 'dental-instruments';
}));
if (!$instrumentFamilies) {
    return;
}
$instrumentGroups = [];
$instrumentProductCount = 0;
$instrumentPending = false;
foreach ($instrumentFamilies as $instrumentFamily) {
    $groupUrl = $instrumentFamily['group_url'];
    if (!isset($instrumentGroups[$groupUrl])) {
        $instrumentGroups[$groupUrl] = [
            'url' => $groupUrl,
            'name' => $instrumentFamily['group'] ?? 'Instruments',
            'discipline' => $instrumentFamily['discipline'] ?? 'Surgical Instruments',
            'families' => [],
            'count' => 0,
        ];
    }
    $instrumentGroups[$groupUrl]['families'][] = $instrumentFamily;
    $instrumentGroups[$groupUrl]['count'] += count($instrumentFamily['products'] ?? []);
    $instrumentProductCount += count($instrumentFamily['products'] ?? []);
    foreach ($instrumentFamily['products'] ?? [] as $instrumentProduct) {
        $instrumentPending = $instrumentPending || ($instrumentProduct['review_status'] ?? '') !== 'verified';
    }
}
$instrumentCssVersion = @filemtime(__DIR__ . '/../assets/css/instruments.css') ?: 1;
?>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/instruments.css?v=<?= $instrumentCssVersion ?>">
<section class="section section-white instrument-catalogue" id="instrument-catalogue" aria-labelledby="instrument-catalogue-heading">
  <div class="container">
    <div class="instrument-section-heading">
      <div>
        <div class="section-label">Explore Our Instrument Range</div>
        <h2 class="section-title" id="instrument-catalogue-heading">Browse Instruments in Detail</h2>
        <p class="section-desc">Explore related instrument families, compare individual specifications, and choose the references to include in your sourcing enquiry.</p>
      </div>
      <div class="instrument-range-count"><strong><?= count($instrumentFamilies) ?></strong><span>instrument families<br><?= $instrumentProductCount ?> variants</span></div>
    </div>
    <?php if ($instrumentPending): ?>
      <div class="instrument-review-note"><?= icon('file-text', 19) ?><p><strong>Catalogue preview.</strong> The detailed specifications below are awaiting review. Availability and requirements will be confirmed for your enquiry.</p></div>
    <?php endif; ?>
    <div class="instrument-group-grid">
      <?php foreach ($instrumentGroups as $instrumentGroup): ?>
        <article class="product-card instrument-group-card">
          <div class="instrument-group-intro">
            <div class="product-icon"><?= icon('scissors', 24) ?></div>
            <div><span class="instrument-kicker"><?= e($instrumentGroup['discipline']) ?></span><h3><?= e($instrumentGroup['name']) ?> Instruments</h3><p><?= count($instrumentGroup['families']) ?> families · <?= $instrumentGroup['count'] ?> variants</p></div>
          </div>
          <p class="instrument-group-description">Browse the families in this range, from the shared pattern to the individual variant details.</p>
          <ul class="instrument-family-links">
            <?php foreach (array_slice($instrumentGroup['families'], 0, 3) as $instrumentFeatured): ?>
              <li><a href="<?= SITE_URL . e($instrumentFeatured['url']) ?>"><span><?= e($instrumentFeatured['name']) ?></span><?= icon('arrow-right', 15) ?></a></li>
            <?php endforeach; ?>
          </ul>
          <a href="<?= SITE_URL . e($instrumentGroup['url']) ?>" class="btn btn-gold">Browse All <?= count($instrumentGroup['families']) ?> Families <?= icon('arrow-right', 17) ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
