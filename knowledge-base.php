<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/knowledge-base.php';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (!is_string($path) || !preg_match('#^/knowledge-base(?:/([a-z0-9]+(?:-[a-z0-9]+)*))?/?$#D', $path, $route)) {
    require __DIR__ . '/404.php'; exit;
}
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    header('Allow: GET, HEAD'); http_response_code(405); exit('Please open a guide.');
}
$articles = knowledge_articles(instrument_is_staging());
$slug = $route[1] ?? '';
$policy = $slug === 'editorial-policy';
$article = $articles[$slug] ?? null;
if (!$articles || ($slug !== '' && !$policy && !$article)) {
    require __DIR__ . '/404.php'; exit;
}
$query = is_string($_GET['q'] ?? null) ? substr(trim($_GET['q']), 0, 200) : '';
$industry = is_string($_GET['industry'] ?? null) ? substr($_GET['industry'], 0, 80) : '';
$industries = array_values(array_unique(array_column($articles, 'industry')));
$filtered = knowledge_search($articles, $query, $industry);
$canonicalPath = '/knowledge-base' . ($slug !== '' ? '/' . $slug : '');
$pageTitle = $article['title'] ?? ($policy ? 'How We Prepare and Review Our Guides' : 'Sialkot Industry Knowledge Base');
$headingTitle = $pageTitle;
$pageDesc = $article['summary'] ?? 'Practical, source-linked guides to surgical instruments, sports goods and sourcing from Sialkot. Explore specifications, supplier evidence and buyer questions.';
$noindex = instrument_is_staging() || ($article && $article['status'] !== 'published');
if ($noindex) header('X-Robots-Tag: noindex, nofollow');
require __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/knowledge.css?v=<?= filemtime(__DIR__ . '/assets/css/knowledge.css') ?>">
<div class="knowledge">
<section class="knowledge-hero"><div class="container">
  <nav class="knowledge-breadcrumbs" aria-label="Breadcrumb"><a href="<?= SITE_URL ?>/resources">Resources</a><span aria-hidden="true">/</span><?php if ($slug): ?><a href="<?= SITE_URL ?>/knowledge-base">Knowledge Base</a><span aria-hidden="true">/</span><span aria-current="page"><?= e($article['industry'] ?? 'Editorial Policy') ?></span><?php else: ?><span aria-current="page">Knowledge Base</span><?php endif; ?></nav>
  <div class="section-label"><?= e($article['industry'] ?? 'Know What You Are Sourcing') ?></div>
  <h1><?= e($headingTitle) ?></h1><p><?= e($pageDesc) ?></p>
  <?php if ($article): ?><div class="knowledge-meta"><span>Prepared <?= e($article['prepared_on']) ?></span><span><?= $article['status'] === 'published' ? 'Reviewed by ' . e($article['reviewer']) . ' · ' . e($article['reviewed_on']) : 'Draft · specialist review pending' ?></span></div><?php endif; ?>
</div></section>
<?php if ($policy): ?>
<section class="section section-white"><div class="container knowledge-policy">
  <div class="section-label">Our Editorial Approach</div><h2>Useful guidance, with evidence you can check.</h2>
  <p>The knowledge base is intended to help buyers describe a product, ask better supplier questions and understand the evidence behind a claim. SialSourcing is a sourcing intermediary; a guide is not a product approval or a substitute for a manufacturer's instructions.</p>
  <h3>Sources and scope</h3><p>Guides link to the primary organizations behind technical or industry information. Practical buyer checklists are identified as our proposed sourcing process. A source about an industry or a quality programme does not certify an individual supplier or product.</p>
  <h3>Preparation and review</h3><p>The initial drafts were prepared with AI assistance using the linked sources. They have not yet been approved by a named specialist. Drafts are available in the private preview only. Publication requires a recorded reviewer, a review date and a deliberate change of status.</p>
  <h3>Product photographs</h3><p>We use our own or supplier-authorized photographs. Each image must be matched to its exact reference before it appears. Where a family illustration shows one variant, its caption identifies that variant. Photography alone cannot establish material composition, sterility, performance or market eligibility.</p>
  <h3>Corrections and updates</h3><p>Each guide carries its preparation date; a review date appears only after review. If you spot an error, send the guide title, the disputed statement and a supporting source through our contact page. Revised technical content must be reviewed again before publication.</p>
  <a class="btn btn-gold" href="<?= SITE_URL ?>/contact">Suggest a Correction <?= icon('arrow-right', 16) ?></a>
</div></section>
<?php elseif ($article): ?>
<section class="section section-white"><div class="container knowledge-layout">
  <aside class="knowledge-aside"><nav aria-label="In this guide"><h2>In This Guide</h2><?php foreach ($article['sections'] as $i => $section): ?><a href="#section-<?= $i + 1 ?>"><?= e($section['title']) ?></a><?php endforeach; ?><a href="#guide-sources">Sources &amp; Further Reading</a></nav><p>Prepared with AI assistance from the sources below. <?= $article['status'] === 'published' ? 'Reviewer: ' . e($article['reviewer']) . '.' : 'Technical review is pending.' ?></p><a href="<?= SITE_URL ?>/knowledge-base/editorial-policy">How we prepare our guides</a></aside>
  <article class="knowledge-article">
    <?php if ($article['status'] !== 'published'): ?><p class="knowledge-review">Draft for review. Confirm product requirements and supporting evidence before relying on this guide.</p><?php endif; ?>
    <?php foreach ($article['sections'] as $i => $section): ?><section id="section-<?= $i + 1 ?>"><h2><?= e($section['title']) ?></h2><?php foreach ($section['paragraphs'] ?? [] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?><?php if (!empty($section['bullets'])): ?><ul><?php foreach ($section['bullets'] as $bullet): ?><li><?= e($bullet) ?></li><?php endforeach; ?></ul><?php endif; ?><?php if (!empty($section['sources'])): ?><p class="knowledge-citations">Sources: <?php foreach ($section['sources'] as $n => $sourceId): $source = $article['sources'][$sourceId]; ?><?= $n ? ' · ' : '' ?><a href="<?= e($source['url']) ?>" rel="noopener"><?= e($source['publisher']) ?></a><?php endforeach; ?></p><?php endif; ?></section><?php endforeach; ?>
    <section id="guide-sources" class="knowledge-sources"><h2>Sources &amp; Further Reading</h2><p>Sources consulted <?= e($article['prepared_on']) ?>. Requirements and programmes can change; check the linked publisher for the current text.</p><ol><?php foreach ($article['sources'] as $source): ?><li><a href="<?= e($source['url']) ?>" rel="noopener"><?= e($source['title']) ?></a><span><?= e($source['publisher']) ?></span></li><?php endforeach; ?></ol></section>
    <div class="knowledge-next"><h2>Put the Guide to Work</h2><p><?= e($article['next_step']) ?></p><?php foreach ($article['related'] as $link): ?><a class="product-link" href="<?= SITE_URL . e($link['path']) ?>"><?= e($link['label']) ?> <?= icon('arrow-right', 16) ?></a><?php endforeach; ?></div>
  </article>
</div></section>
<?php else: ?>
<section class="section section-cream"><div class="container">
  <div class="knowledge-intro"><h2>Start with the questions that matter.</h2><p>Understand product specifications, compare supplier evidence and prepare a clear sourcing brief. Each guide connects practical questions with sources you can follow.</p></div>
  <?php if (instrument_is_staging()): ?><p class="knowledge-review">Private preview · the first guides are drafts awaiting specialist review.</p><?php endif; ?>
  <form class="knowledge-search" method="get" action="<?= SITE_URL ?>/knowledge-base" role="search" aria-label="Search knowledge base"><div><label for="knowledge-q">Search guides</label><input id="knowledge-q" name="q" type="search" class="field-input" value="<?= e($query) ?>" maxlength="200" placeholder="Try instruments, footballs or leather"></div><div><label for="knowledge-industry">Industry</label><select id="knowledge-industry" name="industry" class="field-input"><option value="">All industries</option><?php foreach ($industries as $option): ?><option value="<?= e($option) ?>"<?= $industry === $option ? ' selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div><button class="btn btn-gold" type="submit">Find Guides <?= icon('search', 16) ?></button></form>
  <p class="knowledge-count" role="status"><?= count($filtered) ?> <?= count($filtered) === 1 ? 'guide' : 'guides' ?><?= $query !== '' ? ' matching “' . e($query) . '”' : ' available' ?>. <?php if ($query !== '' || $industry !== ''): ?><a href="<?= SITE_URL ?>/knowledge-base">Clear filters</a><?php endif; ?></p>
  <div class="knowledge-grid"><?php foreach ($filtered as $key => $item): ?><article class="product-card knowledge-card"><div class="knowledge-card-top"><span class="section-label"><?= e($item['industry']) ?></span><?= icon($item['icon'], 25) ?></div><h2><a href="<?= SITE_URL ?>/knowledge-base/<?= e($key) ?>"><?= e($item['title']) ?></a></h2><p><?= e($item['summary']) ?></p><span class="knowledge-card-status"><?= $item['status'] === 'published' ? 'Reviewed guide' : 'Draft · review pending' ?></span><a class="product-link" href="<?= SITE_URL ?>/knowledge-base/<?= e($key) ?>">Read the Guide <?= icon('arrow-right', 16) ?></a></article><?php endforeach; ?></div>
  <?php if (!$filtered): ?><div class="knowledge-empty"><h2>No guides match these filters.</h2><p>Try another term or choose all industries.</p><a class="product-link" href="<?= SITE_URL ?>/knowledge-base">Browse All Guides <?= icon('arrow-right', 16) ?></a></div><?php endif; ?>
  <div class="knowledge-editorial"><div><h2>See where the information comes from.</h2><p>Sources, review status and product evidence belong alongside the advice.</p></div><a class="btn btn-outline" href="<?= SITE_URL ?>/knowledge-base/editorial-policy">Our Editorial Approach</a></div>
</div></section>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
