<?php
$pageTitle     = 'Request a Product Quality Inspection in Pakistan';
$pageDesc      = 'Request pre-production, in-line or pre-shipment inspection at a Pakistani factory. Agree the product-specific checks, reporting scope and availability.';
$canonicalPath = '/qc-inspection-request';
require_once __DIR__ . '/config.php';
header('Cache-Control: no-store, private');

$services = ['Pre-shipment inspection', 'In-line (during production) inspection', 'Pre-production check', 'Container loading supervision', 'Factory audit', 'Not sure — advise me'];

$success = isset($_SESSION['qc_request_received']) && time() - (int)$_SESSION['qc_request_received'] < 600;
unset($_SESSION['qc_request_received']);
$error = '';
$values = [];
$limits = ['name' => 200, 'email' => 200, 'company' => 200, 'phone' => 50, 'factory' => 300, 'product' => 300, 'service' => 100, 'details' => 5000];

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
    $honeypot = $_POST['website'] ?? '';
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
        foreach (['name', 'email', 'factory', 'product'] as $field) {
            if ($values[$field] === '') $error = 'Please fill in all required fields.';
        }
        foreach ($limits as $field => $limit) {
            if (strlen($values[$field]) > $limit) $error = 'One or more fields are too long. Please shorten them and try again.';
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email address.';
        if (!in_array($values['service'], $services, true)) $error = 'Please select an inspection service from the list.';

        if ($error === '' && !rate_limit('qc_request:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 900)) {
            http_response_code(429);
            header('Retry-After: 900');
            $error = 'Too many requests. Please wait 15 minutes and try again, or contact us by email.';
        }
        if ($error === '') {
            try {
                $message = "QC INSPECTION REQUEST

Service: {$values['service']}
Factory (name & city): {$values['factory']}
Product & order: {$values['product']}

Details:
{$values['details']}";
                db()->prepare("INSERT INTO contact_submissions (name,email,company,phone,subject,message) VALUES (?,?,?,?,?,?)")
                    ->execute([$values['name'],$values['email'],$values['company'],$values['phone'],'QC Inspection Request',$message]);
                $subject = 'New QC Inspection Request';
                $body = "From: {$values['name']} <{$values['email']}>
Company: {$values['company']}
Phone: {$values['phone']}

$message";
                $success = true;
            } catch (Throwable $exception) {
                http_response_code(503);
                error_log('qc-inspection-request.php: enquiry could not be saved.');
                $error = 'We could not save your request. Please try again or contact us by email.';
            }
            if ($success) {
                try {
                    if (!send_notification(setting('contact_email', 'info@sialsourcing.com'), $subject, $body, $values['email'])) {
                        error_log('qc-inspection-request.php: enquiry saved; email notification was not sent.');
                    }
                } catch (Throwable $exception) {
                    error_log('qc-inspection-request.php: enquiry saved; email notification failed.');
                }
            }
        }
    }
    if ($success) {
        $_SESSION['qc_request_received'] = time();
        header('Location: /qc-inspection-request', true, 303);
        exit;
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<div style="padding-top:100px;min-height:100vh;background:var(--cream);">
  <div class="container contact-grid">

    <div class="reveal-left">
      <div class="section-label">Third-Party QC</div>
      <h1 style="font-size:clamp(1.9rem,3.5vw,2.7rem);margin-bottom:1rem;">Inspect Before You Pay or Ship</h1>
      <p style="margin-bottom:1.5rem;">Already found a factory in Pakistan yourself? Our inspectors check your order on the ground — so you know what's in the cartons before your money or goods move.</p>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:0.8rem;margin-bottom:2rem;">
        <?php foreach ([
          'Sampling criteria and photographic reporting agreed for the product',
          'Measurements, materials, and workmanship checked against your spec',
          'Clear release / hold recommendation from our QC manager',
          'Report format and delivery date agreed before inspection',
        ] as $pt): ?>
        <li style="display:flex;gap:0.6rem;align-items:flex-start;font-size:0.92rem;color:var(--text);">
          <span style="color:var(--gold);flex-shrink:0;margin-top:2px;"><?= icon('check-circle', 17) ?></span> <?= $pt ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <p style="font-size:0.85rem;color:var(--muted);">Want the paper version for your records? <a href="/assets/docs/QC-Inspection-Request-Form.pdf" style="color:var(--gold);font-weight:600;" download>Download the printable form</a> — or see <a href="/lab-qc" style="color:var(--gold);font-weight:600;">how our QC process works</a>.</p>
    </div>

    <div class="reveal-right">
      <?php if ($success): ?>
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);padding:2rem;text-align:center;">
          <div style="color:#16a34a;margin-bottom:1rem;"><?= icon('check-circle', 44) ?></div>
          <h3 style="color:#166534;margin-bottom:0.5rem;">Request Received!</h3>
          <p style="color:#16a34a;">Our team will review your request and confirm availability, scope and quotation.</p>
        </div>
      <?php else: ?>
        <div style="background:var(--white);border-radius:var(--radius);padding:2.5rem;box-shadow:var(--shadow-md);border:1px solid var(--cream-dark);">
          <?php if ($error): ?>
            <div style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.88rem;"><?= e($error) ?></div>
          <?php endif; ?>
          <form method="post" action="/qc-inspection-request">
            <?= csrf_field() ?>
            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
            <div class="form-fields-grid">
              <div class="field"><label class="field-label" for="qc-inspection-request-name">Your Name *</label><input id="qc-inspection-request-name" class="field-input" type="text" name="name" required maxlength="200" value="<?= e($_POST['name'] ?? '') ?>"></div>
              <div class="field"><label class="field-label" for="qc-inspection-request-email">Email *</label><input id="qc-inspection-request-email" class="field-input" type="email" name="email" required maxlength="200" value="<?= e($_POST['email'] ?? '') ?>"></div>
              <div class="field"><label class="field-label" for="qc-inspection-request-company">Company</label><input id="qc-inspection-request-company" class="field-input" type="text" name="company" maxlength="200" value="<?= e($_POST['company'] ?? '') ?>"></div>
              <div class="field"><label class="field-label" for="qc-inspection-request-phone">Phone / WhatsApp</label><input id="qc-inspection-request-phone" class="field-input" type="text" name="phone" maxlength="50" value="<?= e($_POST['phone'] ?? '') ?>"></div>
            </div>
            <div class="field" style="margin-bottom:1rem;"><label class="field-label" for="qc-inspection-request-service">Inspection Needed</label>
              <select id="qc-inspection-request-service" class="field-input" name="service" style="background:#fff;">
                <?php foreach ($services as $s): ?><option <?= ($_POST['service'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="field" style="margin-bottom:1rem;"><label class="field-label" for="qc-inspection-request-factory">Factory Name &amp; City (Pakistan) *</label><input id="qc-inspection-request-factory" class="field-input" type="text" name="factory" required maxlength="300" placeholder="e.g. ABC Industries, Sialkot" value="<?= e($_POST['factory'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:1rem;"><label class="field-label" for="qc-inspection-request-product">Product &amp; Order Size *</label><input id="qc-inspection-request-product" class="field-input" type="text" name="product" required maxlength="300" placeholder="e.g. 5,000 size-5 soccer balls, PO #1042" value="<?= e($_POST['product'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:1.5rem;"><label class="field-label" for="qc-inspection-request-details">Dates, Specs &amp; Concerns</label><textarea id="qc-inspection-request-details" class="field-input" name="details" rows="4" maxlength="5000" placeholder="Requested inspection dates, approved sample availability, specific concerns..."><?= e($_POST['details'] ?? '') ?></textarea></div>
            <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;">Request Inspection <?= icon('send', 17) ?></button>
          </form>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
