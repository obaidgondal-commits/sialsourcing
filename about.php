<?php
$pageTitle = 'About SialSourcing — Your Pakistan Sourcing Partner';
$pageDesc = 'Meet SialSourcing: product sourcing, supplier coordination, sampling, inspection and shipment planning for international buyers sourcing from Pakistan.';
$canonicalPath = '/about';
require_once __DIR__ . '/includes/header.php';
$schemaOrigin = rtrim(defined('CANONICAL_URL') ? CANONICAL_URL : 'https://sialsourcing.com', '/');
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        ['@type' => 'AboutPage', 'name' => 'About SialSourcing', 'url' => $schemaOrigin . $canonicalPath, 'description' => $pageDesc, 'inLanguage' => 'en', 'breadcrumb' => ['@id' => $schemaOrigin . $canonicalPath . '#breadcrumb']],
        ['@type' => 'BreadcrumbList', '@id' => $schemaOrigin . $canonicalPath . '#breadcrumb', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $schemaOrigin . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'About', 'item' => $schemaOrigin . $canonicalPath],
        ]],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?></script>
<section class="guide-hero">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="/">Home</a> / <span aria-current="page">About</span></nav>
    <p class="eyebrow">About SialSourcing</p>
    <h1>A partner close<br>to the product.</h1>
    <p>We help international buyers turn a product requirement into a sourcing project: from supplier conversations and sample approval to inspection and shipment coordination.</p>
    <a href="/contact" class="btn btn-gold">Tell us what you want to make</a>
  </div>
</section>
<section class="editorial-section">
  <div class="container guide-grid">
    <div class="guide-content">
      <p class="eyebrow">Our role</p>
      <h2>Connect the brief to the people making it.</h2>
      <p>SialSourcing is a sourcing and supply-chain partner based around Pakistan's manufacturing regions. We work with buyers to clarify specifications, review potential manufacturers and coordinate the work needed to move an order forward.</p>
      <p>A sourcing decision involves more than finding someone who makes a similar product. The construction, order size, destination, documentation and delivery requirements all affect which supplier is suitable. Our role is to bring those details into the conversation early.</p>
      <h3>Built around your product</h3>
      <p>Our sourcing enquiries cover instruments, sports goods, apparel, leather, cutlery and textiles. We help establish the appropriate production route across <a href="/sialkot-sourcing">Sialkot</a>, <a href="/wazirabad-sourcing">Wazirabad</a> and <a href="/faisalabad-sourcing">Faisalabad</a>, with supplier suitability and the available service scope confirmed for each project.</p>
      <h3>Clear decisions at each stage</h3>
      <p>Agreeing on the sample, specification and acceptance criteria gives buyers and manufacturers a shared reference. Documenting changes helps keep the quotation, production instructions and inspection plan aligned.</p>
      <p>We coordinate the services agreed for your order, which may include supplier review, sample development, branding, production updates, inspections, testing arrangements and logistics. Your proposal sets out the deliverables, fees, responsibilities and timing.</p>
      <h3>People you can speak to</h3>
      <p>Our team connects operations in Sialkot with buyer contacts in Dallas and Paris. Share your destination and preferred contact arrangements so the enquiry reaches the appropriate team member.</p>
      <p>For commercial arrangements, confirm the contracting entity, invoicing details, payment schedule and delivery terms in the written proposal.</p>
      <a href="/team" class="text-link">Meet the team <span aria-hidden="true">→</span></a>
    </div>
    <aside class="guide-sidebar" aria-labelledby="about-start-title">
      <p class="eyebrow">Start with the brief</p>
      <h2 id="about-start-title">A useful first conversation.</h2>
      <p>Tell us the product, quantity, destination and delivery target. A drawing, reference photograph or tech pack helps us understand what you need.</p>
      <p>Include the details that matter most to your buyer: materials, dimensions, finish, branding and any required evidence.</p>
      <a href="/contact" class="btn btn-gold">Start a sourcing enquiry</a>
      <a href="/solutions" class="text-link">Explore our services</a>
      <a href="/products" class="text-link">Browse product categories</a>
    </aside>
  </div>
</section>
<section class="editorial-section">
  <div class="container">
    <p class="eyebrow">How we approach an order</p>
    <h2>Specific requirements. Documented decisions.</h2>
    <div class="category-grid">
      <article class="category-card">
        <h3>A practical specification</h3>
        <p>Translate references and expectations into details a manufacturer can quote and a buyer can approve.</p>
        <a href="/resources" class="text-link">Buyer resources <span aria-hidden="true">→</span></a>
      </article>
      <article class="category-card">
        <h3>Evidence for the decision</h3>
        <p>Identify the samples, inspection findings and product documents needed at each agreed approval stage.</p>
        <a href="/lab-qc" class="text-link">Quality-control approach <span aria-hidden="true">→</span></a>
      </article>
      <article class="category-card">
        <h3>A coordinated handover</h3>
        <p>Align packaging, dispatch information and shipment arrangements with the delivery terms in your order.</p>
        <a href="/solutions" class="text-link">Sourcing process <span aria-hidden="true">→</span></a>
      </article>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
