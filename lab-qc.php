<?php
$pageTitle = 'Quality Control & Lab Testing Pakistan — ISO, CE, FDA, AQL 2.5 | SialSourcing';
$pageDesc  = 'SialSourcing conducts rigorous lab tests and QC inspections on every order — ISO, CE, FDA, REACH compliance verified before shipment from Sialkot, Pakistan.';
$canonicalPath = '/lab-qc';
require_once __DIR__ . '/includes/header.php';
$labTests = db()->query("SELECT * FROM lab_tests WHERE is_active=1 ORDER BY sort_order")->fetchAll();
$labItems   = array_filter($labTests, fn($l) => $l['category'] === 'Lab Test');
$qcItems    = array_filter($labTests, fn($l) => $l['category'] === 'QC Process');
?>

<div style="padding-top:80px;">

<!-- Hero -->
<section style="background:var(--navy);padding:5rem 0 4rem;position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 60% at 30% 50%,rgba(201,168,76,0.1) 0%,transparent 70%);"></div>
  <div class="container hero-grid" style="position:relative;">
    <div>
      <div class="section-label reveal">Quality Assurance</div>
      <h1 style="color:var(--white);font-size:clamp(2rem,4vw,3.2rem);margin-bottom:1.25rem;" class="reveal delay-1">Lab Tests & QC That Protect Your Business</h1>
      <p style="color:rgba(255,255,255,0.6);font-size:1.05rem;margin-bottom:2rem;" class="reveal delay-2">Every order placed through SialSourcing goes through a documented quality assurance chain — from factory audit to pre-shipment inspection — before we release it for shipping.</p>
      <a href="/contact" class="btn btn-gold reveal delay-3">Request QC Report Sample</a>
    </div>
    <div class="reveal-right" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
      <?php foreach ([['factory','Factory Audits','Every manufacturer physically audited before onboarding'],['tool','Lab Tests','Third-party certified labs for product compliance'],['file-text','AQL Sampling','Statistical sampling per international AQL 2.5 standard'],['check-circle','Full Reports','Detailed QC report with photos sent to buyer before shipment']] as $c): ?>
      <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(201,168,76,0.15);border-radius:var(--radius);padding:1.5rem;">
        <div style="margin-bottom:0.75rem;color:var(--gold);"><?= icon($c[0], 24) ?></div>
        <div style="color:var(--white);font-weight:600;font-size:0.9rem;margin-bottom:0.4rem;"><?= $c[1] ?></div>
        <div style="color:rgba(255,255,255,0.45);font-size:0.8rem;"><?= $c[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Standards we comply with -->
<section class="section section-cream">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:3rem;">
      <div class="section-label">Compliance</div>
      <h2 class="section-title">Standards We Work To</h2>
      <p style="max-width:560px;margin:0 auto;">Different products demand different standards. Here are the key certifications and frameworks our manufacturers and lab partners operate under.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;">
      <?php foreach ([
        ['ISO 13485','Surgical Instruments','Medical device quality management — mandatory for export to US, EU, and AU markets.'],
        ['CE Marking','Medical & Sports Goods','European conformity mark — required for sale in all EU/EEA countries.'],
        ['FDA Compliance','Cutlery & Medical','US Food & Drug Administration standards for food-contact metals and medical devices.'],
        ['FIFA Quality Pro','Sports Goods','Highest FIFA standard for footballs — tested for weight, pressure, roundness, and rebound.'],
        ['REACH / RoHS','Leather & Metals','Restricts hazardous chemicals in leather tanning and metallic products for EU import.'],
        ['ISO 9001','All Products','General quality management — ensures consistent processes across the manufacturing facility.'],
        ['AQL 2.5','All Products','Industry-standard Acceptable Quality Limit for statistical pre-shipment sampling.'],
        ['SA8000','Ethical Sourcing','Social accountability standard ensuring fair labor, safe conditions, and no child labor.'],
      ] as $i => $std): ?>
      <div class="reveal delay-<?= ($i%4)+1 ?>" style="background:var(--white);border:1px solid var(--cream-dark);border-radius:var(--radius);padding:1.5rem;transition:all 0.3s;">
        <div style="font-size:0.75rem;font-weight:700;letter-spacing:1.5px;color:var(--gold);margin-bottom:0.4rem;text-transform:uppercase;"><?= $std[1] ?></div>
        <h3 style="font-size:1.1rem;margin-bottom:0.5rem;"><?= $std[0] ?></h3>
        <p style="font-size:0.84rem;"><?= $std[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Lab Tests -->
<section class="section section-white">
  <div class="container">
    <div class="reveal" style="margin-bottom:2.5rem;">
      <div class="section-label">Testing</div>
      <h2 class="section-title">Lab Tests We Conduct</h2>
    </div>

    <div class="lab-tabs">
      <button class="lab-tab active" data-filter="all">All</button>
      <button class="lab-tab" data-filter="Lab Test">Lab Tests</button>
      <button class="lab-tab" data-filter="QC Process">QC Processes</button>
    </div>

    <div class="lab-grid">
      <?php
      $fallbackLab = [
        ['Biocompatibility Testing','Lab Test','Surgical instruments tested for tissue compatibility and cytotoxicity — mandatory for medical device export.','ISO 10993','flask'],
        ['Tensile Strength Testing','Lab Test','Leather goods and sports equipment tested for durability under repeated stress and tension.','ASTM D412','zap'],
        ['Chemical Compliance Testing','Lab Test','REACH and RoHS testing for restricted substances in leather, dyes, and metal components.','REACH / RoHS','thermometer'],
        ['Corrosion Resistance Testing','Lab Test','Stainless steel cutlery and surgical instruments tested in salt spray chambers for rust resistance.','ASTM B117','shield'],
        ['Stitching & Seam Strength','Lab Test','Sports goods seams tested for burst strength, thread count integrity, and pressure under use.','FIFA Quality Pro','layers'],
        ['Microbial Contamination Test','Lab Test','Medical instruments tested for bacterial contamination and sterility assurance pre-packaging.','ISO 11135','activity'],
        ['Pre-Shipment Inspection','QC Process','Independent third-party inspection of finished goods batch against buyer-approved samples. Full photographic report included.','AQL 2.5','check-square'],
        ['Factory Audit','QC Process','Comprehensive on-site audit of manufacturing facility — covers premises, machinery, workforce, certifications, and ethical compliance.','SA8000, ISO 9001','clipboard'],
        ['In-Line Production QC','QC Process','Our QC supervisor visits the factory mid-production to catch defects before they multiply.','Internal SOP','eye'],
        ['Sample Approval Process','QC Process','Initial samples reviewed against tech pack specifications — dimensions, finish, materials, and function tested before production go-ahead.','Buyer Specs','check-circle'],
      ];
      $display = !empty($labTests) ? $labTests : $fallbackLab;
      foreach ($display as $i => $l):
        $cat = is_array($l) ? ($l['category'] ?? $l[1]) : '';
        $title = is_array($l) ? ($l['title'] ?? $l[0]) : '';
        $desc  = is_array($l) ? ($l['description'] ?? $l[2]) : '';
        $std   = is_array($l) ? ($l['standards'] ?? $l[3]) : '';
        $icon  = is_array($l) ? ($l['icon'] ?? $l[4]) : 'check';
      ?>
      <div class="lab-card reveal delay-<?= ($i%3)+1 ?>" data-category="<?= e($cat) ?>">
        <span class="lab-badge"><?= e($cat) ?></span>
        <h3><?= e($title) ?></h3>
        <p style="font-size:0.88rem;margin-top:0.5rem;"><?= e($desc) ?></p>
        <div class="lab-standards" style="display:flex;align-items:center;gap:0.35rem;"><?= icon('file-text', 13) ?> <?= e($std) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- QC Report sample explanation -->
<section class="section section-cream">
  <div class="container">
    <div class="two-col-grid">
      <div class="reveal-left">
        <div class="section-label">What You Get</div>
        <h2 class="section-title">Your QC Report Includes</h2>
        <p style="margin-bottom:1.5rem;">After every pre-shipment inspection, we send you a structured report before goods leave the factory. Nothing ships without your sign-off.</p>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:0.85rem;">
          <?php foreach ([
            'Production summary — quantities inspected, approved, rejected',
            'Photographic evidence — product close-ups, packaging, labelling',
            'Defect classification — critical, major, and minor defects catalogued',
            'AQL pass/fail result with statistical sampling table',
            'Measurements vs. spec sheet comparison',
            'Carton drop test and packaging integrity assessment',
            'Our QC manager\'s final recommendation',
          ] as $item): ?>
          <li style="display:flex;align-items:flex-start;gap:0.75rem;color:var(--text);font-size:0.92rem;">
            <span style="color:var(--gold);flex-shrink:0;margin-top:3px;display:inline-flex;"><?= icon('check-circle', 18) ?></span>
            <?= $item ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <a href="/contact" class="btn btn-gold" style="margin-top:2rem;">Request a Sample QC Report</a>
      </div>
      <div class="reveal-right">
        <div style="background:var(--navy);border-radius:var(--radius);padding:2.5rem;">
          <h3 style="color:var(--gold);margin-bottom:1.5rem;">Our QC Guarantee</h3>
          <p style="color:rgba(255,255,255,0.6);font-size:0.9rem;line-height:1.9;margin-bottom:1.5rem;">If a QC-passed order arrives with defects that were not present in the inspection report, <strong style="color:var(--white);">we pursue the manufacturer on your behalf</strong> at no additional cost.</p>
          <p style="color:rgba(255,255,255,0.6);font-size:0.9rem;line-height:1.9;margin-bottom:1.5rem;">We maintain ongoing relationships with all our manufacturers, which means our word carries weight on the factory floor — and yours is protected.</p>
          <div style="border-top:1px solid rgba(201,168,76,0.2);padding-top:1.25rem;font-size:0.82rem;color:rgba(255,255,255,0.4);">
            * Applies to orders where pre-shipment inspection was conducted by SialSourcing or an approved third-party inspector.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="cta-section">
  <h2 class="reveal">Questions About Our QC Process?</h2>
  <p class="reveal delay-1">Talk to our quality assurance team — we're happy to walk you through our inspection process before you place an order.</p>
  <a href="/contact" class="btn btn-gold reveal delay-2">Speak to Our QC Team</a>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
