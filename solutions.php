<?php
$pageTitle = 'Sourcing Solutions';
$pageDesc  = 'SialSourcing provides end-to-end sourcing solutions — manufacturer vetting, OEM branding, export documentation, QC inspection, and freight logistics from Sialkot, Pakistan.';
$canonicalPath = '/solutions';
require_once __DIR__ . '/includes/header.php';
$solutions = db()->query("SELECT * FROM solutions WHERE is_active=1 ORDER BY sort_order")->fetchAll();
?>

<div style="padding-top:80px;">

<!-- Hero -->
<section style="background:var(--navy);padding:5rem 0 4rem;text-align:center;position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 50% 50%,rgba(201,168,76,0.1) 0%,transparent 70%);"></div>
  <div class="container" style="position:relative;">
    <div class="section-label reveal">What We Offer</div>
    <h1 style="color:var(--white);font-size:clamp(2rem,5vw,3.5rem);margin-bottom:1rem;" class="reveal delay-1">End-to-End Sourcing Solutions</h1>
    <p style="color:rgba(255,255,255,0.6);max-width:580px;margin:0 auto;" class="reveal delay-2">From the first factory audit to the final delivery at your warehouse — SialSourcing manages every step, so you focus on growing your business.</p>
  </div>
</section>

<!-- Process Timeline -->
<section class="section section-cream">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:3.5rem;">
      <div class="section-label">How It Works</div>
      <h2 class="section-title">Your Sourcing Journey with Us</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:0;position:relative;">
      <?php
      $steps = [
        ['01','Inquiry','You tell us what you need — product specs, quantity, target price, and certifications.','message-circle'],
        ['02','Matching','We shortlist 2–3 vetted manufacturers from our network and share their profiles.','search'],
        ['03','Sampling','Factory produces samples. We inspect and ship to you for approval.','package'],
        ['04','Production','Once you approve, production begins under our supervision.','factory'],
        ['05','QC Inspection','Pre-shipment inspection against AQL standards. Full report shared with you.','tool'],
        ['06','Shipment','We coordinate freight, prepare export docs, and track delivery to your port.','truck'],
      ];
      foreach ($steps as $i => $step): ?>
      <div class="reveal delay-<?= ($i%3)+1 ?>" style="background:var(--white);border:1px solid var(--cream-dark);padding:2rem 1.5rem;text-align:center;position:relative;<?= $i>0?'border-left:none;':'' ?>">
        <div style="margin-bottom:0.75rem;color:var(--gold);display:flex;justify-content:center;"><?= icon($step[3], 26) ?></div>
        <div style="font-size:0.7rem;font-weight:700;letter-spacing:2px;color:var(--gold);margin-bottom:0.5rem;"><?= $step[0] ?></div>
        <h3 style="font-size:1.05rem;margin-bottom:0.5rem;"><?= $step[1] ?></h3>
        <p style="font-size:0.84rem;"><?= $step[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Solutions Grid -->
<section class="section section-dark">
  <div class="container">
    <div class="reveal" style="text-align:center;margin-bottom:3rem;">
      <div class="section-label">Our Services</div>
      <h2 class="section-title">Everything Under One Roof</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;">
      <?php
      $fallback = [
        ['Manufacturer Vetting','shield','We physically audit every factory before onboarding them. Certificates, premises, workforce conditions, machinery — all verified. You only see manufacturers we trust.'],
        ['Order Management','package','From purchase order to final delivery, our team tracks every milestone. You get regular updates, photos from the production floor, and no surprises.'],
        ['Custom Branding & OEM','tag','Your logo on the product, your design on the packaging, your label on the box. We coordinate complete OEM arrangements directly with factories and send approval samples before bulk production.'],
        ['Export Documentation','file-text','Certificate of Origin, Packing Lists, Commercial Invoices, Phytosanitary Certificates, Fumigation Reports — we prepare and verify every document your customs will ask for.'],
        ['Logistics & Freight','truck','Sea freight (FCL/LCL), air cargo, express courier — we book the right mode for your timeline and budget, and share live tracking until it reaches your port.'],
        ['After-Sales Support','headphones','Defective units after delivery? We engage the manufacturer immediately, arrange re-inspection or replacement, and ensure full accountability — every time.'],
      ];
      $display = !empty($solutions) ? $solutions : $fallback;
      foreach ($display as $i => $sol): ?>
      <div class="reveal delay-<?= ($i%3)+1 ?>" style="background:rgba(255,255,255,0.04);border:1px solid rgba(201,168,76,0.12);border-radius:var(--radius);padding:2rem;transition:all 0.3s;">
        <div style="margin-bottom:1rem;color:var(--gold);"><?= icon(is_array($sol)?($sol['icon']??$sol[1]):$sol[1], 26) ?></div>
        <h3 style="color:var(--white);font-size:1.15rem;margin-bottom:0.75rem;"><?= e(is_array($sol)?($sol['title']??$sol[0]):$sol[0]) ?></h3>
        <p style="color:rgba(255,255,255,0.55);font-size:0.9rem;line-height:1.8;"><?= e(is_array($sol)?($sol['short_desc']??$sol['description']??$sol[2]):$sol[2]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Why SialSourcing -->
<section class="section section-white">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;">
      <div class="reveal-left">
        <div class="section-label">Why Us</div>
        <h2 class="section-title">Sialkot Knowledge. Global Standards.</h2>
        <p style="margin-bottom:1.5rem;">Sialkot is one of the world's most specialized manufacturing cities — 70% of global footballs, world-leading surgical instruments, premium leather, and precision cutlery all come from here. But navigating this ecosystem as a foreign buyer is complex.</p>
        <p style="margin-bottom:2rem;">SialSourcing was built specifically to bridge that gap. We are based here, we speak the language, we know the factories personally, and we hold manufacturers to international standards — because our reputation depends on it.</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <?php foreach ([['map-pin','Local Expertise','Deep roots in Sialkot manufacturing ecosystem'],['search','Transparent Audits','Full factory reports shared — no hidden suppliers'],['zap','Fast Turnaround','Sample delivery in 2–3 weeks, production in 4–8 weeks'],['dollar-sign','Honest Pricing','No inflated margins. We charge a fixed sourcing fee.']] as $w): ?>
          <div style="background:var(--cream);border-radius:10px;padding:1.25rem;">
            <div style="margin-bottom:0.5rem;color:var(--gold);"><?= icon($w[0], 22) ?></div>
            <div style="font-weight:700;font-size:0.9rem;color:var(--text);margin-bottom:0.3rem;"><?= $w[1] ?></div>
            <div style="font-size:0.8rem;color:var(--muted);"><?= $w[2] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="reveal-right">
        <div style="background:var(--navy);border-radius:var(--radius);padding:2.5rem;color:white;">
          <h3 style="color:var(--gold);margin-bottom:1.5rem;font-size:1.3rem;">Our Sourcing Fee Structure</h3>
          <?php foreach ([['Order value under $10,000','8% sourcing fee'],['$10,001 – $50,000','6% sourcing fee'],['$50,001 – $200,000','4% sourcing fee'],['Above $200,000','Negotiated rate']] as $fee): ?>
          <div style="display:flex;justify-content:space-between;padding:0.9rem 0;border-bottom:1px solid rgba(255,255,255,0.08);font-size:0.9rem;">
            <span style="color:rgba(255,255,255,0.7);"><?= $fee[0] ?></span>
            <span style="color:var(--gold);font-weight:700;"><?= $fee[1] ?></span>
          </div>
          <?php endforeach; ?>
          <p style="color:rgba(255,255,255,0.45);font-size:0.78rem;margin-top:1rem;">Fee covers: manufacturer vetting, sample coordination, QC inspection, and export documentation. Freight costs are separate and billed at cost.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<div class="cta-section">
  <h2 class="reveal">Ready to Place Your First Order?</h2>
  <p class="reveal delay-1">Share your product requirements and we'll send you a sourcing plan within 24 hours — at no cost.</p>
  <a href="/contact" class="btn btn-gold reveal delay-2">Get a Free Sourcing Consultation</a>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
