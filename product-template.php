<?php
// product-template.php — renders a product category page from the DB.
// Wrapper pages set $productSlug (and optionally $pageTitle/$pageDesc)
// before requiring this file.
require_once __DIR__ . '/config.php';

$st = db()->prepare("SELECT * FROM products WHERE slug=? AND is_active=1");
$st->execute([$productSlug ?? '']);
$product = $st->fetch();

if (!$product) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$st2 = db()->prepare("SELECT * FROM product_gallery WHERE product_id=? ORDER BY sort_order");
$st2->execute([$product['id']]);
$gallery = $st2->fetchAll();

$st3 = db()->prepare("SELECT * FROM product_faqs WHERE product_id=? ORDER BY sort_order");
$st3->execute([$product['id']]);
$faqs = $st3->fetchAll();

$certs = !empty($product['certifications'])
    ? array_map('trim', explode(',', $product['certifications']))
    : [];

// Category <-> sub-page relationships (sub-pages use sort_order >= 100)
$categoryChildren = [
    'surgical-instruments'   => [['dental-instruments', 'Dental Instruments']],
    'custom-soccer-balls'    => [['promotional-soccer-balls', 'Promotional Soccer Balls']],
    'uniforms-tactical-wear' => [['security-guard-uniforms', 'Security Guard Uniforms'], ['medical-scrubs', 'Medical Scrubs & Lab Coats']],
];
$categoryParent = [];
foreach ($categoryChildren as $parentSlug => $kids) {
    foreach ($kids as $k) $categoryParent[$k[0]] = $parentSlug;
}
$children   = $categoryChildren[$product['slug']] ?? [];
$parentSlug = $categoryParent[$product['slug']] ?? null;
$parentRow  = null;
if ($parentSlug) {
    $stP = db()->prepare("SELECT title, slug FROM products WHERE slug=? AND is_active=1");
    $stP->execute([$parentSlug]);
    $parentRow = $stP->fetch() ?: null;
}

// Safe null-handling helpers
$heroHeadline  = !empty($product['hero_headline'])  ? $product['hero_headline']  : $product['title'];
$moq           = !empty($product['moq'])            ? $product['moq']            : 'Contact us';
$leadTime      = !empty($product['lead_time'])      ? $product['lead_time']      : 'Contact us';
$materials     = !empty($product['materials'])      ? $product['materials']      : '';
$videoUrl      = !empty($product['video_url'])      ? $product['video_url']      : '';
$productImage  = !empty($product['image'])          ? $product['image']          : SITE_URL . '/og-image.jpg';

// SEO contract for header.php — wrapper-set values win
$pageTitle     = $pageTitle ?? $product['title'] . ' from Sialkot, Pakistan';
$pageDesc      = !empty($product['meta_desc']) ? $product['meta_desc'] : ($pageDesc ?? $product['description']);
$productRegion = $product['slug'] === 'cutlery' ? 'Wazirabad' : 'Sialkot';
$canonicalPath = '/' . $product['slug'];
if (!empty($product['image'])) $ogImage = $product['image'];

require_once __DIR__ . '/includes/header.php';

// Category identity and sourcing service, not an individual manufactured item.
$productSchema = [
    '@context' => 'https://schema.org', '@type' => 'CollectionPage',
    'name' => $product['title'], 'description' => $product['description'],
    'url' => $canonicalUrl,
    'about' => ['@type'=>'Service','name'=>$product['title'].' sourcing',
        'serviceType'=>'Product sourcing and supply chain coordination',
        'provider'=>['@id'=>$canonicalOrigin.'/#organization']],
];

$crumbs = [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $canonicalOrigin . '/'],
    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Products', 'item' => $canonicalOrigin . '/products'],
];
if ($parentRow) {
    $crumbs[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $parentRow['title'], 'item' => $canonicalOrigin . '/' . $parentRow['slug']];
}
$crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $product['title'], 'item' => $canonicalOrigin . '/' . $product['slug']];
$breadcrumbSchema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => $crumbs,
];
?>
<script type="application/ld+json"><?= json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script type="application/ld+json"><?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<!-- ═══ HERO ═══ -->
<section class="pp-hero">
  <div class="pp-hero-left reveal-left">
    <div class="pp-eyebrow"><?= e($product['title']) ?></div>
    <h1><?= nl2br(e($heroHeadline)) ?></h1>
    <p class="pp-hero-desc"><?= e($product['description']) ?></p>

    <?php if (!empty($certs)): ?>
    <div class="pp-cert-strip">
      <?php foreach ($certs as $cert): ?>
        <span class="pp-cert-badge"><?= e($cert) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="pp-hero-actions">
      <a href="/contact?product=<?= e($product['slug']) ?>" class="btn-primary">Discuss this product</a>
      <a href="/lab-qc" class="btn-outline-light">Our QC Process</a>
    </div>

    <div class="pp-meta-strip">
      <div class="pp-meta-item">
        <div class="pp-meta-label">Min. Order</div>
        <div class="pp-meta-value"><?= e($moq) ?></div>
      </div>
      <div class="pp-meta-item">
        <div class="pp-meta-label">Lead Time</div>
        <div class="pp-meta-value"><?= e($leadTime) ?></div>
      </div>
      <div class="pp-meta-item">
        <div class="pp-meta-label">Payment</div>
        <div class="pp-meta-value">Agreed in quotation</div>
      </div>
      <div class="pp-meta-item">
        <div class="pp-meta-label">Inspection</div>
        <div class="pp-meta-value">Product-specific plan</div>
      </div>
    </div>
  </div>

  <div class="pp-hero-right">
    <?php if (!empty($product['image'])): ?>
      <img src="<?= e($product['image']) ?>"
           alt="<?= e($product['title']) ?> from <?= e($productRegion) ?> Pakistan"
           loading="eager">
      <div class="pp-hero-right-overlay"></div>
    <?php else: ?>
      <div class="category-brief"><p class="eyebrow">A better starting point</p><h2>Your product.<br>Your specification.</h2><dl><div><dt>Sourcing region</dt><dd><?= e($productRegion) ?>, Pakistan</dd></div><div><dt>Order quantity</dt><dd><?= e($moq) ?></dd></div><div><dt>Production schedule</dt><dd><?= e($leadTime) ?></dd></div></dl><p>Supplier capability and commercial terms are confirmed against your brief.</p></div>
    <?php endif; ?>
  </div>
</section>

<!-- ═══ MAIN CONTENT + GALLERY ═══ -->
<section class="pp-content">
  <div class="pp-content-inner">

    <div class="pp-content-left reveal-left">
      <h2>Sourcing <?= e($product['title']) ?> from <?= e($productRegion) ?></h2>
      <div class="pp-content-text">
        <?php if (!empty($product['details'])): ?>
          <?= $product['details'] ?>
        <?php else: ?>
          <p><?= e($product['description']) ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($materials)): ?>
      <div style="margin-top:2rem;padding:1.5rem;background:rgba(8,18,42,0.04);border-left:3px solid var(--gold);border-radius:0 8px 8px 0;">
        <div style="font-size:0.62rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:0.5rem;">Materials</div>
        <div style="font-size:0.9rem;color:var(--muted);line-height:1.7;"><?= nl2br(e($materials)) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <div class="pp-content-right reveal-right">
      <?php if (!empty($gallery)): ?>
      <div class="pp-gallery">
        <?php foreach ($gallery as $img): ?>
          <img src="<?= e($img['image']) ?>"
               alt="<?= e($product['title']) ?> — <?= e($img['caption'] ?? 'SialSourcing') ?>"
               loading="lazy"
               onclick="this.requestFullscreen && this.requestFullscreen()">
        <?php endforeach; ?>
      </div>
      <?php elseif (!empty($product['image'])): ?>
      <div class="pp-gallery">
        <img src="<?= e($product['image']) ?>"
             alt="<?= e($product['title']) ?> from <?= e($productRegion) ?> Pakistan"
             loading="lazy"
             style="grid-column:span 2;aspect-ratio:16/9;">
      </div>
      <?php else: ?>
      <aside class="sourcing-checklist"><h3>Prepare your product brief</h3><ul><li>Product pattern or reference</li><li>Materials, dimensions and finish</li><li>Quantity and packaging</li><li>Destination market and intended use</li><li>Required documentation and delivery date</li></ul><a class="text-link" href="/resources">Buyer templates</a></aside>
      <?php endif; ?>
    </div>

  </div>

  <!-- Video — only renders if video_url is not empty -->
  <?php if (!empty($videoUrl)): ?>
  <div class="pp-video reveal" style="margin-top:3rem;">
    <iframe src="<?= e($videoUrl) ?>"
            title="<?= e($product['title']) ?> — SialSourcing"
            allowfullscreen
            loading="lazy">
    </iframe>
  </div>
  <?php endif; ?>
</section>

<!-- ═══ SPECS ═══ -->
<section class="pp-specs">
  <div class="pp-specs-inner">
    <h2>Product Specifications</h2>
    <div class="pp-specs-grid">

      <?php if (!empty($certs)): ?>
      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Market requirements to verify</div>
        <div class="pp-spec-value"><?= e(implode(' · ', $certs)) ?></div>
      </div>
      <?php endif; ?>

      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Minimum Order Quantity</div>
        <div class="pp-spec-value"><?= e($moq) ?></div>
      </div>

      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Lead Time</div>
        <div class="pp-spec-value"><?= e($leadTime) ?></div>
      </div>

      <?php if (!empty($materials)): ?>
      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Materials</div>
        <div class="pp-spec-value"><?= nl2br(e($materials)) ?></div>
      </div>
      <?php endif; ?>

      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Quality Inspection</div>
        <div class="pp-spec-value">Sampling and acceptance criteria agreed for the product</div>
      </div>

      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Payment Options</div>
        <div class="pp-spec-value">Currency, payment schedule and contracting entity confirmed in your quotation</div>
      </div>

      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Origin</div>
        <div class="pp-spec-value"><?= e($productRegion) ?>, Punjab, Pakistan</div>
      </div>

      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Shipping</div>
        <div class="pp-spec-value">Sea freight · Air cargo · Express courier</div>
      </div>

    </div>
  </div>
</section>

<!-- ═══ FAQ ═══ -->
<?php if (!empty($faqs)): ?>
<section class="pp-faq">
  <div class="pp-faq-inner">
    <h2>Frequently Asked Questions</h2>
    <?php foreach ($faqs as $f): ?>
    <div class="faq-item">
      <h3 class="faq-q"><?= e($f['question']) ?></h3>
      <div class="faq-a"><?= nl2br(e($f['answer'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($children) || $parentRow): ?>
<!-- ═══ RELATED PAGES ═══ -->
<section class="section section-cream" style="padding:3rem 0;">
  <div class="container" style="max-width:1000px;">
    <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;justify-content:center;">
      <span style="font-size:0.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);">
        <?= $parentRow ? 'Part of' : 'Specialized pages' ?>
      </span>
      <?php if ($parentRow): ?>
        <a href="/<?= e($parentRow['slug']) ?>" class="share-pill" style="border-color:var(--gold);color:var(--navy);font-weight:600;"><?= e($parentRow['title']) ?> <?= icon('arrow-right', 14) ?></a>
      <?php endif; ?>
      <?php foreach ($children as $child): ?>
        <a href="/<?= e($child[0]) ?>" class="share-pill" style="border-color:var(--gold);color:var(--navy);font-weight:600;"><?= e($child[1]) ?> <?= icon('arrow-right', 14) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══ CTA ═══ -->
<section class="pp-cta">
  <h2>Ready to Source <em style="color:var(--gold);font-style:normal;"><?= e($product['title']) ?></em>?</h2>
  <p>Share your specification, quantity and destination. We will review the brief and confirm the quotation and sampling steps.</p>
  <div class="pp-cta-actions">
    <a href="/contact?product=<?= e($product['slug']) ?>" class="btn-primary">Send your sourcing brief</a>
    <a href="mailto:info@sialsourcing.com" class="btn-outline-light">Email Us Directly</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
