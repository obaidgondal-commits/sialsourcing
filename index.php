<?php
$pageTitle     = 'Pakistan Sourcing | Sialkot Manufacturers';
$pageDesc      = 'Pakistan sourcing agent with offices in Sialkot, Dallas & Paris. We vet factories, inspect quality, protect payments & deliver to your door. Free sourcing plan.';
$canonicalPath = '/';
require_once __DIR__ . '/includes/header.php';

$products  = db()->query("SELECT * FROM products WHERE is_active=1 AND sort_order < 100 ORDER BY sort_order LIMIT 12")->fetchAll();
$solutions = db()->query("SELECT * FROM solutions WHERE is_active=1 ORDER BY sort_order LIMIT 6")->fetchAll();
$blogs     = db()->query("SELECT * FROM blog_posts WHERE is_published=1 ORDER BY published_at DESC LIMIT 3")->fetchAll();
$faqs      = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order LIMIT 5")->fetchAll();
?>

<!-- ===== HERO — Ship Animation ===== -->
<section class="sh">
  <div class="sh-stars"></div>
  <div class="sh-moon"></div>

  <div class="sh-content">
    <div class="sh-badge">
      <span class="sh-dot"></span>
      Dallas · Paris · Sialkot
    </div>
    <h1>
      Don't Source Blind.<br>
      Source <em>Protected.</em>
    </h1>
    <p class="sh-sub">
      You've found the manufacturers. But who checks their quality? Who protects your payment? Who handles customs, lab testing, and freight — so your products actually arrive?<br><strong style="color:rgba(255,255,255,0.85);">We do. End to end.</strong>
    </p>
    <div class="sh-btns">
      <a href="<?= SITE_URL ?>/contact" class="btn btn-gold">Get a Free Sourcing Plan</a>
      <a href="#how-it-works" class="btn btn-outline">See How It Works</a>
    </div>
  </div>

<div class="sh-stats">
    <div class="sh-stat">
      <span class="sh-stat-num" data-count="100+">100+</span>
      <span class="sh-stat-lbl">Vetted Manufacturers</span>
    </div>
    <div class="sh-stat">
      <span class="sh-stat-num" data-count="30+">30+</span>
      <span class="sh-stat-lbl">Countries Served</span>
    </div>
    <div class="sh-stat">
      <span class="sh-stat-num" data-count="3">3</span>
      <span class="sh-stat-lbl">Global Offices</span>
    </div>
    <div class="sh-stat">
      <span class="sh-stat-num" data-count="100%">100%</span>
      <span class="sh-stat-lbl">Quality Inspected</span>
    </div>
  </div>

  <!-- Shipping route animation -->
  <div class="sh-scene">
    <div class="sh-ocean">
      <div class="sh-wave sh-wave-1"></div>
      <div class="sh-wave sh-wave-2"></div>
      <div class="sh-wave sh-wave-3"></div>
      <div class="sh-wl" style="top:15%;left:10%;width:35%;"></div>
      <div class="sh-wl" style="top:45%;left:30%;width:45%;animation-delay:1.5s;"></div>
      <div class="sh-wl" style="top:75%;left:5%;width:55%;animation-delay:3s;"></div>
    </div>
    <div class="sh-route"></div>
    <div class="sh-port" style="left:8%;">
      <div class="sh-port-dot"></div>
      <span class="sh-port-name">Sialkot</span>
    </div>
    <div class="sh-port" style="left:35%;">
      <div class="sh-port-dot" style="animation-delay:0.5s;"></div>
      <span class="sh-port-name">Dubai</span>
    </div>
    <div class="sh-port" style="left:62%;">
      <div class="sh-port-dot" style="animation-delay:1s;"></div>
      <span class="sh-port-name">Paris</span>
    </div>
    <div class="sh-port" style="left:88%;">
      <div class="sh-port-dot" style="animation-delay:1.5s;"></div>
      <span class="sh-port-name">Dallas</span>
    </div>
    <div class="sh-ship">
      <div class="sh-ship-bob">
        <svg width="320" height="130" viewBox="0 0 320 130" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="hullG" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#1e4080"/>
      <stop offset="100%" stop-color="#0d1f40"/>
    </linearGradient>
  </defs>
  <ellipse cx="160" cy="124" rx="140" ry="5" fill="rgba(201,168,76,0.06)"/>
  <path d="M 24,92 L 48,80 L 268,80 L 280,90 L 274,108 L 34,108 Z" fill="url(#hullG)"/>
  <path d="M 34,108 L 274,108 L 266,118 L 42,118 Z" fill="#061020"/>
  <line x1="27" y1="95" x2="278" y2="95" stroke="#c9a84c" stroke-width="1.5" opacity="0.55"/>
  <rect x="47" y="77" width="223" height="5" fill="#162d56"/>
  <line x1="47" y1="78" x2="270" y2="78" stroke="rgba(201,168,76,0.35)" stroke-width="0.75"/>
  <path d="M 24,92 L 16,96 M 24,92 L 16,88" stroke="rgba(201,168,76,0.4)" stroke-width="1" fill="none"/>
  <circle cx="36" cy="95" r="2" fill="none" stroke="rgba(201,168,76,0.28)" stroke-width="0.75"/>
  <rect x="52"  y="52" width="34" height="26" rx="1.5" fill="#b83232"/>
  <rect x="54"  y="54" width="30" height="9"  fill="rgba(0,0,0,0.15)"/>
  <line x1="69" y1="52" x2="69" y2="78" stroke="rgba(255,255,255,0.07)" stroke-width="0.75"/>
  <rect x="90"  y="52" width="34" height="26" rx="1.5" fill="#c9a84c"/>
  <rect x="92"  y="54" width="30" height="9"  fill="rgba(0,0,0,0.12)"/>
  <line x1="107" y1="52" x2="107" y2="78" stroke="rgba(255,255,255,0.07)" stroke-width="0.75"/>
  <rect x="128" y="52" width="34" height="26" rx="1.5" fill="#2a5a8c"/>
  <rect x="130" y="54" width="30" height="9"  fill="rgba(0,0,0,0.12)"/>
  <line x1="145" y1="52" x2="145" y2="78" stroke="rgba(255,255,255,0.07)" stroke-width="0.75"/>
  <rect x="166" y="52" width="34" height="26" rx="1.5" fill="#1a6644"/>
  <rect x="168" y="54" width="30" height="9"  fill="rgba(0,0,0,0.12)"/>
  <line x1="183" y1="52" x2="183" y2="78" stroke="rgba(255,255,255,0.07)" stroke-width="0.75"/>
  <rect x="204" y="52" width="34" height="26" rx="1.5" fill="#7a3a12"/>
  <rect x="206" y="54" width="30" height="9"  fill="rgba(0,0,0,0.12)"/>
  <rect x="90"  y="30" width="34" height="22" rx="1.5" fill="#1a6644" opacity="0.9"/>
  <rect x="92"  y="32" width="30" height="8"  fill="rgba(0,0,0,0.12)"/>
  <rect x="128" y="30" width="34" height="22" rx="1.5" fill="#b83232"  opacity="0.9"/>
  <rect x="130" y="32" width="30" height="8"  fill="rgba(0,0,0,0.12)"/>
  <rect x="166" y="30" width="34" height="22" rx="1.5" fill="#2a5a8c"  opacity="0.9"/>
  <rect x="168" y="32" width="30" height="8"  fill="rgba(0,0,0,0.12)"/>
  <rect x="128" y="12" width="34" height="18" rx="1.5" fill="#c9a84c"  opacity="0.82"/>
  <rect x="130" y="14" width="30" height="7"  fill="rgba(0,0,0,0.1)"/>
  <rect x="216" y="38" width="48" height="40" rx="2" fill="#102040" stroke="#1a3060" stroke-width="0.75"/>
  <rect x="220" y="42" width="40" height="10" rx="1" fill="rgba(201,168,76,0.32)"/>
  <line x1="229" y1="42" x2="229" y2="52" stroke="rgba(8,18,42,0.5)" stroke-width="0.5"/>
  <line x1="238" y1="42" x2="238" y2="52" stroke="rgba(8,18,42,0.5)" stroke-width="0.5"/>
  <line x1="247" y1="42" x2="247" y2="52" stroke="rgba(8,18,42,0.5)" stroke-width="0.5"/>
  <line x1="256" y1="42" x2="256" y2="52" stroke="rgba(8,18,42,0.5)" stroke-width="0.5"/>
  <rect x="220" y="30" width="40" height="10" rx="1.5" fill="#0d1e3a"/>
  <rect x="223" y="32" width="34" height="6"  rx="0.75" fill="rgba(201,168,76,0.12)"/>
  <rect x="225" y="21" width="30" height="10" rx="1.5" fill="#0a1830"/>
  <rect x="232" y="10" width="12" height="13" rx="2" fill="#1a3565"/>
  <rect x="233" y="11" width="10" height="5"  rx="1" fill="#c9a84c" opacity="0.7"/>
  <line x1="238" y1="2"  x2="238" y2="21" stroke="rgba(201,168,76,0.65)" stroke-width="1.5"/>
  <line x1="230" y1="7"  x2="246" y2="7"  stroke="rgba(201,168,76,0.5)" stroke-width="1"/>
  <circle cx="238" cy="2"  r="2.5" fill="#c9a84c" opacity="0.9"/>
  <circle cx="238" cy="2"  r="5"   fill="rgba(201,168,76,0.18)"/>
  <line x1="208" y1="52" x2="208" y2="78" stroke="rgba(201,168,76,0.22)" stroke-width="1.5"/>
  <line x1="202" y1="52" x2="214" y2="52" stroke="rgba(201,168,76,0.18)" stroke-width="1"/>
  <circle cx="62"  cy="96" r="2" fill="none" stroke="rgba(201,168,76,0.22)" stroke-width="0.75"/>
  <circle cx="82"  cy="96" r="2" fill="none" stroke="rgba(201,168,76,0.22)" stroke-width="0.75"/>
  <circle cx="102" cy="96" r="2" fill="none" stroke="rgba(201,168,76,0.22)" stroke-width="0.75"/>
  <circle cx="122" cy="96" r="2" fill="none" stroke="rgba(201,168,76,0.22)" stroke-width="0.75"/>
  <circle cx="142" cy="96" r="2" fill="none" stroke="rgba(201,168,76,0.22)" stroke-width="0.75"/>
  <circle cx="162" cy="96" r="2" fill="none" stroke="rgba(201,168,76,0.22)" stroke-width="0.75"/>
  <circle cx="182" cy="96" r="2" fill="none" stroke="rgba(201,168,76,0.22)" stroke-width="0.75"/>
</svg>
        <div class="sh-wake"></div>
      </div>
    </div>
  </div>
</section>

<!-- ===== THE PROBLEM — Why Not Go Direct ===== -->
<section class="section section-white" id="how-it-works">
  <div class="container">
    <div style="text-align:center;max-width:800px;margin:0 auto 3rem;" class="reveal">
      <div class="section-label">The Problem</div>
      <h2 class="section-title">Finding a Manufacturer Is Easy.<br>Getting Your Order Right Isn't.</h2>
      <p style="font-size:1.05rem;line-height:1.9;">Sialkot has thousands of manufacturers. A quick search gives you fifty options. But international buyers face the same problems every time:</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:1.25rem;max-width:900px;margin:0 auto;" class="problems-grid">
      <?php
      $problems = [
        ['search','Quality Mismatch','The sample looked perfect. The bulk order didn\'t. Without on-ground inspection, you won\'t know until it\'s too late.'],
        ['dollar-sign','Payment Risk','You wired $30,000 to a factory you found online. What happens if they disappear? Who holds them accountable?'],
        ['file-text','Compliance Gaps','Your product needs CE marking, FDA clearance, or REACH compliance. Does the factory even know what that means?'],
        ['truck','Logistics Chaos','Production finished three weeks ago — but your shipment is stuck because of missing fumigation certificates and wrong HS codes.'],
        ['message-circle','Communication Breakdown','Language barriers, time zone gaps, and unanswered WhatsApp messages. You\'re running a business, not chasing updates.'],
        ['shield','No Recourse','If something goes wrong with a direct factory order, who do you call? You have no leverage, no local presence, no legal standing.'],
      ];
      foreach ($problems as $i => $p): ?>
      <div class="reveal delay-<?= ($i%3)+1 ?>" style="display:flex;gap:1.25rem;align-items:flex-start;padding:1.5rem;border:1px solid var(--cream-dark);border-radius:var(--radius);background:var(--cream);">
        <div style="flex-shrink:0;line-height:1;color:var(--gold);"><?= icon($p[0], 28) ?></div>
        <div>
          <h3 style="font-size:1rem;margin-bottom:0.35rem;"><?= $p[1] ?></h3>
          <p style="font-size:0.88rem;line-height:1.7;"><?= $p[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2.5rem;" class="reveal">
      <p style="font-size:1.15rem;font-weight:600;color:var(--text);font-family:'Playfair Display',serif;">This is why serious buyers don't go direct.<br>They go through a supply chain partner.</p>
    </div>
  </div>
</section>

<!-- ===== THE SOLUTION — What SialSourcing Does ===== -->
<section class="section section-dark">
  <div class="container">
    <div style="text-align:center;max-width:720px;margin:0 auto 2.5rem;" class="reveal">
      <div class="section-label">The Solution</div>
      <h2 class="section-title" style="color:var(--white);">One Partner. Zero Hassle.<br>Products at Your Doorstep.</h2>
      <p class="section-desc" style="margin:0 auto;color:rgba(255,255,255,0.6);font-size:1rem;">SialSourcing sits between you and the manufacturer — protecting your money, inspecting your quality, and managing your entire supply chain from factory floor to your warehouse door.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:1rem;max-width:1000px;margin:0 auto;" class="solutions-grid-home">
      <?php
      $steps = [
        ['message-circle','You Tell Us What You Need','Product specs, quantities, target price, certifications — we take your brief and get to work.'],
        ['factory','We Find the Right Manufacturer','Not any factory. The RIGHT one — vetted, audited, certified, and matched to your product.'],
        ['check-circle','We Inspect & Lab Test Everything','Our own QC facility in Sialkot plus third-party labs for ISO, CE, FDA, and REACH compliance. Nothing ships without our sign-off.'],
        ['dollar-sign','You Pay Safely','Wire to our Dallas or Paris office — in USD, EUR, or GBP. Your money is protected. We pay the factory only after quality is confirmed.'],
        ['truck','We Handle All Logistics','Export documentation, customs clearance, freight booking, insurance, fumigation, HS codes — we manage every detail.'],
        ['box','Products Arrive at Your Door','Full tracking from Sialkot to your warehouse. Sea freight, air cargo, or express courier — your choice, our coordination.'],
      ];
      foreach ($steps as $i => $s): ?>
      <div class="reveal delay-<?= ($i%3)+1 ?>" style="display:flex;gap:1.25rem;align-items:flex-start;padding:1.5rem;background:rgba(255,255,255,0.04);border:1px solid rgba(201,168,76,0.12);border-radius:var(--radius);">
        <div style="flex-shrink:0;width:48px;height:48px;background:rgba(201,168,76,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);"><?= icon($s[0], 22) ?></div>
        <div>
          <div style="font-size:0.65rem;color:var(--gold);font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:0.2rem;">Step <?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
          <h3 style="color:var(--white);font-size:1rem;margin-bottom:0.35rem;"><?= $s[1] ?></h3>
          <p style="color:rgba(255,255,255,0.5);font-size:0.88rem;line-height:1.7;"><?= $s[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2.5rem;" class="reveal">
      <a href="<?= SITE_URL ?>/solutions" class="btn btn-outline">See Our Full Process</a>
    </div>
  </div>
</section>

<!-- ===== TRUST SIGNALS — Global Offices ===== -->
<section class="section section-white">
  <div class="container">
    <div style="text-align:center;max-width:720px;margin:0 auto 2.5rem;" class="reveal">
      <div class="section-label">Global Presence</div>
      <h2 class="section-title">Pay Locally. Source Globally.</h2>
      <p>Unlike factories that require risky international wire transfers to Pakistani bank accounts, SialSourcing has offices where you do business. Pay in your currency, to a local account, with full legal protection.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:1.5rem;" class="offices-grid">
      <?php
      $offices = [
        ['🇺🇸','Dallas, USA','USD payments accepted','Our Americas headquarters. Pay to a US bank account in USD. Full invoicing, contracts, and dispute resolution under US commercial law.'],
        ['🇫🇷','Paris, France','EUR & GBP payments accepted','Our European office serving the EU and UK markets. Pay in Euros or Pounds to a French bank account. EU trade compliance handled locally.'],
        ['🇵🇰','Sialkot, Pakistan','Operations & QC Hub','Our factory floor presence. QC inspection facility, in-house lab, warehouse, and direct manufacturer relationships. This is where the work happens.'],
      ];
      foreach ($offices as $i => $o): ?>
      <div class="reveal delay-<?= $i+1 ?>" style="display:flex;gap:1.5rem;align-items:flex-start;padding:2rem;border:1px solid var(--cream-dark);border-radius:var(--radius);background:var(--white);">
        <div style="font-size:2.5rem;flex-shrink:0;line-height:1;"><?= $o[0] ?></div>
        <div>
          <h3 style="font-size:1.15rem;margin-bottom:0.15rem;"><?= $o[1] ?></h3>
          <div style="font-size:0.75rem;color:var(--gold);font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:0.6rem;"><?= $o[2] ?></div>
          <p style="font-size:0.9rem;line-height:1.7;"><?= $o[3] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== FOR US BUYERS ===== -->
<section class="section us-strip">
  <div class="container">
    <div style="text-align:center;max-width:720px;margin:0 auto 2.5rem;" class="reveal">
      <div class="section-label">🇺🇸 Buying from the USA?</div>
      <h2 class="section-title">Built for American Importers</h2>
      <p>Our Dallas headquarters exists for one reason: to make sourcing from Pakistan feel like buying domestic.</p>
    </div>
    <div class="us-strip-grid">
      <div class="us-tile reveal delay-1">
        <div class="us-tile-icon"><?= icon('dollar-sign', 20) ?></div>
        <h3>Pay in USD, Onshore</h3>
        <p>Wire to a US bank account in Dallas — no risky international transfers to accounts you can't verify.</p>
      </div>
      <div class="us-tile reveal delay-2">
        <div class="us-tile-icon"><?= icon('shield-check', 20) ?></div>
        <h3>US-Law Contracts</h3>
        <p>Invoicing, contracts, and dispute resolution under US commercial law — real recourse, in your jurisdiction.</p>
      </div>
      <div class="us-tile reveal delay-3">
        <div class="us-tile-icon"><?= icon('truck', 20) ?></div>
        <h3>Duty &amp; Customs Guidance</h3>
        <p>HTS classification, customs documents, and freight to any US port — handled and double-checked before goods ship.</p>
      </div>
      <div class="us-tile reveal delay-4">
        <div class="us-tile-icon"><?= icon('clock', 20) ?></div>
        <h3>Support on CST Hours</h3>
        <p>Our US team answers during your business day — no waiting overnight for answers from the other side of the world.</p>
      </div>
    </div>
    <!-- TESTIMONIALS: add a testimonial row here when real client quotes are available — do not publish placeholder quotes -->
  </div>
</section>

<!-- ===== QUALITY — Lab & QC ===== -->
<section class="section section-dark">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr;gap:2rem;align-items:center;" class="qc-grid">
      <div class="reveal">
        <div class="section-label">Quality You Can Verify</div>
        <h2 class="section-title" style="color:var(--white);">Our Own Lab. Our Own Inspectors.<br>Your Peace of Mind.</h2>
        <p style="color:rgba(255,255,255,0.6);margin-bottom:1.5rem;font-size:1rem;line-height:1.8;">Most sourcing agents outsource their quality checks. We don't. SialSourcing operates its own inspection facility and testing lab in Sialkot — staffed by our own team, on our own clock.</p>
        <p style="color:rgba(255,255,255,0.6);margin-bottom:2rem;font-size:0.95rem;line-height:1.8;">For specialised compliance testing — CE marking, FDA clearance, REACH chemical analysis, ISO 13485 medical device standards — we partner with internationally accredited third-party laboratories. Every test result is documented and shared with you before shipment.</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <?php
          $qcPoints = [
            ['factory','In-House QC Facility','Our own inspectors, our own standards'],
            ['tool','Own Testing Lab','Tensile, durability, and functional testing'],
            ['file-text','AQL 2.5 Inspection','Statistical sampling on every order'],
            ['globe','Third-Party Certification','ISO, CE, FDA, REACH through accredited labs'],
          ];
          foreach ($qcPoints as $q): ?>
          <div style="border-left:2px solid rgba(201,168,76,0.4);padding-left:1rem;">
            <div style="margin-bottom:0.3rem;color:var(--gold);"><?= icon($q[0], 20) ?></div>
            <div style="color:var(--white);font-size:0.88rem;font-weight:600;"><?= $q[1] ?></div>
            <div style="color:rgba(255,255,255,0.4);font-size:0.78rem;"><?= $q[2] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="margin-top:2rem;">
          <a href="<?= SITE_URL ?>/lab-qc" class="btn btn-gold">See Our QC Process</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== PRODUCTS ===== -->
<section class="section section-cream" id="products">
  <div class="container">
    <div class="reveal" style="text-align:center;max-width:700px;margin:0 auto 2.5rem;">
      <div class="section-label">What We Source</div>
      <h2 class="section-title">Seven Product Categories.<br>Hundreds of Manufacturers. One Partner.</h2>
      <p>Custom soccer balls, sublimation teamwear, military &amp; civil uniforms, CE-marked surgical instruments, REACH-compliant leather, FDA-compliant cutlery — we match you with the right manufacturer and handle everything else.</p>
    </div>
    <div class="products-grid">
      <?php
      if (!empty($products)):
        foreach ($products as $i => $p): ?>
      <div class="product-card reveal delay-<?= ($i%3)+1 ?>">
        <div class="product-icon" style="color:var(--gold);"><?= icon($p['icon'] ?: 'box', 22) ?></div>
        <h3><?= e($p['title']) ?></h3>
        <p><?= e($p['description']) ?></p>
        <a href="<?= SITE_URL ?>/<?= e($p['slug']) ?>" class="product-link">Explore <?= e($p['title']) ?> <?= icon('arrow-right', 16) ?></a>
      </div>
      <?php endforeach;
      else:
        $fallback = [
          ['Custom Soccer Balls','soccer-ball','Match, training, and promotional soccer balls from the city that hand-stitches 70% of the world\'s supply — full custom printing, FIFA Quality Pro capability.','custom-soccer-balls'],
          ['Activewear & Sports Uniforms','shirt','Custom sublimation jerseys, yoga pants, compression wear, gym apparel, and team uniforms — OEM branded and bulk produced for global markets.','activewear-sports-uniforms'],
          ['Uniforms & Tactical Wear','shield','Military & civil uniforms, police and security apparel, duty gear, scrubs, and workwear — built to spec for private-sector buyers.','uniforms-tactical-wear'],
          ['Surgical Instruments','scissors','Precision-crafted surgical and dental instruments from the world\'s largest hub — ISO 13485 certified, CE marked, FDA registered.','surgical-instruments'],
          ['Sports Goods & Equipment','target','FIFA-approved footballs, hockey sticks, boxing gloves, cricket gear — from manufacturers who supply the world\'s biggest brands.','sports-goods'],
          ['Leather Products','briefcase','Full-grain leather gloves, jackets, wallets, motorcycle gear, and accessories — REACH compliant, tanned to European standards.','leather-goods'],
          ['Cutlery & Kitchenware','cutlery','18/10 stainless steel cutlery, chef knives, kitchen tools — FDA compliant, dishwasher safe, available with custom branding.','cutlery'],
        ];
        foreach ($fallback as $i => $f): ?>
      <div class="product-card reveal delay-<?= ($i%3)+1 ?>">
        <div class="product-icon" style="color:var(--gold);"><?= icon($f[1], 22) ?></div>
        <h3><?= $f[0] ?></h3>
        <p><?= $f[2] ?></p>
        <a href="<?= SITE_URL ?>/<?= $f[3] ?>" class="product-link">Explore <?= $f[0] ?> <?= icon('arrow-right', 16) ?></a>
      </div>
      <?php endforeach; endif; ?>
      <div class="product-card product-card--cta reveal">
        <h3>Something else in mind?</h3>
        <p style="margin-bottom:0.5rem;">If Pakistan makes it, we can source, inspect, and deliver it.</p>
        <a href="<?= SITE_URL ?>/contact" class="product-link">Ask Our Team <?= icon('arrow-right', 16) ?></a>
      </div>
    </div>
    <div style="text-align:center;margin-top:2.5rem;" class="reveal">
      <a href="<?= SITE_URL ?>/products" class="btn btn-gold">View All Products</a>
    </div>
  </div>
</section>

<!-- ===== WHY NOT GO DIRECT — Comparison ===== -->
<section class="section section-white">
  <div class="container">
    <div style="text-align:center;max-width:720px;margin:0 auto 2.5rem;" class="reveal">
      <div class="section-label">The Difference</div>
      <h2 class="section-title">Going Direct vs. Going Through SialSourcing</h2>
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:0;max-width:800px;margin:0 auto;border:1px solid var(--cream-dark);border-radius:var(--radius);overflow:hidden;" class="reveal">
      <?php
      $comparisons = [
        ['Quality Assurance','You trust the factory\'s word','Our inspectors verify every order at our own facility'],
        ['Lab Testing','You arrange it yourself (if at all)','Our in-house lab + accredited third-party labs handle it'],
        ['Payment Safety','Wire money to a foreign account and hope','Pay to our US or EU bank account with legal protection'],
        ['Logistics','You coordinate freight, customs, documents','We handle everything — from factory to your doorstep'],
        ['Manufacturer Selection','You Google and guess','We match you from 100+ audited, certified factories'],
        ['If Something Goes Wrong','You have no recourse','We hold manufacturers accountable — your money, your leverage'],
      ];
      foreach ($comparisons as $i => $c): ?>
      <div style="display:grid;grid-template-columns:2fr 3fr 3fr;border-bottom:<?= $i<count($comparisons)-1?'1px solid var(--cream-dark)':'none' ?>;">
        <div style="padding:1rem;background:var(--navy);color:var(--white);font-size:0.82rem;font-weight:600;display:flex;align-items:center;"><?= $c[0] ?></div>
        <div style="padding:1rem;background:rgba(220,38,38,0.04);font-size:0.82rem;color:#991b1b;display:flex;align-items:center;gap:0.5rem;"><span style="flex-shrink:0;display:inline-flex;"><?= icon('x', 15) ?></span> <?= $c[1] ?></div>
        <div style="padding:1rem;background:rgba(22,163,74,0.04);font-size:0.82rem;color:#166534;display:flex;align-items:center;gap:0.5rem;"><span style="flex-shrink:0;display:inline-flex;"><?= icon('check', 15) ?></span> <?= $c[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== FAQ ===== -->
<?php if (!empty($faqs)): ?>
<section class="section section-cream" id="faq">
  <div class="container" style="display:grid;grid-template-columns:1fr 1.5fr;gap:4rem;align-items:start;">
    <div class="reveal-left">
      <div class="section-label">Got Questions?</div>
      <h2 class="section-title">Frequently Asked Questions</h2>
      <p>Everything you need to know before placing your first order with SialSourcing.</p>
      <a href="<?= SITE_URL ?>/faq" style="display:inline-block;margin-top:1.5rem;" class="btn btn-gold">All FAQs</a>
    </div>
    <div class="faq-list reveal-right">
      <?php foreach ($faqs as $faq): ?>
      <div class="faq-item">
        <button class="faq-q"><?= e($faq['question']) ?><span class="icon">+</span></button>
        <div class="faq-a"><div class="faq-a-inner"><?= nl2br(e($faq['answer'])) ?></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== LATEST INSIGHTS ===== -->
<?php if (!empty($blogs)): ?>
<section class="section section-white">
  <div class="container">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:2rem;" class="reveal">
      <div>
        <div class="section-label">From the Blog</div>
        <h2 class="section-title" style="margin-bottom:0;">Latest Sourcing Insights</h2>
      </div>
      <a href="<?= SITE_URL ?>/blog" class="product-link" style="margin:0;">All articles <?= icon('arrow-right', 16) ?></a>
    </div>
    <div class="blog-grid">
      <?php foreach ($blogs as $i => $b): ?>
      <a href="/blog/<?= e($b['slug']) ?>" class="blog-card reveal delay-<?= $i+1 ?>" style="display:block;">
        <div class="blog-img">
          <?php if ($b['featured_image']): ?>
            <img src="<?= e($b['featured_image']) ?>" alt="<?= e($b['title']) ?>" loading="lazy">
          <?php else: ?>
            <span class="card-placeholder"><?= icon('file-text', 34) ?></span>
          <?php endif; ?>
        </div>
        <div class="blog-body">
          <div class="blog-meta">
            <span class="blog-cat"><?= e($b['category']) ?></span>
            <span><?= date('M d, Y', strtotime($b['published_at'] ?? $b['created_at'])) ?></span>
          </div>
          <h3><?= e($b['title']) ?></h3>
          <span class="blog-link">Read Article <?= icon('arrow-right', 16) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== CTA ===== -->
<div class="cta-section">
  <h2 class="reveal">Ready to Source Without the Risk?</h2>
  <p class="reveal delay-1">Tell us what you need. Within 24 hours, you'll have a sourcing plan, manufacturer shortlist, and indicative pricing — at no cost.</p>
  <div class="reveal delay-2" style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
    <a href="<?= SITE_URL ?>/contact" class="btn btn-gold">Get a Free Sourcing Plan</a>
    <a href="mailto:<?= e(setting('contact_email','info@sialsourcing.com')) ?>" class="btn btn-outline">Email Us Directly</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
