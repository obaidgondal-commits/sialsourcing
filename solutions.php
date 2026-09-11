<?php
$pageTitle = 'Pakistan Sourcing Services — Supplier Review to Shipment';
$pageDesc = 'Explore SialSourcing services for supplier review, sampling, private-label development, production coordination, inspections and shipment planning.';
$canonicalPath = '/solutions';
require_once __DIR__ . '/includes/header.php';
// Reviewed public narrative. Legacy CMS service records require editorial review before reuse.
$schemaOrigin = rtrim(defined('CANONICAL_URL') ? CANONICAL_URL : 'https://sialsourcing.com', '/');
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'WebPage', 'name' => 'Pakistan sourcing services', 'url' => $schemaOrigin . $canonicalPath, 'description' => $pageDesc, 'inLanguage' => 'en', 'breadcrumb' => ['@id' => $schemaOrigin . $canonicalPath . '#breadcrumb'], 'mainEntity' => ['@id' => $schemaOrigin . $canonicalPath . '#service']],
        ['@type' => 'Service', '@id' => $schemaOrigin . $canonicalPath . '#service', 'name' => 'Product sourcing and order coordination', 'serviceType' => 'Sourcing coordination', 'description' => $pageDesc, 'provider' => ['@type' => 'Organization', 'name' => 'SialSourcing', 'url' => $schemaOrigin]],
        ['@type' => 'BreadcrumbList', '@id' => $schemaOrigin . $canonicalPath . '#breadcrumb', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $schemaOrigin . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Sourcing services', 'item' => $schemaOrigin . $canonicalPath],
        ]],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?></script>
<section class="guide-hero">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="/">Home</a> / <span aria-current="page">Sourcing services</span></nav>
    <p class="eyebrow">From enquiry to delivery</p>
    <h1>Give every stage<br>a clear next step.</h1>
    <p>Supplier review, samples, production, inspection and shipment: build the sourcing support your project needs around an agreed scope and a practical specification.</p>
    <a href="/contact" class="btn btn-gold">Discuss your project</a>
  </div>
</section>
<section class="editorial-section">
  <div class="container">
    <p class="eyebrow">Services</p>
    <h2>Coordinate the work around your order.</h2>
    <div class="category-grid">
      <article class="category-card">
        <p class="eyebrow">01 / Supplier review</p>
        <h3>Find a suitable production route</h3>
        <p>Review your brief against prospective suppliers' products, processes, order requirements and available evidence. Factory visits and document checks can be included in the agreed scope.</p>
      </article>
      <article class="category-card">
        <p class="eyebrow">02 / Sampling &amp; OEM</p>
        <h3>Make the product tangible</h3>
        <p>Coordinate samples, artwork, materials, labels and packaging. Record approvals and changes before the manufacturer proceeds with the agreed production specification.</p>
      </article>
      <article class="category-card">
        <p class="eyebrow">03 / Order coordination</p>
        <h3>Keep the milestones connected</h3>
        <p>Follow the agreed production schedule, gather updates and raise decisions that affect the order. Define the reporting frequency and who approves changes at the outset.</p>
      </article>
      <article class="category-card">
        <p class="eyebrow">04 / Quality checks</p>
        <h3>Inspect against agreed criteria</h3>
        <p>Plan the inspection stage, checks and sampling approach for your product. Where laboratory work is needed, agree on the method, provider, samples and deliverables separately.</p>
        <a href="/lab-qc" class="text-link">Explore quality control <span aria-hidden="true">→</span></a>
      </article>
      <article class="category-card">
        <p class="eyebrow">05 / Documentation &amp; freight</p>
        <h3>Prepare the shipment handover</h3>
        <p>Coordinate packing information, commercial documents and freight arrangements within the agreed delivery scope. Confirm responsibilities with the buyer, shipper and destination broker.</p>
      </article>
      <article class="category-card">
        <p class="eyebrow">06 / Follow-through</p>
        <h3>Keep the evidence together</h3>
        <p>Organize the agreed order records and coordinate supplier discussions when discrepancies arise. Corrective actions, replacements and claims follow the commercial terms of the order.</p>
      </article>
    </div>
  </div>
</section>
<section class="editorial-section">
  <div class="container guide-grid">
    <div class="guide-content">
      <h2>How a project moves forward.</h2>
      <ol>
        <li><strong>Define the brief.</strong> Share the product, specifications, quantities, destination and target dates.</li>
        <li><strong>Agree on the proposal.</strong> Confirm the sourcing scope, fees, responsibilities and expected deliverables.</li>
        <li><strong>Review the sample.</strong> Resolve construction, finish, branding and performance requirements, then record approval.</li>
        <li><strong>Coordinate production.</strong> Track the agreed milestones and document any changes affecting cost, quality or timing.</li>
        <li><strong>Review the findings.</strong> Evaluate the agreed inspection and test evidence before the release decision.</li>
        <li><strong>Arrange dispatch.</strong> Confirm packing, documents and freight instructions under the agreed delivery terms.</li>
      </ol>
      <h3>Define what the price includes.</h3>
      <p>Ask for product costs, sourcing services, samples, tooling, inspection, laboratory testing, packaging and freight to be identified clearly. Your proposal sets out which services are included and which require a separate quotation.</p>
      <p>Production timing depends on the product and supplier. Agree on when the schedule begins, how sample revisions affect it and which approvals are needed before the next stage.</p>
      <h3>Already have a manufacturer?</h3>
      <p>You can enquire about a focused inspection or coordination assignment. Provide the factory location, product information, order stage and checks you need so we can confirm availability and scope.</p>
      <a href="/qc-inspection-request" class="text-link">Request a QC inspection <span aria-hidden="true">→</span></a>
    </div>
    <aside class="guide-sidebar" aria-labelledby="services-brief-title">
      <p class="eyebrow">Start your project</p>
      <h2 id="services-brief-title">What do you need help with?</h2>
      <ul>
        <li>Finding a manufacturer</li>
        <li>Developing samples or private-label products</li>
        <li>Coordinating an existing order</li>
        <li>Planning inspection or testing</li>
        <li>Preparing a shipment</li>
      </ul>
      <p>Send your brief and identify the decisions or production stages where support would be most useful.</p>
      <a href="/contact" class="btn btn-gold">Request a sourcing proposal</a>
      <a href="/sourcing-regions" class="text-link">Explore manufacturing regions</a>
      <a href="/resources" class="text-link">Prepare your sourcing brief</a>
    </aside>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
