<?php
$pageTitle = 'Our Team — Meet the People Behind SialSourcing';
$pageDesc  = 'Meet the SialSourcing team — quality inspectors, logistics managers, and client relations experts across Dallas, Paris, and Sialkot working to deliver your supply chain.';
$canonicalPath = '/team';
require_once __DIR__ . '/includes/header.php';

// Try to load team members from database
$team = [];
try {
    $team = db()->query("SELECT * FROM team_members WHERE is_active=1 ORDER BY sort_order")->fetchAll();
} catch (Exception $e) {
    $team = [];
}
?>

<div style="padding-top:calc(70px + var(--safe-top, 0px));">

<!-- Hero -->
<section style="background:var(--navy);padding:4rem 0 3rem;text-align:center;">
  <div class="container">
    <div class="section-label reveal">Our Team</div>
    <h1 style="color:var(--white);font-size:clamp(2rem,4vw,3rem);margin-bottom:1rem;" class="reveal delay-1">The People Who Protect<br><em style="color:var(--gold);font-style:normal;">Your Supply Chain</em></h1>
    <p style="color:rgba(255,255,255,0.55);max-width:600px;margin:0 auto;font-size:1.05rem;line-height:1.8;" class="reveal delay-2">We're not a one-man operation. SialSourcing is a dedicated team of quality inspectors, logistics coordinators, lab technicians, and client managers — across three countries.</p>
  </div>
</section>

<!-- Team Structure -->
<section class="section section-white">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto 2.5rem;" class="reveal">
      <div class="section-label">How We're Structured</div>
      <h2 class="section-title">Three Offices. Five Departments. One Goal.</h2>
      <p>Every department exists for one reason: to make sure your order arrives at your door — on time, on spec, and on budget.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr;gap:1.25rem;max-width:900px;margin:0 auto;" class="dept-grid">
      <?php
      $departments = [
        ['tool','Quality Control & Lab Testing','Sialkot','Our largest department. QC inspectors and lab technicians who physically inspect every order at our own facility. They run tensile tests, check stitching quality, verify dimensions against spec sheets, and ensure compliance certificates are valid. Nothing ships without their approval.','8+ inspectors & technicians'],
        ['factory','Manufacturer Relations','Sialkot','The team that finds, audits, and manages our network of 100+ vetted factories. They conduct physical factory audits, monitor production schedules, negotiate pricing, and maintain the relationships that give us leverage when something goes wrong.','Factory auditors & production coordinators'],
        ['truck','Logistics & Documentation','Sialkot','Export documentation specialists and freight coordinators. They prepare commercial invoices, packing lists, certificates of origin, HS code classifications, fumigation certificates, and book freight — sea, air, or express courier.','Documentation & freight team'],
        ['briefcase','Client Relations — Americas','Dallas','Your point of contact if you\'re in the US, Canada, or Latin America. They handle onboarding, contracts, invoicing in USD, and day-to-day communication. Same time zone, same language, same business culture.','Account managers'],
        ['globe','Client Relations — Europe & Middle East','Paris','Your point of contact if you\'re in the EU, UK, or Middle East. Contracts in EUR or GBP, EU compliance guidance, and support during European business hours.','Account managers'],
      ];
      foreach ($departments as $i => $d): ?>
      <div class="reveal delay-<?= ($i%3)+1 ?>" style="display:grid;grid-template-columns:auto 1fr;gap:1.5rem;padding:2rem;border:1px solid var(--cream-dark);border-radius:var(--radius);background:var(--white);">
        <div style="text-align:center;">
          <div style="margin-bottom:0.4rem;color:var(--gold);"><?= icon($d[0], 30) ?></div>
          <div style="font-size:0.65rem;color:var(--gold);font-weight:700;letter-spacing:1px;text-transform:uppercase;display:flex;align-items:center;gap:0.25rem;justify-content:center;"><?= icon('map-pin', 11) ?> <?= $d[2] ?></div>
        </div>
        <div>
          <h3 style="font-size:1.1rem;margin-bottom:0.4rem;"><?= $d[1] ?></h3>
          <p style="font-size:0.88rem;line-height:1.7;margin-bottom:0.5rem;"><?= $d[3] ?></p>
          <div style="font-size:0.75rem;color:var(--navy);font-weight:600;"><?= $d[4] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Leadership — from database if available -->
<?php if (!empty($team)): ?>
<section class="section section-cream">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto 2.5rem;" class="reveal">
      <div class="section-label">Leadership</div>
      <h2 class="section-title">The People Leading Our Operations</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:1.5rem;max-width:900px;margin:0 auto;">
      <?php foreach ($team as $i => $m): ?>
      <div class="reveal delay-<?= ($i%3)+1 ?>" style="text-align:center;padding:2rem 1.5rem;border:1px solid var(--cream-dark);border-radius:var(--radius);background:var(--white);">
        <?php if (!empty($m['image'])): ?>
        <img src="<?= e($m['image']) ?>" alt="<?= e($m['name']) ?>" style="width:100px;height:100px;border-radius:50%;object-fit:cover;margin-bottom:1rem;border:3px solid var(--gold);" loading="lazy">
        <?php else: ?>
        <div style="width:100px;height:100px;border-radius:50%;background:var(--navy);margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--gold);font-family:'Playfair Display',serif;font-weight:700;border:3px solid var(--gold);">
          <?= strtoupper(substr($m['name'] ?? 'S', 0, 1)) ?>
        </div>
        <?php endif; ?>
        <h3 style="font-size:1.05rem;margin-bottom:0.15rem;"><?= e($m['name']) ?></h3>
        <div style="font-size:0.75rem;color:var(--gold);font-weight:600;letter-spacing:1px;text-transform:uppercase;margin-bottom:0.6rem;"><?= e($m['role'] ?? $m['position'] ?? '') ?></div>
        <p style="font-size:0.85rem;line-height:1.6;"><?= e($m['bio'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php else: ?>
<!-- Leadership — static fallback when database is empty -->
<section class="section section-cream">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto 2.5rem;" class="reveal">
      <div class="section-label">Leadership</div>
      <h2 class="section-title">Experienced Operators. Not Middlemen.</h2>
      <p>Our leadership team combines deep Sialkot manufacturing knowledge with international trade experience. Every senior team member has hands-on factory experience — they know the supply chain because they've worked inside it.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:1.5rem;max-width:900px;margin:0 auto;">
      <?php
      $leaders = [
        ['O','Founder & CEO','Oversees global operations across all three offices. Background in international trade and manufacturing. Personally audits key manufacturers and maintains senior relationships with factory owners.','Sialkot & Dallas'],
        ['Q','Head of Quality Control','Leads the QC inspection facility and lab testing operations. 10+ years in quality assurance for export-grade products. Certifications in ISO 9001 and AQL methodology.','Sialkot'],
        ['L','Head of Logistics','Manages all freight, customs documentation, and shipment tracking. Coordinates with freight forwarders, customs brokers, and insurance providers across multiple trade lanes.','Sialkot'],
      ];
      foreach ($leaders as $i => $l): ?>
      <div class="reveal delay-<?= $i+1 ?>" style="text-align:center;padding:2rem 1.5rem;border:1px solid var(--cream-dark);border-radius:var(--radius);background:var(--white);">
        <div style="width:100px;height:100px;border-radius:50%;background:var(--navy);margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--gold);font-family:'Playfair Display',serif;font-weight:700;border:3px solid var(--gold);"><?= $l[0] ?></div>
        <h3 style="font-size:1.05rem;margin-bottom:0.15rem;"><?= $l[1] ?></h3>
        <div style="font-size:0.7rem;color:var(--gold);font-weight:600;letter-spacing:1px;text-transform:uppercase;margin-bottom:0.6rem;display:flex;align-items:center;gap:0.25rem;justify-content:center;"><?= icon('map-pin', 11) ?> <?= $l[3] ?></div>
        <p style="font-size:0.85rem;line-height:1.6;"><?= $l[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Why This Matters to You -->
<section class="section section-dark">
  <div class="container" style="max-width:800px;text-align:center;">
    <div class="reveal">
      <div class="section-label">Why This Matters</div>
      <h2 class="section-title" style="color:var(--white);">When You Work With SialSourcing,<br>You're Not Working With One Person.</h2>
      <p style="color:rgba(255,255,255,0.6);font-size:1.05rem;line-height:1.9;margin-bottom:2rem;">Many sourcing agents in Pakistan are solo operators — one person finding factories, checking quality, managing logistics, and handling your money. When that person is busy, sick, or unavailable, your order stops.</p>
      <p style="color:rgba(255,255,255,0.6);font-size:1.05rem;line-height:1.9;margin-bottom:2rem;">SialSourcing is a structured company with dedicated departments. Your quality inspector is not the same person negotiating with the factory. Your logistics coordinator is not the same person running lab tests. Each function is handled by a specialist — so nothing falls through the cracks.</p>
      <p style="color:var(--gold);font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:600;">That's the difference between a sourcing agent and a supply chain company.</p>
    </div>
  </div>
</section>

<!-- CTA -->
<div class="cta-section">
  <h2 class="reveal">Ready to Work With a Real Team?</h2>
  <p class="reveal delay-1">Tell us what you need. You'll be assigned a dedicated account manager within 24 hours.</p>
  <div class="reveal delay-2" style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
    <a href="<?= SITE_URL ?>/contact" class="btn btn-gold">Get Started</a>
    <a href="<?= SITE_URL ?>/about" class="btn btn-outline">About SialSourcing</a>
  </div>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
