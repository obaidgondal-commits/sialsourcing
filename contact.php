<?php
$pageTitle     = 'Get a Free Sourcing Quote — Pakistan Manufacturer Vetting & Supply Chain | SialSourcing';
$pageDesc      = 'Tell us what you are sourcing and get a manufacturer shortlist, compliance overview, and indicative pricing within 24 hours. Free, no obligation.';
$canonicalPath = '/contact';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/instrument-data.php';

$subjects = [
    'Custom Soccer Balls',
    'Activewear & Sports Uniforms',
    'Uniforms & Tactical Wear',
    'Surgical Instruments',
    'Sports Goods',
    'Leather Products',
    'Cutlery & Kitchenware',
    'Multiple Products',
    'Other',
];

// Preselect the subject when arriving from a product page (/contact?product=slug)
$preselect = '';
if (is_string($_GET['product'] ?? null) && $_GET['product'] !== '') {
    $st = db()->prepare("SELECT title FROM products WHERE slug=? AND is_active=1");
    $st->execute([trim($_GET['product'])]);
    $preselect = $st->fetchColumn() ?: '';
    if ($preselect !== '' && in_array($_GET['product'], ['dental-instruments', 'surgical-instruments'], true)) {
        $preselect = 'Surgical Instruments';
    }
}

$success = false;
$error   = '';
$selection = null;
$prefill = '';
$postedValue = static fn ($key) => is_string($_POST[$key] ?? '') ? ($_POST[$key] ?? '') : '';
$postedText = static fn ($key) => trim($postedValue($key));
$isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$selectionInput = $isPost ? $_POST : $_GET;
if (array_key_exists('family', $selectionInput) || array_key_exists('skus', $selectionInput)) {
    try {
        $selection = instrument_selected_products($selectionInput['family'] ?? null, $selectionInput['skus'] ?? null);
        $preselect = 'Surgical Instruments';
        $prefill = instrument_enquiry_prefix($selection) . "Quantities and requirements:\n";
    } catch (InvalidArgumentException $invalidSelection) {
        $error = $invalidSelection->getMessage();
        http_response_code(422);
    }
}
if ($isPost) {
    $name    = $postedText('name');
    $email   = $postedText('email');
    $company = $postedText('company');
    $phone   = $postedText('phone');
    $subject = $selection ? 'Surgical Instruments' : $postedText('subject');
    $message = $postedText('message');
    $malformedFields = array_filter(['name', 'email', 'company', 'phone', 'subject', 'message'],
        static fn ($key) => isset($_POST[$key]) && !is_string($_POST[$key]));
    if ($selection) {
        $prefix = instrument_enquiry_prefix($selection);
        if (!str_starts_with($message, $prefix)) {
            $message = $prefix . $message;
        }
    }

    if ($error !== '') {
        // Invalid catalogue references must never reach the CMS inbox or mail.
    } elseif ($malformedFields) {
        $error = 'Please enter valid text in the contact fields.';
    } elseif (!empty($_POST['website'])) {
        // Honeypot tripped — pretend success, store nothing
        $success = true;
    } elseif (!csrf_ok()) {
        $error = 'Your session expired — please resubmit the form.';
    } elseif (!$name || !$email || !$message) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($name) > 200 || strlen($subject) > 200 || strlen($message) > 5000) {
        $error = 'Your message is too long — please shorten it and resubmit.';
    } else {
        db()->prepare("INSERT INTO contact_submissions (name,email,company,phone,subject,message) VALUES (?,?,?,?,?,?)")
            ->execute([$name,$email,$company,$phone,$subject,$message]);

        // Notify by email — the enquiry is already saved, so a mail failure
        // is logged but never blocks the submission.
        $to       = setting('contact_email', 'info@sialsourcing.com');
        $safeName = str_replace(["\r", "\n"], ' ', $name);
        $safeMail = str_replace(["\r", "\n"], '', $email);
        $body     = "New enquiry via sialsourcing.com\n\n"
                  . "Name: $safeName\nEmail: $safeMail\nCompany: $company\nPhone: $phone\nSourcing: $subject\n\n$message";
        $headers  = "From: SialSourcing Website <no-reply@sialsourcing.com>\r\nReply-To: $safeMail";
        if (!instrument_is_staging() && !@mail($to, 'New Enquiry: ' . ($subject ?: 'General'), $body, $headers)) {
            error_log('contact.php: mail() failed for enquiry from ' . $safeMail);
        }
        $success = true;
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<div style="padding-top:100px;min-height:100vh;background:var(--cream);">
  <div class="container contact-grid">

    <div class="reveal-left">
      <div class="section-label">Get In Touch</div>
      <h1 style="font-size:clamp(2rem,4vw,3rem);margin-bottom:1rem;">Let's Source Together</h1>
      <p style="margin-bottom:2rem;">Tell us what you're looking for and our team will respond within 24 hours with a sourcing plan and indicative pricing.</p>

      <div style="display:flex;flex-direction:column;gap:1.25rem;margin-bottom:2.5rem;">
        <div style="display:flex;align-items:center;gap:1rem;">
          <div class="contact-icon-tile"><?= icon('mail', 20) ?></div>
          <div><div style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Email</div>
          <a href="mailto:<?= e(setting('contact_email')) ?>" style="color:var(--text);font-weight:600;"><?= e(setting('contact_email')) ?></a></div>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;">
          <div class="contact-icon-tile"><?= icon('phone', 20) ?></div>
          <div><div style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Phone / WhatsApp</div>
          <a href="tel:<?= e(setting('contact_phone')) ?>" style="color:var(--text);font-weight:600;"><?= e(setting('contact_phone')) ?></a></div>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;">
          <div class="contact-icon-tile"><?= icon('map-pin', 20) ?></div>
          <div><div style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Location</div>
          <span style="color:var(--text);font-weight:600;"><?= e(setting('contact_address')) ?></span></div>
        </div>
      </div>
    </div>

    <div class="reveal-right">
      <?php if ($success): ?>
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);padding:2rem;text-align:center;">
          <div style="color:#16a34a;margin-bottom:1rem;"><?= icon('check-circle', 44) ?></div>
          <h3 style="color:#166534;margin-bottom:0.5rem;">Enquiry Received!</h3>
          <p style="color:#16a34a;"><?= instrument_is_staging() ? 'Test enquiry saved in staging. No email was sent.' : "Thank you. We'll get back to you within 24 hours." ?></p>
        </div>
      <?php else: ?>
        <div style="background:var(--white);border-radius:var(--radius);padding:2.5rem;box-shadow:0 4px 30px rgba(0,0,0,0.06);border:1px solid var(--cream-dark);">
          <?php if ($error): ?>
            <div class="alert" style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.88rem;"><?= e($error) ?></div>
          <?php endif; ?>
          <form method="post" action="/contact">
            <?= csrf_field() ?>
<?php if ($selection): ?>
            <input type="hidden" name="family" value="<?= e($selection['family']['code']) ?>">
<?php foreach ($selection['skus'] as $selectedSku): ?>
            <input type="hidden" name="skus[]" value="<?= e($selectedSku) ?>">
<?php endforeach; ?>
<?php endif; ?>
            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
            <div class="form-fields-grid">
              <div class="field">
                <label class="field-label">Full Name *</label>
                <input class="field-input" type="text" name="name" required maxlength="200" placeholder="John Smith" value="<?= e($postedValue('name')) ?>">
              </div>
              <div class="field">
                <label class="field-label">Email *</label>
                <input class="field-input" type="email" name="email" required maxlength="200" placeholder="john@company.com" value="<?= e($postedValue('email')) ?>">
              </div>
              <div class="field">
                <label class="field-label">Company</label>
                <input class="field-input" type="text" name="company" maxlength="200" placeholder="Your Company" value="<?= e($postedValue('company')) ?>">
              </div>
              <div class="field">
                <label class="field-label">Phone / WhatsApp</label>
                <input class="field-input" type="text" name="phone" maxlength="50" placeholder="+1 555 000 0000" value="<?= e($postedValue('phone')) ?>">
              </div>
            </div>
            <div class="field" style="margin-bottom:1rem;">
              <label class="field-label">What are you sourcing?</label>
              <select class="field-input" name="subject" style="background:#fff;">
                <?php $sel = $selection ? 'Surgical Instruments' : ($_POST['subject'] ?? $preselect); ?>
                <?php foreach ($subjects as $s): ?>
                <option <?= $sel === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field" style="margin-bottom:1.5rem;">
              <label class="field-label">Message / Requirements *</label>
              <textarea class="field-input" name="message" required maxlength="5000" rows="5" placeholder="Tell us about your product requirements, quantities, target price, and any certifications needed..."><?= e(is_string($_POST['message'] ?? null) ? $_POST['message'] : $prefill) ?></textarea>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;">Send Enquiry <?= icon('send', 17) ?></button>
          </form>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
