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
$pageDesc      = $pageDesc ?? (!empty($product['meta_desc']) ? $product['meta_desc'] : $product['description']);
$canonicalPath = '/' . $product['slug'];
if (!empty($product['image'])) $ogImage = $product['image'];

require_once __DIR__ . '/includes/header.php';

// Honest structured data: no invented prices or ratings — factual
// product identity plus MOQ/lead time as properties.
$productSchema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'Product',
    'name'            => $product['title'],
    'description'     => $product['description'],
    'image'           => $productImage,
    'url'             => SITE_URL . '/' . $product['slug'],
    'brand'           => ['@type' => 'Brand', 'name' => 'SialSourcing'],
    'countryOfOrigin' => ['@type' => 'Country', 'name' => 'Pakistan'],
    'manufacturer'    => [
        '@type'   => 'Organization',
        'name'    => 'SialSourcing',
        'url'     => 'https://sialsourcing.com',
        'address' => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Sialkot',
            'addressRegion'   => 'Punjab',
            'addressCountry'  => 'PK',
        ],
    ],
    'additionalProperty' => [
        ['@type' => 'PropertyValue', 'name' => 'Minimum Order Quantity', 'value' => $moq],
        ['@type' => 'PropertyValue', 'name' => 'Lead Time', 'value' => $leadTime],
    ],
];

$crumbs = [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => SITE_URL . '/'],
    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Products', 'item' => SITE_URL . '/products'],
];
if ($parentRow) {
    $crumbs[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $parentRow['title'], 'item' => SITE_URL . '/' . $parentRow['slug']];
}
$crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $product['title'], 'item' => SITE_URL . '/' . $product['slug']];
$breadcrumbSchema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => $crumbs,
];
?>
<script type="application/ld+json"><?= json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/ld+json"><?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php if (!empty($faqs)):
$faqSchema = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type'          => 'Question',
        'name'           => $f['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
    ], $faqs),
];
?>
<script type="application/ld+json"><?= json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>

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
      <a href="/contact" class="btn-primary">Get a Free Quote</a>
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
        <div class="pp-meta-value">USD · EUR · GBP</div>
      </div>
      <div class="pp-meta-item">
        <div class="pp-meta-label">Inspection</div>
        <div class="pp-meta-value">AQL 2.5</div>
      </div>
    </div>
  </div>

  <div class="pp-hero-right">
    <?php if (!empty($product['image'])): ?>
      <img src="<?= e($product['image']) ?>"
           alt="<?= e($product['title']) ?> from Sialkot Pakistan"
           loading="eager">
      <div class="pp-hero-right-overlay"></div>
    <?php else: ?>
      <div class="pp-hero-ph"><?= icon($product['icon'] ?: 'box', 190, 'pp-hero-ph-icon') ?></div>
    <?php endif; ?>
  </div>
</section>

<!-- ═══ MAIN CONTENT + GALLERY ═══ -->
<section class="pp-content">
  <div class="pp-content-inner">

    <div class="pp-content-left reveal-left">
      <h2>World-Class <?= e($product['title']) ?> from Sialkot</h2>
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
             alt="<?= e($product['title']) ?> from Sialkot Pakistan"
             loading="lazy"
             style="grid-column:span 2;aspect-ratio:16/9;">
      </div>
      <?php else: ?>
      <div class="card-placeholder" style="aspect-ratio:4/3;border-radius:12px;">
        <?= icon($product['icon'] ?: 'box', 64) ?>
        <span>Product photography coming soon</span>
      </div>
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
        <div class="pp-spec-label">Certifications</div>
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
        <div class="pp-spec-value">AQL 2.5 pre-shipment inspection on every order</div>
      </div>

      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Payment Options</div>
        <div class="pp-spec-value">USD to Dallas · EUR/GBP to Paris</div>
      </div>

      <div class="pp-spec-card reveal">
        <div class="pp-spec-label">Origin</div>
        <div class="pp-spec-value">Sialkot, Punjab, Pakistan</div>
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
      <button class="faq-q"><?= e($f['question']) ?></button>
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
  <p>Tell us what you need. Within 24 hours you will have a manufacturer shortlist, compliance overview, and indicative pricing — at no cost and no obligation.</p>
  <div class="pp-cta-actions">
    <a href="/contact?product=<?= e($product['slug']) ?>" class="btn-primary">Get a Free Sourcing Plan</a>
    <a href="mailto:info@sialsourcing.com" class="btn-outline-light">Email Us Directly</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
