<?php
$pageTitle = 'About SialSourcing — End-to-End Supply Chain Partner from Sialkot Pakistan';
$pageDesc  = 'SialSourcing is an end-to-end supply chain company with offices in Dallas, Paris, and Sialkot. We source, inspect, test, and deliver products from Pakistan\'s finest manufacturers to buyers worldwide.';
$canonicalPath = '/about';
require_once __DIR__ . '/includes/header.php';
?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AboutPage",
  "name": "About SialSourcing",
  "description": "SialSourcing is an end-to-end supply chain partner based in Sialkot, Pakistan with offices in Dallas and Paris. We source, inspect, test, and deliver products from Pakistan's finest manufacturers to buyers worldwide.",
  "url": "https://sialsourcing.com/about",
  "mainEntity": {
    "@type": "Organization",
    "name": "SialSourcing",
    "url": "https://sialsourcing.com",
    "foundingLocation": "Sialkot, Pakistan",
    "description": "End-to-end supply chain solutions from Sialkot, Pakistan",
    "areaServed": ["US", "GB", "FR", "DE", "AU", "AE", "CA"],
    "address": [
      {
        "@type": "PostalAddress",
        "addressLocality": "Dallas",
        "addressRegion": "TX",
        "addressCountry": "US"
      },
      {
        "@type": "PostalAddress",
        "addressLocality": "Paris",
        "addressCountry": "FR"
      },
      {
        "@type": "PostalAddress",
        "addressLocality": "Sialkot",
        "addressRegion": "Punjab",
        "addressCountry": "PK"
      }
    ]
  }
}
</script>

<style>
.about-hero {
  background: linear-gradient(160deg, #030a18 0%, #08122a 40%, #0c1d3d 70%, #0a1830 100%);
  padding: calc(120px + var(--safe-top, 0px)) 0 5rem;
  position: relative;
  overflow: hidden;
  text-align: center;
}
.about-hero::before {
  content: '';
  position: absolute;
  top: 20%;
  left: 50%;
  transform: translateX(-50%);
  width: 700px;
  height: 700px;
  background: radial-gradient(circle, rgba(201,168,76,0.07) 0%, transparent 70%);
  pointer-events: none;
}
.about-hero-inner { position: relative; z-index: 2; }
.about-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 1.5rem;
}
.about-eyebrow::before, .about-eyebrow::after {
  content: '';
  display: block;
  width: 30px;
  height: 1px;
  background: rgba(201,168,76,0.5);
}
.about-hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2rem, 4vw, 3.5rem);
  font-weight: 900;
  color: var(--white);
  line-height: 1.15;
  margin-bottom: 1.5rem;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}
.about-hero h1 em {
  color: var(--gold);
  font-style: normal;
}
.about-hero-desc {
  color: rgba(255,255,255,0.55);
  font-size: 1.05rem;
  line-height: 1.85;
  max-width: 640px;
  margin: 0 auto 2.5rem;
}
.about-stats-strip {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  max-width: 900px;
  margin: 3rem auto 0;
  border: 1px solid rgba(201,168,76,0.15);
  border-radius: var(--radius);
  overflow: hidden;
}
.about-stat {
  padding: 1.5rem 1rem;
  border-right: 1px solid rgba(201,168,76,0.1);
  text-align: center;
}
.about-stat:last-child { border-right: none; }
.about-stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 900;
  color: var(--gold);
  line-height: 1;
  display: block;
}
.about-stat-label {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: rgba(255,255,255,0.4);
  display: block;
  margin-top: 0.4rem;
}
.story-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 5rem;
  align-items: center;
}
.story-pull-quote {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.3rem, 2.5vw, 1.8rem);
  font-weight: 700;
  color: var(--navy);
  line-height: 1.4;
  border-left: 3px solid var(--gold);
  padding-left: 1.5rem;
  margin: 2rem 0;
}
.diff-card {
  display: flex;
  gap: 1.25rem;
  align-items: flex-start;
  padding: 1.5rem;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(201,168,76,0.12);
  border-radius: var(--radius);
  transition: all 0.3s ease;
}
.diff-card:hover {
  background: rgba(201,168,76,0.06);
  border-color: rgba(201,168,76,0.3);
}
.diff-icon {
  font-size: 1.6rem;
  flex-shrink: 0;
  line-height: 1;
  width: 48px;
  height: 48px;
  background: rgba(201,168,76,0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.office-card {
  padding: 2rem;
  border: 1px solid var(--cream-dark);
  border-radius: var(--radius);
  background: var(--white);
  transition: all 0.3s ease;
}
.office-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 50px rgba(0,0,0,0.08);
  border-color: var(--gold);
}
.office-flag { font-size: 2.5rem; margin-bottom: 1rem; }
.office-name { font-size: 1.15rem; margin-bottom: 0.1rem; }
.office-role {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 1rem;
}
.office-desc { font-size: 0.9rem; line-height: 1.8; margin-bottom: 1rem; }
.office-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}
.office-tag {
  background: var(--cream);
  color: var(--navy);
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.25rem 0.65rem;
  border-radius: 50px;
}
.promise-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}
.promise-card {
  text-align: center;
  padding: 2rem 1.5rem;
  background: var(--white);
  border: 1px solid var(--cream-dark);
  border-radius: var(--radius);
}
.promise-number {
  font-family: 'Playfair Display', serif;
  font-size: 3rem;
  font-weight: 900;
  color: rgba(201,168,76,0.2);
  line-height: 1;
  margin-bottom: 0.5rem;
}
.promise-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: 0.5rem;
}
.promise-desc { font-size: 0.85rem; line-height: 1.7; }

/* Mobile */
@media (max-width: 767px) {
  .about-stats-strip { grid-template-columns: repeat(2, 1fr); }
  .about-stat { border-bottom: 1px solid rgba(201,168,76,0.1); }
  .story-grid { grid-template-columns: 1fr; gap: 2.5rem; }
  .promise-grid { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .about-stats-strip { grid-template-columns: 1fr 1fr; }
}
</style>

<div style="padding-top:0;">

<!-- ═══ HERO ═══ -->
<section class="about-hero">
  <div class="container about-hero-inner">
    <div class="about-eyebrow">About SialSourcing</div>
    <h1>We Don't Make Products.<br>We Make Sure <em>You Get Them Right.</em></h1>
    <p class="about-hero-desc">SialSourcing is an end-to-end supply chain company that sits between international buyers and Pakistani manufacturers — protecting your money, verifying your quality, and delivering products to your doorstep from one of the world's greatest manufacturing cities.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/contact" class="btn btn-gold">Get a Free Sourcing Plan</a>
      <a href="<?= SITE_URL ?>/team" class="btn btn-outline">Meet Our Team</a>
    </div>
    <div class="about-stats-strip reveal">
      <div class="about-stat">
        <span class="about-stat-num" data-count="100+">0</span>
        <span class="about-stat-label">Vetted Manufacturers</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-num" data-count="30+">0</span>
        <span class="about-stat-label">Countries Served</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-num" data-count="3">0</span>
        <span class="about-stat-label">Global Offices</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-num" data-count="100%">0</span>
        <span class="about-stat-label">Orders Inspected</span>
      </div>
    </div>
  </div>
</section>

<!-- ═══ THE STORY ═══ -->
<section class="section section-white">
  <div class="container">
    <div class="story-grid">
      <div class="reveal-left">
        <div class="section-label">Our Story</div>
        <h2 class="section-title">Built in Sialkot.<br>Built for Global Buyers.</h2>
        <div class="story-pull-quote">"Sialkot makes products that supply the world. We make sure the world can access them safely."</div>
        <a href="<?= SITE_URL ?>/contact" class="btn btn-gold" style="margin-top:1rem;">Work With Us</a>
      </div>
      <div class="reveal-right" style="font-size:0.97rem;line-height:2;color:#4a4a5a;">
        <p style="margin-bottom:1.25rem;">Sialkot is one of the most extraordinary manufacturing cities on earth. A city of four million people that produces over 70% of the world's hand-stitched footballs, a significant share of global surgical instruments, and exports billions of dollars worth of sportswear, leather goods, and cutlery every year. The factories here have supplied FIFA World Cups, Olympic teams, hospital chains across the United States and Europe, and retail giants on six continents.</p>
        <p style="margin-bottom:1.25rem;">But for international buyers — especially those sourcing from Pakistan for the first time — working directly with these factories is a minefield. Language barriers. Payment risk. Quality inconsistencies. Missing compliance certificates. Shipments stuck at customs because of wrong HS codes or incomplete fumigation documentation. We saw these problems repeat themselves year after year, costing buyers thousands of dollars and months of lost time.</p>
        <p style="margin-bottom:1.25rem;"><strong style="color:var(--navy);">SialSourcing was built to solve exactly this problem.</strong></p>
        <p style="margin-bottom:1.25rem;">We are not a manufacturer. We are not a trading company that buys and resells. We are a dedicated supply chain partner — embedded in the Sialkot manufacturing ecosystem with deep relationships across 100+ vetted factories, but working exclusively in the interest of our international buyers.</p>
        <p>Every order that goes through SialSourcing is quality inspected at our own facility, lab tested against international standards, documented for customs compliance, and shipped with full tracking and insurance. Our buyers do not chase factories for updates. They do not worry about fraudulent suppliers. They do not navigate Pakistani customs regulations. That is our job — and we do it from factory floor to warehouse door.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══ MISSION ═══ -->
<section class="section section-cream">
  <div class="container">
    <div class="two-col-grid" style="align-items:center;">
      <div class="reveal-left">
        <div class="section-label">Our Mission</div>
        <h2 class="section-title">Making Pakistan's Manufacturing Excellence Accessible to the World</h2>
        <p style="margin-top:1rem;font-size:0.95rem;line-height:1.85;">Pakistan's manufacturers produce world-class products that supply some of the biggest brands on earth. But accessing that manufacturing excellence safely — without payment risk, quality failures, or compliance gaps — has historically required either deep local knowledge or costly on-ground representation that most buyers simply do not have.</p>
      </div>
      <div class="reveal-right">
        <div style="background:var(--navy);border-radius:var(--radius);padding:2.5rem;">
          <h3 style="color:var(--gold);font-family:'DM Sans',sans-serif;font-size:0.65rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin-bottom:1.5rem;">What We Believe</h3>
          <?php
          $beliefs = [
            'Every international buyer deserves access to the world\'s best manufacturers — regardless of whether they have a local team on the ground.',
            'Quality is not negotiable. Every order, every time, regardless of value or volume.',
            'A sourcing partner should work for the buyer — not collect commissions from factories.',
            'Transparency builds long-term relationships. We share everything — inspection reports, test results, factory audits.',
            'Pakistan\'s manufacturers deserve global recognition for the extraordinary quality they produce.',
          ];
          foreach ($beliefs as $i => $b): ?>
          <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1.25rem;<?= $i === count($beliefs)-1 ? '' : '' ?>">
            <div style="width:24px;height:24px;background:rgba(201,168,76,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
              <span style="color:var(--gold);font-size:0.7rem;font-weight:700;"><?= $i+1 ?></span>
            </div>
            <p style="color:rgba(255,255,255,0.65);font-size:0.88rem;line-height:1.7;"><?= $b ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ WHAT MAKES US DIFFERENT ═══ -->
<section class="section section-dark">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto 3rem;" class="reveal">
      <div class="section-label">What Makes Us Different</div>
      <h2 class="section-title" style="color:var(--white);">Not a Sourcing Agent.<br>A Supply Chain Company.</h2>
      <p style="color:rgba(255,255,255,0.5);">Most sourcing agents in Pakistan find you a factory and collect a commission. Here is what we do differently — and why it matters for your business.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:1rem;max-width:960px;margin:0 auto;">
      <?php
      $diffs = [
        ['factory','Own Inspection Facility — Not Outsourced','We operate our own QC inspection center in Sialkot staffed by our own team. We do not rely on factory self-inspections or outsource to third-party inspection agencies we do not control. Every order is checked by our people, on our clock, to our standards.'],
        ['tool','Own Testing Lab Plus Third-Party Certification','Our in-house lab handles functional and durability testing. For international compliance certification — ISO 13485, CE marking, FDA clearance, REACH chemical analysis — we partner with internationally accredited laboratories whose test reports are accepted by customs authorities worldwide.'],
        ['dollar-sign','Pay Locally in Your Own Currency','You pay to our Dallas office in USD or our Paris office in EUR or GBP. There are no risky international wire transfers to unknown Pakistani bank accounts. Your payment is held securely and released to the manufacturer only after our QC team approves your order.'],
        ['file-text','Complete Export Documentation Management','We prepare every document your shipment requires — commercial invoices, packing lists, certificates of origin, fumigation certificates, phytosanitary certificates, and HS code classification. Nothing gets held at your customs because of missing or incorrect paperwork.'],
        ['shield-check','Full Buyer Protection','If a QC-approved order arrives with defects that were not identified in our inspection report, we pursue the manufacturer on your behalf at no additional cost. We maintain ongoing relationships with all our factories — our word carries weight on the factory floor in a way that a distant international buyer\'s complaint simply does not.'],
        ['truck','Door-to-Door Logistics Management','We do not hand you a shipment at the Sialkot factory gate and wish you luck. We manage freight booking, container loading, port clearance, marine insurance, and delivery tracking all the way to your warehouse door. Sea freight, air cargo, or express courier — we coordinate everything.'],
        ['message-circle','One Point of Contact for Everything','One account manager. One WhatsApp number. One email thread. You never have to chase five different people across three time zones to find out where your order is. We give you clear updates at every stage — production, inspection, shipping, and delivery.'],
        ['shield','Legal Protection in Your Jurisdiction','Our US and European offices provide legal presence in your jurisdiction. Contracts, invoices, and dispute resolution are handled under US commercial law for North American buyers and EU commercial frameworks for European buyers. You are not navigating Pakistani commercial law from the other side of the world.'],
      ];
      foreach ($diffs as $i => $d): ?>
      <div class="diff-card reveal delay-<?= ($i%3)+1 ?>">
        <div class="diff-icon" style="color:var(--gold);"><?= icon($d[0], 24) ?></div>
        <div>
          <h3 style="color:var(--white);font-size:0.95rem;margin-bottom:0.4rem;"><?= $d[1] ?></h3>
          <p style="color:rgba(255,255,255,0.5);font-size:0.86rem;line-height:1.75;"><?= $d[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ OUR PROMISE ═══ -->
<section class="section section-cream">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto 3rem;" class="reveal">
      <div class="section-label">Our Promise</div>
      <h2 class="section-title">Three Guarantees We Make to Every Buyer</h2>
    </div>
    <div class="promise-grid">
      <div class="promise-card reveal delay-1">
        <div class="promise-number">01</div>
        <div class="promise-title">Quality Verified Before Shipment</div>
        <p class="promise-desc">Nothing leaves Pakistan without passing our inspection. Every order receives a documented QC report with photographs before we release it for shipping. You approve before we ship.</p>
      </div>
      <div class="promise-card reveal delay-2">
        <div class="promise-number">02</div>
        <div class="promise-title">Payment Protected Until Approval</div>
        <p class="promise-desc">Your money goes to the manufacturer only after quality is confirmed. You pay to our US or European office in your currency. Your investment is protected throughout the entire process.</p>
      </div>
      <div class="promise-card reveal delay-3">
        <div class="promise-number">03</div>
        <div class="promise-title">Response Within 24 Hours</div>
        <p class="promise-desc">Every enquiry receives a sourcing plan, manufacturer shortlist, and indicative pricing within one business day — at no cost and with no obligation to proceed.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══ GLOBAL OFFICES ═══ -->
<section class="section section-white">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto 3rem;" class="reveal">
      <div class="section-label">Global Presence</div>
      <h2 class="section-title">Three Offices. Three Continents. One Mission.</h2>
      <p>We operate where our buyers are and where the manufacturing happens. This dual presence is what makes our supply chain work seamlessly across every time zone.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:1.5rem;" class="offices-grid">
      <?php
      $offices = [
        [
          '🇺🇸',
          'Dallas, United States',
          'Americas Headquarters',
          'Our US office handles buyer relationships, contracts, and USD payments for clients across North America, Latin America, the Caribbean, and the Asia Pacific region. When you work with SialSourcing from the United States, you are working with a US-based company that invoices in USD, operates under US commercial law, and is reachable during your business hours.',
          ['Sales and Client Relations','USD Payments','US Commercial Contracts','North American Logistics']
        ],
        [
          '🇫🇷',
          'Paris, France',
          'European Headquarters',
          'Our Paris office serves the European Union, United Kingdom, and Middle Eastern markets. EUR and GBP payments are accepted directly to a French bank account. EU trade compliance documentation, CE marking verification, and REACH compliance are all coordinated from our European office — making import into EU member states as smooth as possible for our buyers.',
          ['EUR and GBP Payments','EU Trade Compliance','CE and REACH Verification','European Client Relations']
        ],
        [
          '🇵🇰',
          'Sialkot, Pakistan',
          'Operations and Quality Hub',
          'The heart of SialSourcing\'s operation. Our Sialkot office is home to our QC inspection facility, in-house testing lab, warehousing space, and the team that manages our manufacturer relationships on the ground. Every factory visit, every production inspection, every pre-shipment QC check, and every export documentation process is coordinated from Sialkot. This is where the work actually happens.',
          ['QC Inspection Facility','In-House Testing Lab','Warehouse and Logistics','Factory Relationship Management']
        ],
      ];
      foreach ($offices as $i => $o): ?>
      <div class="office-card reveal delay-<?= $i+1 ?>">
        <div class="office-flag"><?= $o[0] ?></div>
        <h3 class="office-name"><?= $o[1] ?></h3>
        <div class="office-role"><?= $o[2] ?></div>
        <p class="office-desc"><?= $o[3] ?></p>
        <div class="office-tags">
          <?php foreach ($o[4] as $tag): ?>
          <span class="office-tag"><?= $tag ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ PRODUCT CATEGORIES ═══ -->
<section class="section section-cream">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto 2.5rem;" class="reveal">
      <div class="section-label">What We Source</div>
      <h2 class="section-title">Seven Categories. Hundreds of Manufacturers. One Partner.</h2>
      <p>Every product category below is sourced from manufacturers we have personally vetted, audited, and maintained ongoing relationships with in Sialkot.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr;gap:1rem;max-width:900px;margin:0 auto;" class="solutions-grid-home">
      <?php
      $cats = [
        ['soccer-ball','Custom Soccer Balls','custom-soccer-balls','Match, training, futsal, and promotional soccer balls with full custom printing — hand-stitched or thermo-bonded in the city that makes 70% of the world\'s hand-stitched supply.'],
        ['shirt','Activewear & Sports Uniforms','activewear-sports-uniforms','Custom sublimation jerseys, yoga pants, compression wear, and gym apparel. OEM branding, low MOQ, OEKO-TEX certified fabrics, fast lead times.'],
        ['shield','Uniforms & Tactical Wear','uniforms-tactical-wear','Military-style and civil uniforms, police and security apparel, duty gear, medical scrubs, workwear, and school uniforms — built to spec for private-sector buyers.'],
        ['scissors','Surgical Instruments','surgical-instruments','ISO 13485 certified, CE marked, FDA registered surgical and dental instruments from Sialkot — the world\'s surgical capital producing over 150 million instruments annually.'],
        ['target','Sports Goods','sports-goods','FIFA approved footballs, field hockey sticks, boxing gloves, cricket equipment, and gym gear from manufacturers supplying the world\'s biggest sports brands.'],
        ['briefcase','Leather Products','leather-goods','REACH compliant full-grain leather gloves, jackets, wallets, and accessories from ethical tanneries. EN388 certified work gloves and CE marked motorcycle gear available.'],
        ['cutlery','Cutlery & Kitchenware','cutlery','18/10 stainless steel flatware, professional chef knives, hotel grade cutlery, and kitchen tool sets. FDA compliant, dishwasher safe, custom branded.'],
      ];
      foreach ($cats as $i => $c): ?>
      <div class="reveal delay-<?= ($i%3)+1 ?>" style="display:flex;gap:1.25rem;align-items:center;padding:1.25rem 1.5rem;border:1px solid var(--cream-dark);border-radius:var(--radius);background:var(--white);transition:all var(--transition);">
        <div style="flex-shrink:0;width:52px;height:52px;background:rgba(201,168,76,0.08);border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--gold);"><?= icon($c[0], 24) ?></div>
        <div style="flex:1;">
          <h3 style="font-size:0.95rem;margin-bottom:0.2rem;"><?= $c[1] ?></h3>
          <p style="font-size:0.83rem;line-height:1.6;"><?= $c[3] ?></p>
        </div>
        <a href="<?= SITE_URL ?>/<?= $c[2] ?>" style="flex-shrink:0;background:var(--navy);color:rgba(255,255,255,0.85);padding:0.5rem 1.1rem;border-radius:50px;font-size:0.72rem;font-weight:600;white-space:nowrap;transition:all var(--transition);text-decoration:none;">
          Explore →
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ CTA ═══ -->
<div class="cta-section">
  <h2 class="reveal">Ready to Source from Pakistan Without the Risk?</h2>
  <p class="reveal delay-1">Tell us what you need. Within 24 hours you will have a sourcing plan, manufacturer shortlist, and indicative pricing — completely free and with no obligation.</p>
  <div class="reveal delay-2" style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
    <a href="<?= SITE_URL ?>/contact" class="btn btn-gold">Get a Free Sourcing Plan</a>
    <a href="<?= SITE_URL ?>/team" class="btn btn-outline">Meet Our Team</a>
  </div>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>