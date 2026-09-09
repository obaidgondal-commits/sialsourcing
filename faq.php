<?php
$pageTitle     = 'Frequently Asked Questions';
$pageDesc      = 'Common questions about sourcing from Pakistan with SialSourcing — MOQ, lead times, quality, payments, and more.';
$canonicalPath = '/faq';
require_once __DIR__ . '/includes/header.php';

$faqs = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
$cats = array_unique(array_column($faqs, 'category'));

if (!empty($faqs)):
$faqSchema = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type'          => 'Question',
        'name'           => $f['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
    ], $faqs),
];
?>
<script type="application/ld+json"><?= json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>

<div style="padding-top:80px;">

<section style="background:var(--navy);padding:4rem 0 3rem;text-align:center;">
  <div class="container">
    <div class="section-label reveal">FAQ</div>
    <h1 style="color:var(--white);font-size:clamp(2rem,4vw,3rem);margin-bottom:1rem;" class="reveal delay-1">Frequently Asked Questions</h1>
    <p style="color:rgba(255,255,255,0.55);max-width:520px;margin:0 auto;" class="reveal delay-2">Everything you need to know before placing your first sourcing order with SialSourcing.</p>
  </div>
</section>

<section class="section section-white">
  <div class="container" style="display:grid;grid-template-columns:240px 1fr;gap:3rem;align-items:start;">

    <!-- Category sidebar -->
    <div style="position:sticky;top:100px;" class="reveal-left">
      <div style="font-size:0.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:1rem;">Categories</div>
      <?php foreach ($cats as $c): ?>
      <a href="#cat-<?= e(strtolower(str_replace(' ','-',$c))) ?>" style="display:block;padding:0.6rem 1rem;border-radius:8px;color:var(--text);font-size:0.9rem;font-weight:500;margin-bottom:0.35rem;transition:all 0.2s;border:1px solid transparent;" 
         onmouseover="this.style.background='rgba(201,168,76,0.08)';this.style.borderColor='rgba(201,168,76,0.2)'" 
         onmouseout="this.style.background='transparent';this.style.borderColor='transparent'">
        <?= e($c) ?>
      </a>
      <?php endforeach; ?>
      <div style="margin-top:2rem;padding:1.25rem;background:var(--cream);border-radius:var(--radius);">
        <div style="font-size:0.85rem;font-weight:600;color:var(--text);margin-bottom:0.5rem;">Still have questions?</div>
        <p style="font-size:0.8rem;color:var(--muted);margin-bottom:0.75rem;">Our team responds within 24 hours.</p>
        <a href="/contact" class="btn btn-gold" style="width:100%;text-align:center;display:block;padding:0.65rem 1rem;font-size:0.85rem;">Contact Us</a>
      </div>
    </div>

    <!-- FAQ list by category -->
    <div class="reveal-right">
      <?php foreach ($cats as $cat):
        $catFaqs = array_filter($faqs, fn($f) => $f['category'] === $cat);
      ?>
      <div id="cat-<?= e(strtolower(str_replace(' ','-',$cat))) ?>" style="margin-bottom:2.5rem;">
        <h2 style="font-size:1.2rem;font-weight:700;color:var(--text);padding-bottom:0.75rem;border-bottom:2px solid var(--cream-dark);margin-bottom:1rem;"><?= e($cat) ?></h2>
        <div class="faq-list">
          <?php foreach ($catFaqs as $f): ?>
          <div class="faq-item">
            <button class="faq-q"><?= e($f['question']) ?><span class="icon">+</span></button>
            <div class="faq-a"><div class="faq-a-inner"><?= nl2br(e($f['answer'])) ?></div></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if (empty($faqs)): ?>
      <!-- Fallback content if DB is empty -->
      <?php foreach ([
        'Orders' => [
          ['What is the minimum order quantity (MOQ)?','MOQ varies by product. Surgical instruments: 100 units per SKU. Sports goods: 500 pieces. Leather goods: 200 units. Cutlery sets: 200 sets. We can negotiate lower MOQs for new buyers placing trial orders — contact us to discuss.'],
          ['How long does production take?','Standard production is 4–8 weeks from order confirmation and deposit payment. Rush orders (2–3 weeks) are available for select products at a premium. Sampling alone takes 10–15 days.'],
          ['Can I combine different products in one order?','Yes. We can consolidate orders from multiple manufacturers into a single shipment (LCL — Less than Container Load). This is common and saves shipping costs significantly.'],
        ],
        'Quality' => [
          ['How do you verify manufacturers are legitimate?','We conduct physical factory audits before onboarding any manufacturer. We verify certificates, inspect production equipment, interview workers, and review past export records. Audit reports are shared with buyers on request.'],
          ['What happens if I receive defective products?','If QC-inspected goods arrive with defects not noted in the pre-shipment report, we engage the manufacturer directly for replacement or credit. Our ongoing manufacturer relationships mean we can hold them accountable effectively.'],
        ],
        'Payments' => [
          ['What are the payment terms?','Standard terms: 30% advance on order confirmation, 70% before shipment release. For orders above $50,000, LC (Letter of Credit) is accepted. We accept T/T (bank wire transfer) in USD.'],
          ['Are there any hidden fees?','No. Our sourcing fee is disclosed upfront and applied to the ex-factory price. Freight, insurance, and any third-party inspection costs are charged at actual cost with full receipts shared.'],
        ],
      ] as $cat => $items): ?>
      <div style="margin-bottom:2.5rem;">
        <h2 style="font-size:1.2rem;font-weight:700;color:var(--text);padding-bottom:0.75rem;border-bottom:2px solid var(--cream-dark);margin-bottom:1rem;"><?= $cat ?></h2>
        <div class="faq-list">
          <?php foreach ($items as $f): ?>
          <div class="faq-item">
            <button class="faq-q"><?= $f[0] ?><span class="icon">+</span></button>
            <div class="faq-a"><div class="faq-a-inner"><?= $f[1] ?></div></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>

  </div>
</section>

<div class="cta-section">
  <h2 class="reveal">Didn't Find Your Answer?</h2>
  <p class="reveal delay-1">Our sourcing experts are available via email and WhatsApp. We typically respond within 4 business hours.</p>
  <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;" class="reveal delay-2">
    <a href="/contact" class="btn btn-gold">Ask Us Directly</a>
    <a href="https://wa.me/<?= preg_replace('/\D/','',(setting('contact_phone',''))) ?>" class="btn btn-outline" target="_blank" rel="noopener"><?= icon('message-circle', 16) ?> WhatsApp Us</a>
  </div>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
