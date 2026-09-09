<?php
http_response_code(404);
$pageTitle = 'Page Not Found';
$pageDesc  = 'The page you were looking for could not be found.';
$noindex   = true;
$canonicalPath = '/404';
require_once __DIR__ . '/includes/header.php';
?>

<section style="background:var(--navy);min-height:70vh;display:flex;align-items:center;padding:8rem 0 4rem;">
  <div class="container" style="text-align:center;max-width:640px;">
    <div style="font-family:'Playfair Display',serif;font-size:clamp(5rem,14vw,9rem);font-weight:900;color:var(--gold);line-height:1;">404</div>
    <h1 style="color:var(--white);font-size:clamp(1.4rem,3vw,2rem);margin:1rem 0 0.75rem;">This page has sailed without us.</h1>
    <p style="color:rgba(255,255,255,0.6);line-height:1.7;margin-bottom:2.25rem;">The address may have changed or never existed. Everything we source is still right where it should be:</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/products" class="btn btn-gold">Browse Products</a>
      <a href="<?= SITE_URL ?>/blog" class="btn btn-outline">Read the Blog</a>
      <a href="<?= SITE_URL ?>/contact" class="btn btn-outline">Get a Quote</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
