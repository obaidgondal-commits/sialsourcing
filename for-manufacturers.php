<?php
$pageTitle     = 'For Manufacturers — Join Our Vetted Factory Network | Export Orders from International Buyers';
$pageDesc      = 'Sialkot & Pakistan manufacturers: partner with SialSourcing to receive export orders from US and European buyers. Apply to join our vetted network — audit-based onboarding, reliable payment.';
$canonicalPath = '/for-manufacturers';
require_once __DIR__ . '/config.php';
header('Cache-Control: no-store, private');


$success = isset($_SESSION['manufacturer_application_received']) && time() - (int)$_SESSION['manufacturer_application_received'] < 600;
unset($_SESSION['manufacturer_application_received']);
$error = '';
$values = [];
$limits = ['company_name' => 200, 'contact_name' => 200, 'email' => 200, 'phone' => 50, 'city' => 100, 'product_categories' => 255, 'certifications' => 255, 'website_url' => 255, 'message' => 5000];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = false;
    $malformed = false;
    foreach ($limits as $field => $limit) {
        $value = $_POST[$field] ?? '';
        if (!is_string($value)) {
            $malformed = true;
            $value = '';
        }
        $values[$field] = trim($value);
        $_POST[$field] = $values[$field];
    }
    $honeypot = $_POST['portfolio'] ?? '';
    if (!is_string($honeypot)) $malformed = true;

    if ($malformed) {
        http_response_code(400);
        $error = 'Invalid form data. Please check the fields and try again.';
    } elseif (!csrf_ok()) {
        http_response_code(400);
        $error = 'Your session expired — refresh this page and resubmit the form.';
    } elseif ($honeypot !== '') {
        $success = true;
    } else {
        foreach (['company_name', 'contact_name', 'email', 'city', 'product_categories'] as $field) {
            if ($values[$field] === '') $error = 'Please fill in all required fields.';
        }
        foreach ($limits as $field => $limit) {
            if (strlen($values[$field]) > $limit) $error = 'One or more fields are too long. Please shorten them and try again.';
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email address.';
        if ($values['website_url'] !== '' && (!filter_var($values['website_url'], FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($values['website_url'], PHP_URL_SCHEME) ?: ''), ['https','http'], true))) {
            $error = 'Please enter a complete website address starting with https:// or http://.';
        }

        if ($error === '' && !rate_limit('manufacturer_application:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 900)) {
            http_response_code(429);
            header('Retry-After: 900');
            $error = 'Too many requests. Please wait 15 minutes and try again, or contact us by email.';
        }
        if ($error === '') {
            try {
                db()->prepare("INSERT INTO manufacturer_applications (company_name,contact_name,email,phone,city,product_categories,certifications,website,message) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$values['company_name'],$values['contact_name'],$values['email'],$values['phone'],$values['city'],$values['product_categories'],$values['certifications'],$values['website_url'],$values['message']]);
                $subject = 'New Manufacturer Application';
                $body = "Company: {$values['company_name']}
Contact: {$values['contact_name']} <{$values['email']}>
Phone: {$values['phone']}
City: {$values['city']}
Categories: {$values['product_categories']}
Certifications: {$values['certifications']}
Website: {$values['website_url']}

{$values['message']}";
                $success = true;
            } catch (Throwable $exception) {
                http_response_code(503);
                error_log('for-manufacturers.php: enquiry could not be saved.');
                $error = 'We could not save your request. Please try again or contact us by email.';
            }
            if ($success) {
                try {
                    if (!send_notification(setting('contact_email', 'info@sialsourcing.com'), $subject, $body, $values['email'])) {
                        error_log('for-manufacturers.php: enquiry saved; email notification was not sent.');
                    }
                } catch (Throwable $exception) {
                    error_log('for-manufacturers.php: enquiry saved; email notification failed.');
                }
            }
        }
    }
    if ($success) {
        $_SESSION['manufacturer_application_received'] = time();
        header('Location: /for-manufacturers', true, 303);
        exit;
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<div style="padding-top:80px;">

<section style="background:var(--navy);padding:4.5rem 0 3.5rem;text-align:center;">
  <div class="container">
    <div class="section-label reveal">For Manufacturers</div>
    <h1 style="color:var(--white);font-size:clamp(2rem,4vw,3rem);margin-bottom:1rem;" class="reveal delay-1">You Make It Well.<br><em style="color:var(--gold);font-style:normal;">We Bring the Buyers.</em></h1>
    <p style="color:rgba(255,255,255,0.55);max-width:600px;margin:0 auto;" class="reveal delay-2">SialSourcing places export orders from US and European buyers with vetted Pakistani factories. If your quality can pass our audit, your factory can receive orders without spending a rupee on overseas marketing.</p>
  </div>
</section>

<section class="section section-white">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;max-width:1000px;margin:0 auto;">
      <div class="us-tile reveal delay-1">
        <div class="us-tile-icon"><?= icon('globe', 20) ?></div>
        <h3>Export Orders, No Marketing</h3>
        <p>We handle buyer acquisition, contracts, and communication. You focus on production.</p>
      </div>
      <div class="us-tile reveal delay-2">
        <div class="us-tile-icon"><?= icon('dollar-sign', 20) ?></div>
        <h3>Reliable Payment</h3>
        <p>Clear terms agreed before production. Payment released promptly once your order passes inspection.</p>
      </div>
      <div class="us-tile reveal delay-3">
        <div class="us-tile-icon"><?= icon('check-circle', 20) ?></div>
        <h3>Clear Specs &amp; QC</h3>
        <p>Approved samples, written specs, and in-line inspection — fewer disputes, fewer surprises for everyone.</p>
      </div>
    </div>

    <div style="max-width:1000px;margin:3rem auto 0;display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2.5rem;align-items:start;">
      <div class="reveal-left">
        <h2 class="section-title" style="font-size:1.5rem;">What We Look For</h2>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:0.75rem;margin-top:1rem;">
          <?php foreach ([
            'Registered manufacturing business in Pakistan with export experience or export-ready capacity',
            'Consistent quality — your production must match approved samples, order after order',
            'Certifications relevant to your category (ISO 9001, ISO 13485, OEKO-TEX, BSCI, etc.) or willingness to obtain them',
            'Ethical operations — safe working conditions and no child labor, verified during our physical audit',
            'Responsive communication and honest capacity commitments',
          ] as $req): ?>
          <li style="display:flex;gap:0.6rem;align-items:flex-start;font-size:0.92rem;color:var(--text);">
            <span style="color:var(--gold);flex-shrink:0;margin-top:2px;"><?= icon('check', 16) ?></span> <?= $req ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <h2 class="section-title" style="font-size:1.5rem;margin-top:2rem;">How Onboarding Works</h2>
        <ol style="margin:1rem 0 0 1.2rem;display:flex;flex-direction:column;gap:0.6rem;font-size:0.92rem;color:var(--text);">
          <li>Submit the application — takes five minutes.</li>
          <li>Screening call and document review (registration, certifications, export record).</li>
          <li>Physical factory audit by our Sialkot team — premises, machinery, workforce, compliance.</li>
          <li>Trial order or sample development to verify quality consistency.</li>
          <li>Onboarded — you receive orders matched to your capability.</li>
        </ol>
        <p style="font-size:0.85rem;color:var(--muted);margin-top:1.5rem;">Please do not attach documents at this stage — we request certificates and references by email during screening.</p>
      </div>

      <div class="reveal-right">
        <?php if ($success): ?>
          <div style="background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);padding:2rem;text-align:center;">
            <div style="color:#16a34a;margin-bottom:1rem;"><?= icon('check-circle', 44) ?></div>
            <h3 style="color:#166534;margin-bottom:0.5rem;">Application Received!</h3>
            <p style="color:#16a34a;">Our manufacturer relations team will contact you for screening within 3 business days.</p>
          </div>
        <?php else: ?>
          <div style="background:var(--cream);border-radius:var(--radius);padding:2rem;border:1px solid var(--cream-dark);">
            <h3 style="font-size:1.1rem;margin-bottom:1.25rem;">Manufacturer Application</h3>
            <?php if ($error): ?>
              <div style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.88rem;"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="/for-manufacturers">
              <?= csrf_field() ?>
              <input type="text" name="portfolio" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
              <div class="form-fields-grid">
                <div class="field"><label class="field-label" for="for-manufacturers-company_name">Company Name *</label><input id="for-manufacturers-company_name" class="field-input" type="text" name="company_name" required maxlength="200" value="<?= e($_POST['company_name'] ?? '') ?>"></div>
                <div class="field"><label class="field-label" for="for-manufacturers-contact_name">Contact Person *</label><input id="for-manufacturers-contact_name" class="field-input" type="text" name="contact_name" required maxlength="200" value="<?= e($_POST['contact_name'] ?? '') ?>"></div>
                <div class="field"><label class="field-label" for="for-manufacturers-email">Email *</label><input id="for-manufacturers-email" class="field-input" type="email" name="email" required maxlength="200" value="<?= e($_POST['email'] ?? '') ?>"></div>
                <div class="field"><label class="field-label" for="for-manufacturers-phone">Phone / WhatsApp</label><input id="for-manufacturers-phone" class="field-input" type="text" name="phone" maxlength="50" value="<?= e($_POST['phone'] ?? '') ?>"></div>
                <div class="field"><label class="field-label" for="for-manufacturers-city">City *</label><input id="for-manufacturers-city" class="field-input" type="text" name="city" required maxlength="100" placeholder="e.g. Sialkot" value="<?= e($_POST['city'] ?? '') ?>"></div>
                <div class="field"><label class="field-label" for="for-manufacturers-website_url">Website (if any)</label><input id="for-manufacturers-website_url" class="field-input" type="text" name="website_url" maxlength="255" placeholder="https://example.com" value="<?= e($_POST['website_url'] ?? '') ?>"></div>
              </div>
              <div class="field" style="margin-bottom:1rem;"><label class="field-label" for="for-manufacturers-product_categories">What You Manufacture *</label><input id="for-manufacturers-product_categories" class="field-input" type="text" name="product_categories" required maxlength="255" placeholder="e.g. soccer balls, sublimation sportswear" value="<?= e($_POST['product_categories'] ?? '') ?>"></div>
              <div class="field" style="margin-bottom:1rem;"><label class="field-label" for="for-manufacturers-certifications">Certifications Held</label><input id="for-manufacturers-certifications" class="field-input" type="text" name="certifications" maxlength="255" placeholder="e.g. ISO 9001, OEKO-TEX, BSCI" value="<?= e($_POST['certifications'] ?? '') ?>"></div>
              <div class="field" style="margin-bottom:1.5rem;"><label class="field-label" for="for-manufacturers-message">Capacity, Export Experience &amp; Anything Else</label><textarea id="for-manufacturers-message" class="field-input" name="message" rows="4" maxlength="5000"><?= e($_POST['message'] ?? '') ?></textarea></div>
              <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;">Submit Application <?= icon('send', 17) ?></button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
