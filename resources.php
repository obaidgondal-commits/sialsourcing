<?php
$pageTitle     = 'Buyer Resources — Free Sourcing Templates, QC Report Sample & Import Checklists';
$pageDesc      = 'Free downloads for importers: sample QC inspection report, RFQ template, US import documents checklist, apparel tech pack template, and our company profile.';
$canonicalPath = '/resources';
require_once __DIR__ . '/includes/header.php';

$docs = [
    ['Sample QC Inspection Report', 'See exactly what you receive before any shipment is released — AQL tables, conformity checks, defect classification, and the release/hold recommendation. Specimen format.', '/assets/docs/Sample-QC-Inspection-Report.pdf', 'check-circle'],
    ['RFQ / Sourcing Brief Template', 'A one-page brief that gets you a sharper quote, faster. Fill it in and email it to us — or use the quote form.', '/assets/docs/RFQ-Sourcing-Brief-Template.pdf', 'file-text'],
    ['US Import Documents Checklist', 'Every document and filing a US importer needs for shipments from Pakistan — and the five mistakes that cause customs detentions.', '/assets/docs/US-Import-Documents-Checklist.pdf', 'flag'],
    ['Apparel & Uniform Tech Pack Template', 'Spec sheet for teamwear, activewear, and uniform orders: fabrics, measurements by size, decoration, construction notes.', '/assets/docs/Apparel-Uniform-Tech-Pack-Template.pdf', 'shirt'],
    ['QC Inspection Request Form', 'Printable version of our third-party inspection request — for orders you placed directly with a factory.', '/assets/docs/QC-Inspection-Request-Form.pdf', 'search'],
    ['SialSourcing Company Profile', 'One-page company profile for your procurement or vendor-onboarding file: categories, QC framework, commercial terms, offices.', '/assets/docs/SialSourcing-Company-Profile.pdf', 'briefcase'],
];
?>

<div style="padding-top:80px;">

<section style="background:var(--navy);padding:4rem 0 3rem;text-align:center;">
  <div class="container">
    <div class="section-label reveal">Buyer Resources</div>
    <h1 style="color:var(--white);font-size:clamp(2rem,4vw,3rem);margin-bottom:1rem;" class="reveal delay-1">Templates, Checklists &amp; Proof of Process</h1>
    <p style="color:rgba(255,255,255,0.55);max-width:560px;margin:0 auto;" class="reveal delay-2">Free, no email required. These are the working documents behind our sourcing process — use them even if you never order through us.</p>
  </div>
</section>

<?php require_once __DIR__ . '/includes/knowledge-base.php'; if (knowledge_available()): ?>
<!-- knowledge-resource-start -->
<section class="section section-white" aria-labelledby="resource-knowledge-title">
  <div class="container">
    <div class="section-label">Sialkot Industry Knowledge Base</div>
    <h2 class="section-title" id="resource-knowledge-title">Understand the Product. Ask Better Questions.</h2>
    <p class="section-desc">Explore source-linked guides to surgical instruments, sports goods and Sialkot industries, with practical questions to help you prepare your sourcing brief.</p>
    <a href="<?= SITE_URL ?>/knowledge-base" class="btn btn-gold">Explore the Knowledge Base <?= icon('arrow-right', 16) ?></a>
  </div>
</section>
<!-- knowledge-resource-end -->
<?php endif; ?>

<section class="section section-cream">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;">
      <?php foreach ($docs as $i => $d): ?>
      <div class="reveal delay-<?= ($i%3)+1 ?>" style="background:var(--white);border:1px solid var(--cream-dark);border-radius:var(--radius);padding:1.75rem;display:flex;flex-direction:column;transition:all var(--transition);">
        <div class="us-tile-icon" style="margin-bottom:1rem;"><?= icon($d[3], 20) ?></div>
        <h2 style="font-size:1.05rem;font-family:'DM Sans',sans-serif;font-weight:700;margin-bottom:0.5rem;"><?= e($d[0]) ?></h2>
        <p style="font-size:0.88rem;line-height:1.7;flex:1;margin-bottom:1.25rem;"><?= e($d[1]) ?></p>
        <a href="<?= e($d[2]) ?>" download class="btn btn-gold" style="justify-content:center;">Download PDF <?= icon('arrow-right', 15) ?></a>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="margin-top:3rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;">
      <div style="background:var(--navy);border-radius:var(--radius);padding:2rem;" class="reveal">
        <h3 style="color:var(--gold);font-size:1.1rem;margin-bottom:0.5rem;">Need an inspection, not a download?</h3>
        <p style="color:rgba(255,255,255,0.6);font-size:0.9rem;margin-bottom:1.25rem;">We inspect orders you placed directly with Pakistani factories — before you pay or ship.</p>
        <a href="/qc-inspection-request" class="btn btn-gold">Request a QC Inspection</a>
      </div>
      <div style="background:var(--white);border:1px solid var(--cream-dark);border-radius:var(--radius);padding:2rem;" class="reveal delay-1">
        <h3 style="font-size:1.1rem;margin-bottom:0.5rem;">Manufacturer in Pakistan?</h3>
        <p style="font-size:0.9rem;margin-bottom:1.25rem;">Join our vetted network and receive export orders from international buyers.</p>
        <a href="/for-manufacturers" class="btn btn-outline" style="color:var(--navy);border-color:var(--navy);">Partner With Us</a>
      </div>
    </div>
  </div>
</section>

<div class="cta-section">
  <h2 class="reveal">Prefer to Just Talk It Through?</h2>
  <p class="reveal delay-1">Send us your requirements — you'll have a sourcing plan and indicative pricing within 24 hours.</p>
  <a href="/contact" class="btn btn-gold reveal delay-2">Get a Free Quote</a>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
