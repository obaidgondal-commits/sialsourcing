<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/instrument-data.php';

function instrument_view_text(mixed $value, string $fallback = ''): string
{
    return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : $fallback;
}

function instrument_view_attributes(array $product): array
{
    $attributes = [];
    foreach ($product['attributes'] ?? [] as $attribute) {
        $code = instrument_view_text($attribute['code'] ?? null);
        if ($code !== '') {
            $raw = instrument_view_text($attribute['value'] ?? null);
            $attributes[$code] = [
                'label' => instrument_view_text($attribute['label'] ?? null, ucfirst(str_replace('_', ' ', $code))),
                'raw' => $raw,
                'value' => $raw !== '' ? ucfirst(str_replace('_', ' ', $raw)) : 'Not specified',
                'unit' => instrument_view_text($attribute['unit'] ?? null),
            ];
        }
    }
    return $attributes;
}

function instrument_view_product_search(array $product): string
{
    return implode(' ', [
        instrument_view_text($product['sku'] ?? null),
        instrument_view_text($product['description'] ?? null),
        instrument_view_text($product['material'] ?? null),
        ...array_column(instrument_view_attributes($product), 'value'),
    ]);
}

// URL identity comes from the public path, never query parameters appended to a
// rewrite. This also prevents direct controller URLs becoming duplicate pages.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (!is_string($requestPath) || !preg_match('#^/(dental-instruments|surgical-instruments)/([a-z0-9]+(?:-[a-z0-9]+)*)(?:/([a-z0-9]+(?:-[a-z0-9]+)*))?/?$#D', $requestPath, $routeParts)) {
    require __DIR__ . '/404.php';
    exit;
}
$disciplinePath = $routeParts[1];
$groupSlug = $routeParts[2];
$familySlug = $routeParts[3] ?? '';
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    header('Allow: GET, HEAD');
    http_response_code(405);
    $pageTitle = 'Request Unavailable';
    $noindex = true;
    require __DIR__ . '/includes/header.php';
    echo '<section class="section section-cream" style="padding-top:10rem;"><div class="container"><h1>Please open an instrument family.</h1><p>Quote requests continue through our contact page.</p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
try {
    $instrumentData = instrument_catalogue();
} catch (Throwable $error) {
    error_log('Instrument catalogue is unavailable.');
    http_response_code(503);
    $pageTitle = 'Instrument Details';
    $noindex = true;
    require __DIR__ . '/includes/header.php';
    echo '<section class="section section-cream" style="padding-top:10rem;"><div class="container"><h1>Instrument details are being prepared.</h1><p>Please contact us with the instrument references you need.</p><a class="btn btn-gold" href="' . e(SITE_URL . '/contact?product=surgical-instruments') . '">Get a Quote</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$families = array_values(array_filter($instrumentData['families'] ?? [], static function (array $item) use ($disciplinePath, $groupSlug): bool {
    return ($item['base_path'] ?? '') === $disciplinePath && ($item['group_slug'] ?? '') === $groupSlug;
}));
$family = null;
foreach ($families as $candidate) {
    if (($candidate['slug'] ?? '') === $familySlug) {
        $family = $candidate;
        break;
    }
}
if (!$families || ($familySlug !== '' && !$family)) {
    require __DIR__ . '/404.php';
    exit;
}
$disciplineName = instrument_view_text($families[0]['discipline'] ?? null, 'Surgical Instruments');
$groupName = instrument_view_text($families[0]['group'] ?? null, 'Instrument');
$groupUrl = $families[0]['group_url'];
$products = $family['products'] ?? [];
$groupProductCount = array_sum(array_map(static fn (array $item): int => count($item['products'] ?? []), $families));
$pendingCount = 0;
foreach ($family ? [$family] : $families as $visibleFamily) {
    foreach ($visibleFamily['products'] ?? [] as $product) {
        $pendingCount += (int) (($product['review_status'] ?? '') !== 'verified');
    }
}
$preview = ($instrumentData['mode'] ?? '') === 'staging-preview' || $pendingCount > 0;
$canonicalPath = $family['url'] ?? $groupUrl;
$noindex = !empty($noindex) || $preview;
if ($noindex) {
    header('X-Robots-Tag: noindex, nofollow');
}
$pageTitle = $family ? $family['name'] : $groupName . ' Instruments — ' . $disciplineName;
$pageDesc = $family
    ? 'Explore variants and specifications for ' . $family['name'] . '. Select SialSourcing references for your instrument enquiry.'
    : 'Browse ' . count($families) . ' instrument families in ' . $groupName . '. Compare variants and prepare a sourcing enquiry with SialSourcing.';
$quoteFamilyUrl = SITE_URL . '/contact?product=surgical-instruments';
$query = is_string($_GET['q'] ?? null) ? substr(trim($_GET['q']), 0, 200) : '';
$activeFilters = is_array($_GET['filter'] ?? null) ? array_filter($_GET['filter'], 'is_string') : [];
$filterOptions = [];
foreach ($products as $product) {
    foreach (instrument_view_attributes($product) as $code => $attribute) {
        if ($attribute['raw'] !== '') {
            $filterOptions[$code]['label'] = $attribute['label'];
            $filterOptions[$code]['values'][$attribute['raw']] = $attribute['value'];
        }
    }
}
$filterOptions = array_slice($filterOptions, 0, 5, true);
$visibleCount = 0;
$instrumentCssVersion = @filemtime(__DIR__ . '/assets/css/instruments.css') ?: 1;
$instrumentJsVersion = @filemtime(__DIR__ . '/assets/js/instruments.js') ?: 1;
require __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/instruments.css?v=<?= $instrumentCssVersion ?>">
<script src="<?= SITE_URL ?>/assets/js/instruments.js?v=<?= $instrumentJsVersion ?>" defer></script>

<section class="pp-hero instrument-catalogue instrument-detail-hero">
  <div class="pp-hero-left reveal-left">
    <nav class="instrument-breadcrumbs" aria-label="Breadcrumb">
      <a href="<?= SITE_URL ?>/products">Products</a><span aria-hidden="true">/</span><a href="<?= SITE_URL ?>/surgical-instruments">Surgical Instruments</a>
      <?php if ($disciplinePath === 'dental-instruments'): ?><span aria-hidden="true">/</span><a href="<?= SITE_URL ?>/dental-instruments">Dental Instruments</a><?php endif; ?>
      <span aria-hidden="true">/</span><?php if ($family): ?><a href="<?= SITE_URL . e($groupUrl) ?>"><?= e($groupName) ?></a><?php else: ?><span aria-current="page"><?= e($groupName) ?></span><?php endif; ?>
    </nav>
    <div class="pp-eyebrow"><?= e($family ? ($family['code'] ?? $groupName) : $disciplineName) ?></div>
    <h1><?= e($family ? $family['name'] : $groupName . ' Instruments') ?></h1>
    <p class="pp-hero-desc"><?= e($family
        ? instrument_view_text($family['description'] ?? null, 'Explore the variants in this instrument family, compare their recorded specifications, and choose the references for your sourcing enquiry.')
        : 'Find related instrument patterns together. Browse the families below, compare their individual variants, and tell us which references you need.') ?></p>
    <div class="pp-hero-actions">
      <a href="<?= $family ? '#instrument-variants' : '#instrument-families' ?>" class="btn-primary"><?= $family ? 'Compare Variants' : 'Browse Instrument Families' ?> <?= icon('arrow-right', 17) ?></a>
      <a href="<?= $family ? e($quoteFamilyUrl) : SITE_URL . '/contact?product=surgical-instruments' ?>" class="btn-outline-light">Get a Free Quote</a>
    </div>
    <div class="pp-meta-strip">
      <div class="pp-meta-item"><div class="pp-meta-label"><?= $family ? 'Variants' : 'Families' ?></div><div class="pp-meta-value"><?= $family ? count($products) : count($families) ?></div></div>
      <div class="pp-meta-item"><div class="pp-meta-label"><?= $family ? 'Pattern' : 'Variants' ?></div><div class="pp-meta-value"><?= $family ? e(instrument_view_text($family['pattern'] ?? null, 'See specifications')) : $groupProductCount ?></div></div>
      <div class="pp-meta-item"><div class="pp-meta-label">Specifications</div><div class="pp-meta-value"><?= $pendingCount ? 'Awaiting review' : 'Reviewed' ?></div></div>
    </div>
  </div>
  <div class="pp-hero-right"><div class="pp-hero-ph"><?= icon('scissors', 190, 'pp-hero-ph-icon') ?></div><p class="instrument-photo-note">Photograph not available</p></div>
</section>

<?php if (!$family): ?>
<section class="section section-cream instrument-catalogue" id="instrument-families" data-instrument-browser="families">
  <div class="container">
    <div class="instrument-section-heading"><div><div class="section-label">Our Instrument Range</div><h2 class="section-title"><?= e($groupName) ?> Instrument Families</h2><p class="section-desc">Browse the full range or search for a family, pattern or instrument reference. Open a family to compare every available variant.</p></div></div>
    <?php if ($preview): ?><div class="instrument-review-note"><?= icon('file-text', 19) ?><p><strong>Catalogue preview.</strong> <?= $pendingCount ? 'Specifications are awaiting review. ' : '' ?>These details do not establish availability or market eligibility.</p></div><?php endif; ?>
    <form method="get" action="<?= SITE_URL . e($groupUrl) ?>" class="instrument-filter-form" data-instrument-filter-form role="search" aria-label="Find instrument families">
      <div class="field instrument-search-field"><label class="field-label" for="instrument-family-search">Find a family or reference</label><div class="instrument-search-input"><?= icon('search', 18) ?><input class="field-input" type="search" id="instrument-family-search" name="q" value="<?= e($query) ?>" placeholder="Search forceps, elevators, a pattern or SKU…" maxlength="200" data-instrument-search></div></div><button type="submit" class="btn btn-gold" data-instrument-filter-submit>Search Families</button><button type="button" class="instrument-text-button" data-instrument-reset hidden>Clear Search <?= icon('x', 14) ?></button>
    </form>
    <p class="instrument-result-count" data-instrument-results role="status" aria-live="polite"></p>
    <div class="products-grid instrument-family-grid">
      <?php foreach ($families as $item):
        $familySearch = implode(' ', [instrument_view_text($item['name'] ?? null), instrument_view_text($item['code'] ?? null), instrument_view_text($item['pattern'] ?? null), ...array_map('instrument_view_product_search', $item['products'])]);
        $matches = $query === '' || stripos($familySearch, $query) !== false;
        $visibleCount += (int) $matches;
      ?>
        <article class="product-card instrument-family-card" data-instrument-item data-search="<?= e($familySearch) ?>"<?= !$matches ? ' hidden' : '' ?>>
          <div class="instrument-card-top"><div class="product-icon"><?= icon('scissors', 23) ?></div><span class="instrument-variant-count"><?= count($item['products']) ?> variants</span></div>
          <span class="instrument-kicker"><?= e($item['code'] ?? '') ?></span><h3><a href="<?= SITE_URL . e($item['url']) ?>"><?= e($item['name']) ?></a></h3>
          <?php if (!empty($item['pattern'])): ?><p class="instrument-pattern"><?= e($item['pattern']) ?></p><?php endif; ?>
          <details class="instrument-family-preview"><summary>Preview this family <span aria-hidden="true">+</span></summary><ul><?php foreach (array_slice($item['products'], 0, 3) as $sample): ?><li><span><?= e(instrument_view_text($sample['description'] ?? null, 'Instrument variant')) ?></span><small><?= e($sample['sku']) ?></small></li><?php endforeach; ?></ul><?php if (count($item['products']) > 3): ?><p><?= count($item['products']) - 3 ?> more variants in this family.</p><?php endif; ?></details>
          <a class="product-link" href="<?= SITE_URL . e($item['url']) ?>">View All Variants <?= icon('arrow-right', 16) ?></a>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="instrument-empty" data-instrument-empty<?= $visibleCount > 0 ? ' hidden' : '' ?>><h3>No families match your search.</h3><p>Try a broader description or clear the search to explore all families.</p></div>
    <noscript><p class="instrument-result-count"><?= $visibleCount ?> of <?= count($families) ?> families shown.</p></noscript>
  </div>
</section>
<?php else: ?>
<section class="pp-content instrument-catalogue instrument-family-intro">
  <div class="pp-content-inner">
    <div class="pp-content-left reveal-left"><div class="section-label">Explore the Details</div><h2><?= e($family['name']) ?></h2><div class="pp-content-text"><p>Each variant has its own SialSourcing reference. Review the descriptions and recorded attributes below, then select the references you want to discuss.</p><p>For quantities, packaging or other requirements, include the details in your enquiry so they can be confirmed for your order.</p></div></div>
    <div class="pp-content-right reveal-right"><div class="instrument-family-summary"><h3>Family at a Glance</h3><dl><div><dt>Family reference</dt><dd class="instrument-code"><?= e($family['code'] ?? 'Not specified') ?></dd></div><div><dt>Discipline</dt><dd><?= e($disciplineName) ?></dd></div><div><dt>Group</dt><dd><a href="<?= SITE_URL . e($groupUrl) ?>"><?= e($groupName) ?></a></dd></div><div><dt>Pattern</dt><dd><?= e(instrument_view_text($family['pattern'] ?? null, 'Not specified')) ?></dd></div><div><dt>Variants</dt><dd><?= count($products) ?></dd></div></dl></div></div>
  </div>
</section>
<section class="section section-white instrument-catalogue" id="instrument-variants" data-instrument-browser="variants">
  <div class="container">
    <div class="instrument-section-heading"><div><div class="section-label">Individual Specifications</div><h2 class="section-title">Compare &amp; Select Your Variants</h2><p class="section-desc">Filter the range, open the specifications, and add the variants you need to your enquiry.</p></div></div>
    <?php if ($preview): ?><div class="instrument-review-note"><?= icon('file-text', 19) ?><p><strong><?= $pendingCount ? 'Specifications awaiting review.' : 'Catalogue preview.' ?></strong> Use this preview to explore the instrument range. Details and availability must be confirmed before ordering.</p></div><?php endif; ?>
    <form method="get" action="<?= SITE_URL . e($family['url']) ?>" class="instrument-filter-form" data-instrument-filter-form role="search" aria-label="Filter variants">
      <div class="field instrument-search-field"><label class="field-label" for="instrument-variant-search">Search variants</label><div class="instrument-search-input"><?= icon('search', 18) ?><input class="field-input" type="search" name="q" id="instrument-variant-search" value="<?= e($query) ?>" placeholder="Search a description or reference…" maxlength="200" data-instrument-search></div></div>
      <?php foreach ($filterOptions as $code => $option): ?><div class="field instrument-filter-field"><label class="field-label" for="instrument-filter-<?= e($code) ?>"><?= e($option['label']) ?></label><select class="field-input" id="instrument-filter-<?= e($code) ?>" name="filter[<?= e($code) ?>]" data-instrument-filter="<?= e($code) ?>"><option value="">All</option><?php foreach ($option['values'] as $raw => $label): ?><option value="<?= e((string) $raw) ?>"<?= ($activeFilters[$code] ?? '') === (string) $raw ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div><?php endforeach; ?>
      <button type="submit" class="btn btn-gold" data-instrument-filter-submit>Apply Filters</button>
    </form>
    <div class="instrument-table-toolbar"><p class="instrument-result-count" data-instrument-results role="status" aria-live="polite"></p><button type="button" class="instrument-text-button" data-instrument-reset hidden>Clear Filters <?= icon('x', 14) ?></button><span class="instrument-selection-count" role="status" aria-live="polite"><strong data-instrument-selected-count>0</strong> selected</span></div>
    <form action="<?= SITE_URL ?>/contact" method="get" id="instrument-quote" data-instrument-quote>
      <input type="hidden" name="product" value="surgical-instruments"><input type="hidden" name="family" value="<?= e($family['code'] ?? '') ?>">
      <div class="instrument-table-wrap"><table class="instrument-variant-table"><caption class="instrument-sr-only">Variants in <?= e($family['name']) ?>. Select references to include in your enquiry.</caption><thead><tr><th scope="col"><span class="instrument-sr-only">Select</span></th><th scope="col">Instrument / Reference</th><th scope="col">Material</th><th scope="col">Specifications</th></tr></thead><tbody>
        <?php foreach ($products as $product):
          $attributes = instrument_view_attributes($product);
          $searchText = instrument_view_product_search($product);
          $matches = $query === '' || stripos($searchText, $query) !== false;
          foreach ($activeFilters as $code => $value) {
              if ($value !== '' && ($attributes[$code]['raw'] ?? '') !== $value) $matches = false;
          }
          $visibleCount += (int) $matches;
        ?>
          <tr data-instrument-item data-search="<?= e($searchText) ?>" data-attributes="<?= e(json_encode(array_map(static fn (array $attribute): string => $attribute['raw'], $attributes), JSON_THROW_ON_ERROR)) ?>"<?= !$matches ? ' hidden' : '' ?>>
            <td class="instrument-select-cell"><label class="instrument-select-target"><input type="checkbox" name="skus[]" value="<?= e($product['sku']) ?>" data-instrument-select aria-label="Select <?= e(instrument_view_text($product['description'] ?? null, $product['sku'])) ?> (<?= e($product['sku']) ?>)"></label></td>
            <th scope="row" class="instrument-description-cell"><span class="instrument-name"><?= e(instrument_view_text($product['description'] ?? null, 'Instrument variant')) ?></span><span class="instrument-code"><?= e($product['sku']) ?></span><?php if (!empty($attributes['tooth_position'])): ?><span class="instrument-attribute-tag"><?= e($attributes['tooth_position']['value']) ?></span><?php endif; ?></th>
            <td class="instrument-material-cell" data-label="Material"><?= e(instrument_view_text($product['material'] ?? null, $attributes['material']['value'] ?? 'Not specified')) ?></td>
            <td class="instrument-spec-cell"><details class="instrument-spec-details"><summary>View Specifications <span aria-hidden="true">+</span></summary><dl><?php foreach ($attributes as $attribute): ?><div><dt><?= e($attribute['label']) ?></dt><dd><?= e($attribute['value'] . ($attribute['unit'] !== '' ? ' ' . $attribute['unit'] : '')) ?></dd></div><?php endforeach; ?><?php if (!$attributes): ?><div><dt>Specifications</dt><dd>Not yet available</dd></div><?php endif; ?></dl><?php if ($preview): ?><p class="instrument-spec-review"><?= ($product['review_status'] ?? '') === 'verified' ? 'Reviewed specification' : 'Specification awaiting review' ?></p><?php endif; ?></details></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
      <div class="instrument-empty" data-instrument-empty<?= $visibleCount > 0 ? ' hidden' : '' ?>><h3>No variants match these filters.</h3><p>Try another description or clear the filters.</p></div>
      <noscript><p class="instrument-result-count"><?= $visibleCount ?> of <?= count($products) ?> variants shown. Apply filters before selecting variants.</p></noscript>
      <div class="instrument-quote-bar"><div><strong><span data-instrument-selected-count>0</span> variants selected</strong><p>Your selection will be added to the existing enquiry form.</p><p class="instrument-selection-error" data-instrument-selection-error hidden>Please select at least one variant to continue.</p></div><button type="submit" class="btn btn-gold">Enquire About Selected Variants <?= icon('arrow-right', 17) ?></button></div>
    </form>
  </div>
</section>
<section class="pp-specs instrument-catalogue"><div class="pp-specs-inner"><h2>Preparing Your Instrument Enquiry</h2><div class="pp-specs-grid"><div class="pp-spec-card"><div class="pp-spec-label">Instrument References</div><div class="pp-spec-value">Select the individual SialSourcing references so your request identifies the variants you need.</div></div><div class="pp-spec-card"><div class="pp-spec-label">Quantities &amp; Packaging</div><div class="pp-spec-value">Include quantities per reference and any packaging or labelling requirements.</div></div><div class="pp-spec-card"><div class="pp-spec-label">Destination &amp; Requirements</div><div class="pp-spec-value">Tell us your destination and required documentation so these can be checked for your enquiry.</div></div></div></div></section>
<section class="section section-cream instrument-catalogue"><div class="container instrument-back-link"><a class="product-link" href="<?= SITE_URL . e($groupUrl) ?>">Browse All <?= e($groupName) ?> Families <?= icon('arrow-right', 16) ?></a><a class="product-link" href="<?= SITE_URL ?>/<?= e($disciplinePath) ?>">Back to <?= e($disciplineName) ?> <?= icon('arrow-right', 16) ?></a></div></section>
<?php endif; ?>

<section class="pp-cta instrument-catalogue"><h2>Ready to Source Your <em style="color:var(--gold);font-style:normal;">Instruments</em>?</h2><p>Tell us which instruments you need, your quantities and your requirements. We will review the details with you.</p><div class="pp-cta-actions"><a class="btn-primary" href="<?= $family ? '#instrument-variants' : SITE_URL . '/contact?product=surgical-instruments' ?>"><?= $family ? 'Select Variants for Your Enquiry' : 'Get a Free Sourcing Plan' ?></a><a class="btn-outline-light" href="<?= SITE_URL ?>/lab-qc">Our QC Process</a></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
