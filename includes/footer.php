<?php // includes/footer.php ?>
</main>
<footer>
  <div class="footer-inner">

    <!-- Brand -->
    <div class="footer-brand">
      <h3><?= e(setting('site_name','SialSourcing')) ?><span>.</span></h3>
      <p>End-to-end supply chain solutions from Sialkot, Pakistan. We source, inspect, test, and deliver — so your products arrive right, every time.</p>
      <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:0.4rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.78rem;color:rgba(255,255,255,0.35);">
          <span>🇺🇸</span> Dallas, Texas, USA
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.78rem;color:rgba(255,255,255,0.35);">
          <span>🇫🇷</span> Paris, France
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.78rem;color:rgba(255,255,255,0.35);">
          <span>🇵🇰</span> Sialkot, Punjab, Pakistan
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="footer-col">
      <h4>Products</h4>
      <?php
      $footerProds = db()->query("SELECT title, slug FROM products WHERE is_active=1 AND sort_order < 100 ORDER BY sort_order LIMIT 7")->fetchAll();
      foreach ($footerProds as $p): ?>
        <a href="<?= SITE_URL ?>/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a>
      <?php endforeach; ?>
      <a href="<?= SITE_URL ?>/products" style="color:var(--gold);margin-top:0.25rem;">View All Products →</a>
    </div>

    <!-- Company -->
    <div class="footer-col">
      <h4>Company</h4>
      <a href="<?= SITE_URL ?>/about">About Us</a>
      <a href="<?= SITE_URL ?>/team">Our Team</a>
      <a href="<?= SITE_URL ?>/solutions">Our Solutions</a>
      <a href="<?= SITE_URL ?>/lab-qc">Lab &amp; QC</a>
      <a href="<?= SITE_URL ?>/blog">Blog</a>
      <a href="<?= SITE_URL ?>/faq">FAQs</a>
      <a href="<?= SITE_URL ?>/resources">Buyer Resources</a>
      <a href="<?= SITE_URL ?>/for-manufacturers">For Manufacturers</a>
      <a href="<?= SITE_URL ?>/qc-inspection-request">QC Inspection Service</a>
      <a href="<?= SITE_URL ?>/contact">Get a Quote</a>
    </div>

    <!-- Contact -->
    <div class="footer-col">
      <h4>Contact Us</h4>
      <a href="mailto:<?= e(setting('contact_email','info@sialsourcing.com')) ?>">
        <?= e(setting('contact_email','info@sialsourcing.com')) ?>
      </a>
      <a href="tel:<?= e(setting('contact_phone','+92-300-1100110')) ?>">
        <?= e(setting('contact_phone','+92-300-1100110')) ?>
      </a>
      <div style="margin-top:1rem;">
        <div style="font-size:0.65rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:0.5rem;">Pay In</div>
        <div style="font-size:0.82rem;color:rgba(255,255,255,0.5);line-height:1.8;">
          USD — Dallas, USA<br>
          EUR / GBP — Paris, France
        </div>
      </div>
      <div style="margin-top:1rem;">
        <a href="<?= SITE_URL ?>/contact" style="display:inline-flex;align-items:center;gap:0.4rem;background:var(--gold);color:var(--navy);font-size:0.75rem;font-weight:700;padding:0.5rem 1rem;border-radius:50px;text-decoration:none;transition:all 0.3s;">
          Get a Free Quote →
        </a>
      </div>
    </div>

  </div>

  <!-- Footer Bottom -->
  <div class="footer-bottom">
    <span>&copy; <?= date('Y') ?> <?= e(setting('site_name','SialSourcing')) ?>. All rights reserved.</span>
    <span style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;justify-content:center;">
      <a href="<?= SITE_URL ?>/about" style="color:rgba(255,255,255,0.3);font-size:0.75rem;transition:color 0.2s;">About</a>
      <a href="<?= SITE_URL ?>/contact" style="color:rgba(255,255,255,0.3);font-size:0.75rem;transition:color 0.2s;">Contact</a>
      <a href="<?= SITE_URL ?>/lab-qc" style="color:rgba(255,255,255,0.3);font-size:0.75rem;transition:color 0.2s;">Lab &amp; QC</a>
      <span style="color:rgba(255,255,255,0.2);">|</span>
      <span style="color:rgba(255,255,255,0.25);font-size:0.75rem;">Made in Sialkot, Pakistan 🇵🇰</span>
    </span>
  </div>
</footer>

<!-- Floating WhatsApp button -->
<a class="wa-float" aria-label="Chat with us on WhatsApp"
   href="https://wa.me/<?= preg_replace('/\D/', '', setting('contact_phone', '+92-300-1100110')) ?>?text=<?= urlencode("Hi! I'm interested in sourcing products from Pakistan.") ?>"
   target="_blank" rel="noopener">
  <?= icon('whatsapp', 30) ?>
</a>

<!-- JS loaded at bottom — zero render blocking -->
<script src="<?= SITE_URL ?>/assets/js/main.js?v=4" defer></script>
</body>
</html>
