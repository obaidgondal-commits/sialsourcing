<?php
$pageTitle     = 'Products We Source from Sialkot Pakistan';
$pageDesc      = 'SialSourcing sources custom soccer balls, activewear, uniforms & tactical wear, surgical instruments, sports goods, leather products and cutlery from verified Sialkot manufacturers — certified, inspected, and delivered to your door.';
$canonicalPath = '/products';
require_once __DIR__ . '/includes/header.php';
$products = db()->query("SELECT * FROM products WHERE is_active=1 AND sort_order < 100 ORDER BY sort_order")->fetchAll();
?>

<div style="padding-top:80px;">

<!-- Hero -->
<section style="background:var(--navy);padding:5rem 0 4rem;text-align:center;position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 60% at 50% 50%,rgba(201,168,76,0.1) 0%,transparent 70%);"></div>
  <div class="container" style="position:relative;">
    <div class="section-label reveal">What We Source</div>
    <h1 style="color:var(--white);font-size:clamp(2rem,4vw,3rem);margin-bottom:1rem;" class="reveal delay-1">
      Seven Product Categories.<br>Hundreds of Manufacturers.<br>One Trusted Partner.
    </h1>
    <p style="color:rgba(255,255,255,0.55);max-width:600px;margin:0 auto 2rem;" class="reveal delay-2">
      Every product category below is sourced from manufacturers we have personally vetted, audited, and maintained ongoing relationships with. Click any category to learn more.
    </p>
    <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;" class="reveal delay-3">
      <span style="display:inline-flex;align-items:center;gap:0.4rem;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);color:var(--gold);padding:0.35rem 1rem;border-radius:50px;font-size:0.78rem;font-weight:600;"><?= icon('check-circle', 14) ?> 100+ Vetted Manufacturers</span>
      <span style="display:inline-flex;align-items:center;gap:0.4rem;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);color:var(--gold);padding:0.35rem 1rem;border-radius:50px;font-size:0.78rem;font-weight:600;"><?= icon('tool', 14) ?> Quality Inspected</span>
      <span style="display:inline-flex;align-items:center;gap:0.4rem;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);color:var(--gold);padding:0.35rem 1rem;border-radius:50px;font-size:0.78rem;font-weight:600;"><?= icon('globe', 14) ?> Delivered Worldwide</span>
    </div>
  </div>
</section>

<!-- Product Category Cards -->
<section class="section section-cream">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr;gap:2rem;" class="products-page-grid">
      <?php foreach ($products as $i => $p):
        $certs = !empty($p['certifications'])
          ? array_slice(array_map('trim', explode(',', $p['certifications'])), 0, 3)
          : [];
      ?>
      <div class="reveal" style="background:var(--white);border:1px solid var(--cream-dark);border-radius:var(--radius);overflow:hidden;transition:all var(--transition);display:grid;grid-template-columns:1fr;">

        <!-- Product Image -->
        <div style="height:220px;overflow:hidden;background:linear-gradient(135deg,var(--navy),#0a1f3d);display:flex;align-items:center;justify-content:center;position:relative;">
          <?php if (!empty($p['image'])): ?>
            <img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
          <?php else: ?>
            <span class="card-placeholder"><?= icon($p['icon'] ?: 'box', 52) ?></span>
          <?php endif; ?>
          <div style="position:absolute;top:1rem;left:1rem;">
            <span style="background:rgba(201,168,76,0.9);color:var(--navy);padding:0.3rem 0.75rem;border-radius:50px;font-size:0.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;">Category <?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></span>
          </div>
        </div>

        <!-- Product Content -->
        <div style="padding:1.75rem;">
          <h2 style="font-size:1.4rem;margin-bottom:0.75rem;"><?= e($p['title']) ?></h2>
          <p style="font-size:0.92rem;line-height:1.8;margin-bottom:1.25rem;"><?= e($p['description']) ?></p>

          <?php if (!empty($certs)): ?>
          <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.5rem;">
            <?php foreach ($certs as $cert): ?>
            <span style="background:var(--navy);color:rgba(255,255,255,0.8);padding:0.25rem 0.7rem;border-radius:50px;font-size:0.7rem;font-weight:600;"><?= e($cert) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
            <a href="<?= SITE_URL ?>/<?= e($p['slug']) ?>" class="btn btn-gold">
              Explore <?= e($p['title']) ?> <?= icon('arrow-right', 16) ?>
            </a>
            <a href="<?= SITE_URL ?>/contact?product=<?= e($p['slug']) ?>" style="color:var(--gold);font-size:0.88rem;font-weight:600;display:flex;align-items:center;gap:0.3rem;">
              Get a Quote <?= icon('external-link', 14) ?>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Bottom CTA -->
<div class="cta-section">
  <h2 class="reveal">Not Sure What You Need?</h2>
  <p class="reveal delay-1">Tell us your product requirements and we will identify the right manufacturers from our network — at no cost.</p>
  <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;" class="reveal delay-2">
    <a href="<?= SITE_URL ?>/contact" class="btn btn-gold">Get a Free Sourcing Consultation</a>
    <a href="mailto:<?= e(setting('contact_email','info@sialsourcing.com')) ?>" class="btn btn-outline">Email Us Directly</a>
  </div>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
